<?php 

	require_once "includes/BukmekersParser.php";

	$bukmekersParser = BukmekersParser::getInstance();
	$bukmekersList = $bukmekersParser->getBukmekersList();

	var_dump($bukmekersList);
?>