<?php

	require('paths.php');
	require('io.php');
	require('XML_Parser.php');
	require('TV_Channel.php');
	require('TV_Program.php');
	require('TV_Related_Info.php');
	require('TV_Genre.php');
	require('TV_AVAttributes.php');
	require('TV_Program_Schedule.php');
	require('TV_ProgramParser.php');
	require('TV_ScheduleParser.php');
	require('TV_Outputter.php');
	require('TV_View.php');
	require('/home/blueghos/db.php');
	require('DB_Connection.php');
	require('TV_DBConnection.php');
	require('Bleb_ProgramParser.php');
	require('Bleb_Program.php');
	require('Bleb_AVAttributes.php');
	require('Bleb_Program_Schedule.php');	
	require('Bleb_Outputter.php');
	require('Bleb_Channel.php');
	
	$dbase   	= 'blueghos_tv';
	$Database 	= new TV_DBConnection();
	$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
	
	$channels = $Database->getAllChannels();
	//echo "<br />Making dirs";
	foreach ($channels as $chan){
		mkdir($dir.'cache/html/single/'.$chan->id);
		//echo "<br />Making dir:".$dir."cache/html/single/".$chan->id;
	}
	
	//menu
	$menu =  "<div class=\"chanList\">\n";
	foreach ($channels as $channel){
		$menu .="<a href=\"/channel/".$channel->id."\">".$channel->title."</a>\n";
	}
	$menu .= "</div>\n";
	$file = $dir.'cache/html/menu.html';
	write_header($file, $menu);
	
	for($days = 0; $days < 8; $days++){
		echo "<br />Loop for day ".$days;
		flush();
		$time = time() + ($days * 24 * 60 * 60);
		foreach ($channels as $channel){
			$dateText = date("l jS F Y", $time);
			//$date = date("Ymd", $time);
			$date = date("Y-m-d", $time);
			$sChannel = $Database->getChannel($channel->id, $date);
			if ($days == 0){
				$date = 'today';
				$xml_text =  '- for Today';
			}else{
				$xml_text =  '- for '.$dateText;
			}
			$xml_link = '/feeds/rss/channel/'.$sChannel->id.'/'.$date;
			$xml_title = 'RSS Feed for CHANNEL:'.$sChannel->title.$xml_text;
			if ($sChannel->isTVAny()){	
				$op = new TV_Outputter($sChannel);
			}else{
				$op = new Bleb_Outputter($sChannel);
			}
			$date_text = date("l jS F Y", $time);
			
			if ($op->parseSingleChannel($date_text, $date)){
				$file_date = date("Ymd", $time);
				$file = $dir.'cache/html/single/'.$sChannel->id.'/'.$file_date.'.html';
				write_header($file, $op->getOutput());
				echo "<br />Wrote data for ".$sChannel->id." on ".$file_date."";
			}
		}
	}
	/*for ($day = 0; $day <=7; $day++)(
		//echo "<br />Loop for day ".$day;
		//$time = time() + ($day * 24 * 60 * 60);
		/*foreach ($channels as $channel){
			//$time = time() + ($day * 24 * 60 * 60);
		
			$dateText = date("l jS F Y", $time);
			
			$date = date("Ymd", $time);
			$sChannel = $Database->getChannel($channel->id, $date);
			
			if ($day == 0){
				$date = 'today';
				$xml_text =  '- for Today';
			}else{
				$xml_text =  '- for '.$dateText;
			}
			
			$xml_link = '/feeds/rss/channel/'.$sChannel->id.'/'.$date;
			$xml_title = 'RSS Feed for CHANNEL:'.$sChannel->title.$xml_text;
			if ($sChannel->isTVAny()){		
				//include('header.php');
				//echo $menu;
				$op = new TV_Outputter($sChannel);
			}else{
				//include('header.php');
				//echo $menu;
				$op = new Bleb_Outputter($sChannel);
			}
			$date_text = date("l jS F Y", $time);
			
			if ($op->parseSingleChannel($date_text, $date)){
				$file_date = date("Ymd", $time);
				$file = $dir.'cache/html/single/'.$sChannel->id.'/'.$file_date.'.html';
				write_header($file, $op->getOutput());
				echo "<br />Wrote data for ".$sChannel->id." on ".$file_date."";
			}
		}*/
	/*}*/
	
?>