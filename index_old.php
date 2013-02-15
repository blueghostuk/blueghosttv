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
	
	include('header.php');
	
	$dbase   	= 'blueghos_tv';
	$Database 	= new TV_DBConnection();
	$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
	
	$channels = $Database->getAllChannels();
	foreach ($channels as $channel){
		echo'<a href="index.php?channel='.$channel->id.'">'.$channel->title.'</a><br />';
	}
		
	if (isset($_REQUEST['channel'])){
		echo '<strong>Add Days</strong><br />';
		for ($i=1; $i <= 7; $i++){
			echo'<a href="index.php?channel='.$_REQUEST['channel'].'&day='.$i.'">'.$i.'</a> | ';
		}
		$sChannel = $Database->getChannel($_REQUEST['channel']);
		if (isset($_REQUEST['day']))
			$time = time() + ($_REQUEST['day'] * 24 * 60 * 60);
		else
			$time = time();
		$file = $dir.'data/'.date("Y", $time).date("m", $time).date("d", $time).$sChannel->serviceId.'_pi.xml';
	
		$parser = new TV_ProgramParser($file);
		//echo '<h2>Source is:'.$parser->getSource().'</h2>';
		$parser->setChannel($sChannel);
		//echo '<h2>Channel is:'.$channel->title.'</h2>';
		//$parser->setSource();
	
		$parser->parseFile();
		//echo $parser->getOutput();
		$chan = $parser->tv;
		$file = $dir.'data/'.date("Y", $time).date("m", $time).date("d", $time).$sChannel->serviceId.'_pl.xml';
		$parser = new TV_ScheduleParser($file, $chan);
		//echo '<h2>Source is:'.$parser->getSource().'</h2>';
		$parser->parseFile();
		$op = new TV_Outputter($parser->tv);
		if ($op->parseTV()){
			echo $op->getOutput();
		}else{
			echo '<h1>Error</h1>';
		}
	}
	
	include('footer.php');
	
?>