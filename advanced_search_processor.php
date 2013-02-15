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
	
	require('includes/TV_FileSearch.php');
	
	$channels = array();
	
	if (strlen($_POST['sText']) < 3 ){
		die('Need more than 3 chars');
	}
	
	if ($_POST['common'] != 'na'){
		switch($_POST['common']){
			case 	'terr':
				$channels[]	= 1;
				$channels[]	= 2;
				$channels[]	= 32;
				$channels[]	= 33;
				$channels[]	= 30;
			break;
			case	'freeview':
				$channels[]	= 1;
				$channels[]	= 2;
				$channels[]	= 32;
				$channels[]	= 33;
				$channels[]	= 30;
				$channels[]	= 78;
				$channels[]	= 4;
				$channels[]	= 5;
				$channels[]	= 34;
				$channels[]	= 35;
				$channels[]	= 79;
				$channels[]	= 28;
				$channels[]	= 38;
				$channels[]	= 44;
				$channels[]	= 68;
				$channels[]	= 3;
				$channels[]	= 71;
				$channels[]	= 20;
			break;
			case	'radio':
				$channels[]	= 9;
				$channels[]	= 10;
				$channels[]	= 11;
				$channels[]	= 12;
				$channels[]	= 13;
				$channels[]	= 14;
				$channels[]	= 15;
				$channels[]	= 16;
				$channels[]	= 17;
				$channels[]	= 18;
				$channels[]	= 19;
			break;
		}
	}else{
		for($i=0; $i <5; $i++){
			$value = $_POST['channel_group_'.$i];
			if ($value != 'na'){
				$channels[] = $value;
			}
		}
	}
	
	$searcher = new TV_FileSearcher($channels, strtolower($_POST['sText']));
	
	$searcher->performSearch();
	
	$results = $searcher->getResults();
	
	$pg_title = "Advanced Search Results";
	
	include('header.php');
	
	echo 'Found '.count($results).' results';
	
	if ( count($results) > 0){
		echo'<ul>';
		foreach($results as $result){
			echo '<li><a href="'.$result['file_url'].'">Result Found</a></li>';
		}
		echo'</ul>';
	}
	
	include('footer.php');
	
	
	
?>