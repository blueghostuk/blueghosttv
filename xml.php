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
	
	header('Content-type: text/xml');
	
	$dbase   	= 'blueghos_tv';
	$Database 	= new TV_DBConnection();
	$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
	$cache = new Cache;
	if (isset($_REQUEST['channel'])){
		//$date = date("Ymd");
		$date = date("Y-m-d");
		$cache_time = time() - (6*60*60); //6 hours
		$cache_file = 'xml/channel_'.$_REQUEST['channel'].'_'.$date.'.xml';
		//if ($cache->checkRSSCache('xml_channel_'.$_REQUEST['channel'].'_'.$date)){
		if ($cache->checkCacheWithTime($cache_file,$cache_time)){
			$sChannel = $Database->getChannel($_REQUEST['channel'], $date);
			$op = new TV_Outputter($sChannel);
			if ($op->parseSingleChannelForXML()){
				echo $op->getOutput();
				$cache->createFile($cache_file,  $op->getOutput());
				//$cache->createRSSFile('xml_channel_'.$_REQUEST['channel'].'_'.$date, $op->getOutput());
			//}else{
				//echo '<h1>Error</h1>';
			}
		}else{
			echo $cache->getFile($cache_file);
			//echo $cache->getRSSFile('xml_channel_'.$_REQUEST['channel'].'_'.$date);
		}
	}
	if (isset($_REQUEST['channellist'])){
		echo"<?xml version=\"1.0\"?>\n";
		echo"\t<channel_list>\n";
		$cache_time = time() - (7*24*60*60); //61 week hours
		$cache_file = 'xml/channel_list.xml';
		if ($cache->checkCacheWithTime($cache_file,$cache_time)){
			$channels = $Database->getAllChannels();
			$xml_op = "";
			foreach ($channels as $chan){
				echo "\t\t<channel data=\"".$chan->id."\" label=\"".$chan->title."\" />\n";
				$xml_op .= "\t\t<channel data=\"".$chan->id."\" label=\"".$chan->title."\" />\n";
			}
			$cache->createFile($cache_file,  $xml_op);
		}else{
			echo $cache->getFile($cache_file);
		}
		echo"\t</channel_list>\n";
	}
	
	if (isset($_REQUEST['nnp'])){
		$total_op = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$total_op .="<request>\n";
		//$date = date("Ymd", time());
		$date = date("Y-m-d", time());
		$token = explode("+", $_REQUEST['nnp']);
		$token = explode(" ", $_REQUEST['nnp']);
		//print_r($token);
		//echo 'total_op  <br />';
		echo $total_op;
		foreach ($token as $tok){
			//echo '<br />token '.$tok.' , ';
			$op = new TV_Outputter(null);
			$sChannel = $Database->getChannel($tok, $date);
			$nn = $sChannel->getNowAndNext(time());
			if ($op->parseMultipleNNForXML($nn['progs'], $nn['sched'], $sChannel->title)){
				//$opo = $op->getOutput();
				echo /*$total_op +=*/ $op->getOutput();
				//$total_op += $op->getOutput();
				//echo 'total_op  <br />';
				//echo $total_op;
			}else{
				/*$total_op += "\t\t<item>\n";
				$total_op += "\t\t\t<title>".$sChannel->title." - No Data</title>\n";
				$total_op += "\t\t\t<link>http://tv.blueghost.co.uk/channel/".$sChannel->id."</link>\n";
				$total_op += "\t\t</item>\n";*/
			}
		}
		$total_op = "</request>";
		echo $total_op;
	}
	
?>