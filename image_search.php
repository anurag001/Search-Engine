<?php
include('class.php');

if(!empty($_GET["search_input"]))
{
	$search = htmlspecialchars($_GET["search_input"]);
	$search = trim($search);

	$ob->image_search($search);
}
else
{
	echo '<span style="color:black;font-weight:bold;" class="text-center">Please provide some search field</span>';
}
			

?>