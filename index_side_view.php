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
	
	if (isset($_REQUEST['day']))
		$time = time() + ($_REQUEST['day'] * 24 * 60 * 60);
	else
		$time = time();
	
	$dateText = date("l jS F Y", $time);
	include('header.php');
	
	$dbase   	= 'blueghos_tv';
	$Database 	= new TV_DBConnection();
	$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
	
	$sView = new TV_View;
	//$date = date("Ymd", $time);
	$date = date("Y-m-d", $time);
	$channels = $Database->getAllChannels();
	$i = 0;
	foreach ($channels as $channel){
		//if ($channel->isTVAny())
		if ($i < 10)
			$sView->addChannel($Database->getChannel($channel->id,$date));
		$i++;
	}
	$op = new TV_Outputter($sView);
	if ($op->parseSideView()){
		echo $op->getOutput();
	}else{
		echo '<h1>Error</h1>';
	}
	include('footer.php');
?>