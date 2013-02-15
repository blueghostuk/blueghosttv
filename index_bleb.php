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
	require('/home/blueghos/db.php');
	require('includes/DB_Connection.php');
	require('includes/TV_DBConnection.php');
	require('includes/Bleb_ProgramParser.php');
	require('includes/Bleb_Program.php');
	require('includes/Bleb_AVAttributes.php');
	require('includes/Bleb_Program_Schedule.php');	
	require('includes/Bleb_Outputter.php');
	require('includes/Bleb_Channel.php');
	
	if (isset($_REQUEST['day']))
		$time = time() + ($_REQUEST['day'] * 24 * 60 * 60);
	else
		$time = time();
	
	$dateText = date("l jS F Y", $time);
	include('header.php');
	
	$dbase   	= 'blueghos_tv';
	$Database 	= new TV_DBConnection();
	$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
	
	$channels = $Database->getAllChannels();
	foreach ($channels as $channel){
		if (!$channel->isTVAny())
			echo'| <a href="index_bleb.php?channel='.$channel->id.'">'.$channel->title.'</a> ';
	}
	echo '|';
		
	if (isset($_REQUEST['channel'])){
		
		$date = date("Ymd", $time);
		$sChannel = $Database->getChannel($_REQUEST['channel'], $date);
		$op = new Bleb_Outputter($sChannel);
		$date_text = date("l jS F Y", $time);
		
		if ($op->parseSingleChannel($date_text)){
			echo $op->getOutput();
		}else{
			echo '<h1>Error</h1>';
		}
	}
	
	include('footer.php');
	
?>