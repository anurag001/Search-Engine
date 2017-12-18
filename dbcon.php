<?php
	try{
		$pdo=new PDO("mysql:host=localhost;dbname=search",'root','');
	}
	catch(PDOException $ex)
	{
		echo $ex->getMessage();
		die();
	}

?>