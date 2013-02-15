<?php
	require('XML_Parser.php');
	require('paths.php');
	require('AmazonSearch.php');
	require('AmazonSearchResults.php');
	require('AmazonItem.php');
	require('AmazonSearchParser.php');
	
	
	$as = new AmazonSearch;
	$res = new AmazonSearchResults;
	$res->terms = 'Spooks';
	$as->terms = 'Spooks';
	$as->results = $res;
	echo "<p>Amazon URL:".$as->generateXMLUrl()."</p>\n";
	$as->getResults();
	if ($as->parseResultsToFile()){
		echo $as->returnResults();
	}else{
		echo 'Error occured';
	}
?>