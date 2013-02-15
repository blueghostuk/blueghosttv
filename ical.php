<?php

	require('includes/paths.php');
	require('includes/XML_Parser.php');
	require('includes/TV_Channel.php');
	require('includes/TV_Program.php');
	require('includes/TV_Related_Info.php');
	require('includes/TV_Genre.php');
	require('includes/TV_AVAttributes.php');
	require('includes/TV_Program_Schedule.php');
	require('includes/TV_ProgramParser.php');
	require('includes/TV_ScheduleParser.php');
	require('includes/TV_Outputter.php');
	require('includes/TV_View.php');
	require('/home/blueghos/db.php');
	require('includes/DB_Connection.php');
	require('includes/TV_DBConnection.php');
	require('includes/Bleb_ProgramParser.php');
	require('includes/Bleb_Program.php');
	require('includes/Bleb_AVAttributes.php');
	require('includes/Bleb_Program_Schedule.php');	
	require('includes/Bleb_Outputter.php');
	require('includes/Bleb_Channel.php');
	require('includes/cache.php');
	
	header('Content-type: text/plain');
	
	$dbase   	= 'blueghos_tv';
	$Database 	= new TV_DBConnection();
	$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
	$cache = new Cache;
	if (isset($_REQUEST['series']))
	{
		//echo $Database->ajaxSeries($_REQUEST['query']);
		$_REQUEST['series'] = str_replace(".ics", "", $_REQUEST['series']);
		$series = eregi_replace('crid:/', 'crid://', $_REQUEST['series']); // fix url pattern matching
		$file = str_replace("crid://bbc.co.uk/", "crid_bbc.co.uk_", $series);
		$file = str_replace("\\'", "'", $file);
		$_REQUEST['series'] = str_replace("\\'", "'", $series);
		if ($cache->checkiCalCache('series_'.$file))
		{
			if (strpos($_REQUEST['series'], 'crid') === false)
			{/*bleb prog*/
				$series = $Database->getBlebSeries($_REQUEST['series']);
			}
			else
			{/*tv-any prog*/
				$series = $Database->getSeries($series);
			}
			$op = new TV_Outputter(null);
			if ($op->returnParseSeriesResultsForiCal($series['progs'], $series['sched']))
			{
				echo $op->getOutput();
				$cache->createiCalFile('series_'.$file, $op->getOutput());
			}
			else
			{
				echo '<h1>Error</h1>';
			}
		}
		else
		{
			echo $cache->getiCalFile('series_'.$file);
		}
	}
	if (isset($_REQUEST['genre']))
	{
		$_REQUEST['genre'] = str_replace(".ics", "", $_REQUEST['genre']);
		if ($cache->checkiCalCache('genre_'.$_REQUEST['genre']))
		{
			$progs = $Database->findProgsWithGenre($_REQUEST['genre']);
			$op = new TV_Outputter($_REQUEST['genre']);
			if ($op->parseGenreListForiCal($progs['progs'], $progs['sched']))
			{
				echo $op->getOutput();
				$cache->createiCalFile('genre_'.$_REQUEST['genre'], $op->getOutput());
			}
			else
			{
				echo '<h1>Error</h1>';
			}
		}
		else
		{
			echo $cache->getiCalFile('genre_'.$_REQUEST['genre']);
		}
	}
	if (isset($_REQUEST['channel']))
	{
		$_REQUEST['channel'] = str_replace(".ics", "", $_REQUEST['channel']);
		if ($_REQUEST['date'] == 'today')
		{
			//$date = date("Ymd");
			$date = date("Y-m-d");
		}
		else
		{
			$date = $_REQUEST['date'];
		}
		$sChannel = $Database->getChannel($_REQUEST['channel'], $date);
		$op = new TV_Outputter($sChannel);
		if ($op->parseSingleChannelForiCal($date_text))
		{
			echo $op->getOutput();
		}
		else
		{
			echo '<h1>Error</h1>';
		}
	}
	if (isset($_REQUEST['search']))
	{
		$_REQUEST['search'] = str_replace(".ics", "", $_REQUEST['search']);
		$series = $Database->ajaxResults($_REQUEST['search']);
		$op = new TV_Outputter(null);
		if ($op->parseSearchResultsForiCal($series['progs'], $series['sched']))
		{
			echo $op->getOutput();
		}
		else
		{
			echo '<h1>Error</h1>';
		}
	}
?>