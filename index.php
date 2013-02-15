<?php

	require('includes/paths.php');
	
		
	if (isset($_REQUEST['day'])){
		$d = $_REQUEST['day'];
		$time = time() + ($_REQUEST['day'] * 24 * 60 * 60);
		$menu_day = "/".$d;
	}else{
		$d = 0;
		$time = time();
		$menu_day = "";
	}
	$dateText = date("l jS F Y", $time);
			
	$date = date("Ymd", $time);
	if ($d == 0)
	{
		$dateAppend = "";
	}
	else
	{
		$dateAppend = $date;
	}
	
	if (isset($_REQUEST['channel'])){
		$sid = $_REQUEST['channel'];
	}else{
		$sid = 1;
	}
	$file_date = date("Ymd", $time);
	$menu_file = $dir.'cache/html/menu.php';
	$xml_title = "TV Channel Listing for ".$dateText;
	$xml_link = "http://www.blueghosttv.co.uk/feeds/rss/channel/".$sid."/".$dateAppend;
	include('header.php');
	include($menu_file);
	$cache_file 		= $dir.'cache/html/single/'.$sid.'/'.$file_date.'.html';
	$cache_file_today 	= $dir.'cache/html/single/'.$sid.'/'.$file_date.'_today.html';
	if ($d == 0 && !file_exists($cache_file_today))
	{
		require('includes/TV_Outputter.php');
		require('includes/io.php');
		$tv = new TV_Outputter($cache_file);
		write_header($cache_file_today, $tv->regexFile());
		$cache_file = $cache_file_today;
	}
	else
	{
		if ($d == 0 && file_exists($cache_file_today))
		{
			$cache_file = $cache_file_today;
		}
	}
	include($cache_file);
	//include('downtime.php');
	
	include('footer.php');
?>