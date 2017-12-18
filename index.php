<!DOCTYPE html>
<html lang="eng">
	<head>
		<title>Search</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	</head>
	<body>
		<section style="margin-top:10px;">
			<div class="container">
				<div class="row">
					<div class="col-lg-2">
					<span class="center text-center"><b>Project CrawlX</b><br><i>Developed By:<br>Anurag Kumar</i></span>
					</div>
					<div class="col-lg-8">
						<h1>Search Engine</h1>
						<div class="row">
							<form method="get" id="search-form">
								<div class="col-sm-8">
									
									<input type="text" name="search_input" id="search-input" class="form-control" required="required">
						
								</div>
								<div class="col-sm-2">
									
									<input type="submit" id="web-search-btn" class="btn btn-success btn-block" value="Web">
								
								</div>
								<div class="col-sm-2">
									
									<input type="submit" id="image-search-btn" class="btn btn-success btn-block" value="Image">
								
								</div>
							</form>
						</div>
					</div>
					<div class="col-lg-2">

					</div>
				</div>
				</br>
				<div class="row">
					<div class="col-lg-1">
					</div>
					<div class="col-lg-10">
						<div id="result">
						</div>
					</div>
					<div class="col-lg-1">
					</div>
				</div>
			</div>
			
		</section>
	</body>
	<script type="text/javascript">
	$("#web-search-btn").click(function(e){
		e.preventDefault();
			var data = $("#search-input").val();
			$.ajax({
				url:'./web_search.php',
				method: "GET",
				data:"search_input="+data,
				beforeSend:function()
				{
					$("#result").html("<b>Searching...</b><br><i>Crawler will automatic start crawling if results are not found</i>");
				},
				success:function(data)
				{
					$("#result").html(data);
				},
				error:function()
				{
					$("#result").html("<b>Check your internet connection<b><br>Technical error due to poor internet connection");
				},
				complete:function()
				{

				}

			});

	
	});

	$("#image-search-btn").click(function(e){
		e.preventDefault();
		var data = $("#search-input").val();

			$.ajax({
				url:'./image_search.php',
				method: "GET",
				data:"search_input="+data,
				beforeSend:function()
				{
					$("#result").html("<b>Searching...</b><br><i>Crawler will automatic start crawling if results are not found</i>");				
				},
				success:function(data)
				{
					$("#result").html(data);
				},
				error:function()
				{
					$("#result").html("<b>Check your internet connection<b><br>Technical error due to poor internet connection");
				},
				complete:function()
				{

				}

			});

	});

	window.onload = function(){
		$.ajax({
				url:'./info.php',
				method: "post",
				beforeSend:function()
				{
					$("#result").html("Loading...");
				},
				success:function(data)
				{
					$("#result").html(data);
				},
				error:function()
				{
					$("#result").html("<b>Check your internet connection<b><br>Technical error due to poor internet connection");
				},
				complete:function()
				{

				}

			});
	}

	</script>
</html>