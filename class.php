<?php
include_once('dbcon.php');
class SearchFunctions
{

	public function file_get_contents_curl($url)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}

	public function extract_details($url)
	{
		$html = $this->file_get_contents_curl($url);

		//parsing begins here:
		$doc = new DOMDocument();
		@$doc->loadHTML($html);
		$nodes = $doc->getElementsByTagName('title');

		//get and display what you need:
		$title = $nodes->item(0)->nodeValue;

		$metas = $doc->getElementsByTagName('meta');

		for ($i = 0; $i < $metas->length; $i++)
		{
			$meta = $metas->item($i);
			if($meta->getAttribute('name') == 'description')
			{
				$description = $meta->getAttribute('content');
			}
			if($meta->getAttribute('name') == 'keywords')
			{
				$keywords = $meta->getAttribute('content');
			}
		}

		echo "Title: $title". '<br/><br/>';
		echo "Description: $description". '<br/><br/>';
		echo "Keywords: $keywords";

	}

	public function extract_from_url($url)
	{
		$tags = get_meta_tags($url);
		echo $tags['author'];       // name
		echo $tags['keywords'];     // php documentation
		echo $tags['description'];  // a php manual
		echo $tags['geo_position']; // 49.33;-86.59
	}

	public function backlinks($linkurl)
	{
		$link =  $linkurl; 
		$site = "https://en.wikipedia.org";
		// Use: Add following url parameters:
		// link: The url you want to get the backlinnks from (required)
		// siteFilter: The site your backlinks should come from (eg. twitter.com) (optional)

		$jsonObject =  json_decode(file_get_contents("https://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=link:$link+site:$site"), true);
		
		 echo "Number of backlinks: ".$jsonObject['responseData']['cursor']['resultCount'];


	}

	public function web_search($keyword)
	{
		global $pdo;
		$keyword = trim(htmlspecialchars($keyword));
		$query = $pdo->prepare("select * from keyword_table where keyword like '%$keyword%' order by keyword asc");
		$query->bindParam(1,$keyword);
		$query->execute();
		if($query->rowCount()>0)
		{
			$row = $query->fetch(PDO::FETCH_OBJ);
			$keyword_id = $row->id;

			$search = $pdo->prepare("select * from web_table where keyword_id = ?");
			$search->bindParam(1,$keyword_id);
			if($search->execute())
			{
				echo "<br>About ".$search->rowCount()." results<br><hr>";
				while($url_row = $search->fetch(PDO::FETCH_OBJ))
				{
					echo '<a style="font-weight:bold;color:blue;" href="'.$url_row->web_url.'">'.$url_row->web_url.'</a>';
					echo "<br><br>";
				}
			}
		}
		else
		{
			
			echo '<span style="color:black;font-weight:bold;" class="text-center">No Result Found</span><br>Try more specific keywords<br>Result may require Crawling. Visit to <a href="./crawler.php">Crawler</a> for contributing in searching keywords and deep crawling.<br><br><b>Automatic CrawlerBots have started crawling...</b><br>';
				$key_arr = explode(" ",$keyword);
				$first = $key_arr[0];
				for($i=0;$i<count($key_arr);$i++) 
				{
					$key_arr[$i]=ucfirst($key_arr[$i]);
				}
				$search1 = implode("_", $key_arr);
				if(preg_match('/_Of_/im',$search1))
				{
					$search1 = str_replace("_Of_","_of_", $search1);
				}
				if(preg_match('/_And_/im', $search1))
				{
					$search1 = str_replace("_And_","_and_", $search1);
				}
				$url ="https://en.wikipedia.org/wiki/".$search1;
				$this->crawler($url,$keyword,$search1,$first);
		}
	}

	public function image_search($keyword)
	{
		global $pdo;
		$keyword = trim(htmlspecialchars($keyword));
		$query = $pdo->prepare("select * from keyword_table where keyword like '%$keyword%' order by keyword asc");
		$query->execute();
		if($query->rowCount()>0)
		{
			$row = $query->fetch(PDO::FETCH_OBJ);
			$keyword_id = $row->id;
			
			$search = $pdo->prepare("select * from image_table where keyword_id = ?");
			$search->bindParam(1,$keyword_id);
			if($search->execute())
			{
				echo "<br>About ".$search->rowCount()." results<br><hr>";
				echo "<div style='display:flex;flex-wrap:wrap;'>";
				while($img_url_row = $search->fetch(PDO::FETCH_OBJ))
				{
					echo '
						<a href="'.$img_url_row->image_url.'"><img class="img img-thumbnail" src="'.$img_url_row->image_url.'"style="max-height:150px;max-width:250px;margin-bottom:6px;flex-grow:1;"></a>&nbsp;&nbsp;
						';
				}
				echo "</div>";
			}
		}
		else
		{
			echo '<span style="color:black;font-weight:bold;" class="text-center">No Result Found</span><br>Try more specific keywords<br>Result may require Crawling. Visit to <a href="./crawler.php">Crawler</a> for contributing in searching keywords and deep crawling.<br><br><b>Automatic CrawlerBots have started crawling...</b><br>';
				$key_arr = explode(" ",$keyword);
				$first = $key_arr[0];
				for($i=0;$i<count($key_arr);$i++) 
				{
					$key_arr[$i]=ucfirst($key_arr[$i]);
				}
				$search1 = implode("_", $key_arr);
				if(preg_match('/_Of_/im',$search1))
				{
					$search1 = str_replace("_Of_","_of_", $search1);
				}
				if(preg_match('/_And_/im', $search1))
				{
					$search1 = str_replace("_And_","_and_", $search1);
				}
				$url ="https://en.wikipedia.org/wiki/".$search1;
				$this->crawler($url,$keyword,$search1,$first);
		}
	}

	public function crawler($url,$search,$search1,$first)
	{
		global $pdo;
		$web_array = array();
		$img_array=array();
		
		$count1=0;
		$count2=0;
		$count_total=0;
		
		$query = $pdo->prepare("select * from keyword_table where keyword = ?");
		$query->bindParam(1,$search);
		if($query->execute())
		{
			if($query->rowCount()==0)
			{

				$options = array('http'=>array('method' => "GET", 'headers'=>"User-Agent: howBot/0.1\n"));
				$context = stream_context_create($options);
				$doc = new DOMDocument();
				@$doc->loadHTML(file_get_contents($url, false, $context));
				foreach($doc->getElementsByTagName('a') as $link) 
				{
					$links = $link->getAttribute('href');
					if(preg_match_all('/'.$search1.'/im', $links) or preg_match_all('/'.$first.'/im', $links) or preg_match_all('/'.$search.'/im', $links))
					{
						if(preg_match('/.jpg/', $links)==false && preg_match('/.png/', $links)==false && preg_match('/.gif/', $links)==false && preg_match('/.jpeg/', $links)==false && preg_match('/.JPG/', $links)==false && preg_match('/.svg/', $links)==false && substr($links,0,5)!='/wiki')
						{
							if(substr($links,0,8)=='https://' or substr($links,0,7)=='http://')
							{
								if((substr($links,0,8)=='https://' or substr($links,0,7)=='http://') && preg_match('/wikipedia/im', $links)==false && preg_match('/wiki/im', $links)==false)
								{
								
									if(!in_array($links,$web_array))
									{
										$web_array[$count1]=$links;
										$count1++;
									}
								
								}

							}

						}
						$count_total++;
					}

				}
				
				foreach($doc->getElementsByTagName('img') as $link) 
				{
					$links = $link->getAttribute('src');
					if(preg_match_all('/'.$search1.'/im', $links) or preg_match_all('/'.$first.'/im', $links) or preg_match_all('/'.$search.'/im', $links))
					{
						$img_array[$count2]=$links;
						$count2++;
					}
					
				}

				//----Image Crawler upto one more extent----
				/*$num_count=1;
				$web_url = $web_array[$num_count];
				$options = array('http'=>array('method' => "GET", 'headers'=>"User-Agent: howBot/0.1\n"));
				$context = stream_context_create($options);
				$doc = new DOMDocument();
				@$doc->loadHTML(file_get_contents($web_url, false, $context));
				foreach($doc->getElementsByTagName('img') as $link) 
				{
					$links = $link->getAttribute('src');
					if(preg_match_all('/'.$search1.'/im', $links) or  preg_match_all('/'.$first.'/im', $links) or preg_match_all('/'.$search.'/im', $links))
					{
						$img_array[$count2]=$links;
						$count2++;
					}
					
				}*/
				
				if($count1!=0 or $count2!=0)
				{
					$insert_keyword = $pdo->prepare("insert into keyword_table(keyword) values(?)");
					$insert_keyword->bindParam(1,$search);
					$insert_keyword->execute();
					$keyword_id = $pdo->lastInsertId();

					$keyword_query=$pdo->prepare("select keyword from keyword_table where id=?");
					$keyword_query->bindParam(1,$keyword_id);
					$keyword_query->execute();
					$keyword_row = $keyword_query->fetch(PDO::FETCH_OBJ);
					$keyword_name = $keyword_row->keyword; 
				
				
					for($i=0;$i<$count1;$i++)
					{
						$web_url = $web_array[$i];
						$insert_url = $pdo->prepare("insert into web_table(web_url,keyword_id,keyword) values(?,?,?)");
						$insert_url->bindParam(1,$web_url);
						$insert_url->bindParam(2,$keyword_id);
						$insert_url->bindParam(3,$keyword_name);
						$insert_url->execute();
					}

					for($i=0;$i<$count2;$i++)
					{
						$img_url = $img_array[$i];
						$insert_url = $pdo->prepare("insert into image_table(image_url,keyword_id) values(?,?)");
						$insert_url->bindParam(1,$img_url);
						$insert_url->bindParam(2,$keyword_id);
						$insert_url->execute();
					}
				}
				else
				{
					$suggest = $pdo->prepare("select * from keyword_table where keyword like '%$search%' order by keyword asc");
					$suggest->execute();
					if($suggest->rowCount()>0)
					{
						$suggest_row = $suggest->fetch(PDO::FETCH_OBJ); 
						$suggestion = $suggest_row->keyword;
						echo '<span style="font-weight:bold;color:red">Did you mean:</span>'.'<i><a href="./index.php?search_input='.$suggestion.'">'.$suggestion.'</a></i>';
					}
					else
					{
						echo '<span style="font-weight:bold;color:red">Try more specific and similar keyword </span>';
					}

				}

				echo "<hr><b>Crawling Report</b><br>
					<b>Accepted Crawled Web URLs:</b>".$count1."<br>
					<b>Accepted Crawled Image URLs:</b>".$count2."<br>
					<b>Total Crawled URLs:</b>".$count_total."<br>";
					
			}
			else
			{
				echo '<span style="color:red;font-weight:bold;" class="text-center">Keyword is already present in our record. Go for Update Crawling or Deep Crawling.</span>';
			}		

		}
	}


	public function updating_crawler($url,$search,$search1,$first)
	{
		global $pdo;

		$query = $pdo->prepare("select * from keyword_table where keyword = ?");
		$query->bindParam(1,$search);
		$query->execute();
	
		if($query->rowCount()>0)
		{
			$row = $query->fetch(PDO::FETCH_OBJ);
			$keyword_id = $row->id;
			$keyword_name = $row->keyword;

			$web_array = array();
			$img_array=array();
			
			$count1=0;
			$count2=0;
			$count_total=0;

			$options = array('http'=>array('method' => "GET", 'headers'=>"User-Agent: howBot/0.1\n"));
			$context = stream_context_create($options);
			$doc = new DOMDocument();
			@$doc->loadHTML(file_get_contents($url, false, $context));
			foreach($doc->getElementsByTagName('a') as $link) 
			{
				$links = $link->getAttribute('href');
				if(preg_match_all('/'.$search1.'/im', $links) or preg_match_all('/'.$first.'/im', $links) or preg_match_all('/'.$search.'/im', $links))
				{
					if(preg_match('/.jpg/', $links)==false && preg_match('/.png/', $links)==false && preg_match('/.gif/', $links)==false && preg_match('/.jpeg/', $links)==false && preg_match('/.JPG/', $links)==false && preg_match('/.svg/', $links)==false && substr($links,0,5)!='/wiki')
					{
						if((substr($links,0,8)=='https://' or substr($links,0,7)=='http://') && preg_match('/wikipedia/im', $links)==false && preg_match('/wiki/im', $links)==false)
						{
							
								if(!in_array($links,$web_array))
								{
									$web_array[$count1]=$links;
									$count1++;
								}
							
						}

					}
					$count_total++;

				}

			}
			
			foreach($doc->getElementsByTagName('img') as $link) 
			{
				$links = $link->getAttribute('src');
				if(preg_match_all('/'.$search1.'/im', $links) or  preg_match_all('/'.$first.'/im', $links) or preg_match_all('/'.$search.'/im', $links))
				{
					$img_array[$count2]=$links;
					$count2++;
				}
				
			}

			//----Image Crawler upto one more extent----
				/*$num_count=1;
				$web_url = $web_array[$num_count];
				$options = array('http'=>array('method' => "GET", 'headers'=>"User-Agent: howBot/0.1\n"));
				$context = stream_context_create($options);
				$doc = new DOMDocument();
				@$doc->loadHTML(file_get_contents($web_url, false, $context));
				foreach($doc->getElementsByTagName('img') as $link) 
				{
					$links = $link->getAttribute('src');
					if(preg_match_all('/'.$search1.'/im', $links) or  preg_match_all('/'.$first.'/im', $links) or preg_match_all('/'.$search.'/im', $links))
					{
						$img_array[$count2]=$links;
						$count2++;
					}
					
				}*/
				
						
			for($i=0;$i<$count1;$i++)
			{
				$web_url = $web_array[$i];
				$url_check = $pdo->prepare("select * from web_table where web_url=? and keyword_id=?");
				$url_check->bindParam(1,$web_url);
				$url_check->bindParam(2,$keyword_id);
				$url_check->execute();
				if($url_check->rowCount()==0)
				{
					$insert_url = $pdo->prepare("insert into web_table(web_url,keyword_id,keyword) values(?,?,?)");
					$insert_url->bindParam(1,$web_url);
					$insert_url->bindParam(2,$keyword_id);
					$insert_url->bindParam(3,$keyword_name);
					$insert_url->execute();
				}
				
			}

			for($i=0;$i<$count2;$i++)
			{
				$img_url = $img_array[$i];
				$url_check = $pdo->prepare("select * from image_table where image_url=? and keyword_id=?");
				$url_check->bindParam(1,$img_url);
				$url_check->bindParam(2,$keyword_id);
				$url_check->execute();
				if($url_check->rowCount()==0)
				{
					$insert_url = $pdo->prepare("insert into image_table(image_url,keyword_id) values(?,?)");
					$insert_url->bindParam(1,$img_url);
					$insert_url->bindParam(2,$keyword_id);
					$insert_url->execute();
				}
				
			}

			echo "<b>Crawling Report</b><br>
					<b>Accepted Crawled Web URLs:</b>".$count1."<br>
					<b>Accepted Crawled Image URLs:</b>".$count2."<br>
					<b>Total Crawled URLs:</b>".$count_total."<br>";
		}
		else
		{
			echo '<span style="color:red;font-weight:bold;" class="text-center">Keyword is not present in our record. Go for Start Crawling.</span>';
		}
	}

	
	

	public function url_crawl($url)
	{
		$count=0;
		$innerlinkCount=0;
		$totalcount=0;
		global $pdo;

		$options = array('http'=>array('method' => "GET", 'headers'=>"User-Agent: howBot/0.1\n"));
		$context = stream_context_create($options);
		$doc = new DOMDocument();
		@$doc->loadHTML(file_get_contents($url, false, $context));
		foreach($doc->getElementsByTagName('a') as $link) 
		{
			$links = $link->getAttribute('href');
			
				
					if(substr($links,0,5)=='/wiki')
					{
						$l = str_replace("/wiki","https://en.wikipedia.org/wiki",$links)."<br>";
						$count++;
						$innerlinkCount++;

					}
					else if(substr($links,0,8)=='https://' or substr($links,0,7)=='http://')
					{
						if(preg_match_all('/wikipedia/im', $links))
						{
							$innerlinkCount++;
						}
						$count++;
					}
			
			$totalcount++;
		}
		$noturl = $totalcount - $count;
		//$url_inlinks_array[$inner_count]=$count;
		//$inner_count++; 
		echo "<b>".$url."</b><br>";
		echo "Total URL ".$totalcount."<br>";
		echo "URL taken ".$count."<br>";
		echo "URL Not taken ".$noturl."<br>";
		echo "URL Inner Links ".$innerlinkCount."<br>";
		
	}

	

}

$ob = new SearchFunctions;

?>