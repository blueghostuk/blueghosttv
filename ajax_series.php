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
		//echo $Database->ajaxSeries($_REQUEST['query']);
		if (strpos($_REQUEST['query'], 'crid') === false){/*bleb prog*/
			$safe_qry = str_replace("+", " ", $_REQUEST['query']);
			$safe_qry = str_replace("\'", "'", $safe_qry);
			$series = $Database->getBlebSeries($safe_qry);
			$op = new TV_Outputter(null);
			if ($op->returnParseSeriesResultsForAjax($series['progs'], $series['sched'])){
				echo $op->getOutput();
			}else{
				echo '<h1>Error</h1>';
			}
		}else{/*tv-any prog*/
			$series = $Database->getSeries($_REQUEST['query']);
			$op = new TV_Outputter(null);
			if ($op->returnParseSeriesResultsForAjax($series['progs'], $series['sched'])){
				echo $op->getOutput();
			}else{
				echo '<h1>Error</h1>';
			}
		}
	}else{
		echo 'No Results Found';
	}
?>