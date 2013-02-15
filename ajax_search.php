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
	
	$dbase   	= 'blueghos_tv';
	$Database 	= new TV_DBConnection();
	$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
	
	if (isset($_REQUEST['query'])){
		$series = $Database->ajaxResults($_REQUEST['query']);
		$op = new TV_Outputter(null);
		if ($op->parseSearchResultsForAjax($series['progs'], $series['sched'], $_REQUEST['query'])){
			echo $op->getOutput();
		}else{
			echo '<h1>Error</h1>';
		}
		//echo $Database->ajaxResults($_REQUEST['query']);
	}else{
		echo 'No Results Found';
	}
?>