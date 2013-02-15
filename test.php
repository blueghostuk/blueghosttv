<?php
	require('includes/paths.php');
	require('includes/archive.php');
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
	require('includes/io.php');
	require('includes/TV_View.php');
	
	$dbase   	= 'blueghos_tv';
	$Database 	= new TV_DBConnection();
	$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
	
	$channels = $Database->getAllChannels();
	
	for($days = 0; $days < 8; $days++)
	{
		echo "<br />Loop for day ".$days;
		flush();ob_flush();
		$time = time() + ($days * 24 * 60 * 60);
		foreach ($channels as $channel)
		{
			$dateText = date("l jS F Y", $time);
			//$date = date("Ymd", $time);
			$date = date("Y-m-d", $time);
			$sChannel = $Database->getChannel($channel->id, $date);
			if ($days == 0)
			{
				$date = 'today';
				$xml_text =  '- for Today';
			}
			else
			{
				$xml_text =  '- for '.$dateText;
			}
			$xml_link = '/feeds/rss/channel/'.$sChannel->id.'/'.$date;
			$xml_title = 'RSS Feed for CHANNEL:'.$sChannel->title.$xml_text;
			if ($sChannel->isTVAny())
			{	
				$op = new TV_Outputter($sChannel);
			}
			else
			{
				$op = new Bleb_Outputter($sChannel);
			}
			$date_text = date("l jS F Y", $time);
				
			if ($op->parseSingleChannel($date_text, $date))
			{
				$file_date = date("Ymd", $time);
				$file = $dir.'cache/html/single/'.$sChannel->id.'/'.$file_date.'.html';
				echo $op->getOutput();
				echo "<br />Wrote data for ".$sChannel->id." on ".$file_date."";
				flush();ob_flush();
			}
			break;
		}
		break;
	}	