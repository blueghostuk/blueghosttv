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
	
	header('Content-type: application/atom+xml');
	
	$dbase   	= 'blueghos_tv';
	$Database 	= new TV_DBConnection();
	$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
	$cache = new Cache;
	if (isset($_REQUEST['series'])){
		//echo $Database->ajaxSeries($_REQUEST['query']);
		if (strpos($_REQUEST['series'], 'crid') === false){/*bleb prog*/
			$_REQUEST['series'] = str_replace("\\'", "'", $_REQUEST['series']);
			//echo $_REQUEST['series'];
			if ($cache->checkRSSCache('series_'.$_REQUEST['series'])){
				$series = $Database->getBlebSeries($_REQUEST['series']);
				$op = new TV_Outputter(null);
				if ($op->returnParseSeriesResultsForRSS($series['progs'], $series['sched'])){
					echo $op->getOutput();
					$cache->createRSSFile('series_'.$_REQUEST['series'], $op->getOutput());
				}else{
					echo '<h1>Error</h1>';
				}
			}else{
				echo $cache->getRSSFile('series_'.$_REQUEST['series']);
			}
		}else{/*tv-any prog*/
			$file = str_replace("crid://bbc.co.uk/", "crid_bbc.co.uk_", $_REQUEST['series']);
			if ($cache->checkRSSCache('series_'.$file)){
				$series = $Database->getSeries($_REQUEST['series']);
				$op = new TV_Outputter(null);
				if ($op->returnParseSeriesResultsForRSS($series['progs'], $series['sched'])){
					echo $op->getOutput();
					$cache->createRSSFile('series_'.$file, $op->getOutput());
				}else{
					echo '<h1>Error</h1>';
				}
			}else{
				echo $cache->getRSSFile('series_'.$file);
			}
		}
	}
	if (isset($_REQUEST['genre'])){
		if ($cache->checkRSSCache('genre_'.$_REQUEST['genre'])){
			$progs = $Database->findProgsWithGenre($_REQUEST['genre']);
			$op = new TV_Outputter($_REQUEST['genre']);
			if ($op->parseGenreListForRSS($progs['progs'], $progs['sched'])){
				echo $op->getOutput();
				$cache->createRSSFile('genre_'.$_REQUEST['genre'], $op->getOutput());
			}else{
				echo '<h1>Error</h1>';
			}
		}else{
			echo $cache->getRSSFile('genre_'.$_REQUEST['genre']);
		}
	}
	if (isset($_REQUEST['channel'])){
		if ($_REQUEST['date'] == 'today'){
			$date = date("Ymd");
		}else{
			$date = $_REQUEST['date'];
		}
		if ($cache->checkRSSCache('channel_'.$_REQUEST['channel'].'_'.$date)){
			$sChannel = $Database->getChannel($_REQUEST['channel'], $date);
			$op = new TV_Outputter($sChannel);
			if ($op->parseSingleChannelForRSS($date_text)){
				echo $op->getOutput();
				$cache->createRSSFile('channel_'.$_REQUEST['channel'].'_'.$date, $op->getOutput());
			}else{
				echo '<h1>Error</h1>';
			}
		}else{
			echo $cache->getRSSFile('channel_'.$_REQUEST['channel'].'_'.$date);
		}
	}
	if (isset($_REQUEST['search'])){
		if ($cache->checkRSSCache('search_'.$_REQUEST['search'])){
			$series = $Database->ajaxResults($_REQUEST['search']);
			$op = new TV_Outputter(null);
			if ($op->parseSearchResultsForRSS($series['progs'], $series['sched'], $_REQUEST['search'])){
				echo $op->getOutput();
				$cache->createRSSFile('search_'.$_REQUEST['search'], $op->getOutput());
			}else{
				echo '<h1>Error</h1>';
			}
		}else{
			echo $cache->getRSSFile('search_'.$_REQUEST['search']);
		}
	}
	if (isset($_REQUEST['nn'])){
		//if ($cache->checkRSSCache('nn_'.$_REQUEST['nn'])){
			$op = new TV_Outputter(null);
			$date = date("Ymd", time());
			$sChannel = $Database->getChannel($_REQUEST['nn'], $date);
			$nn = $sChannel->getNowAndNext(time());
			if ($op->parseNNForRSS($nn['progs'], $nn['sched'], $sChannel->title)){
				echo $op->getOutput();
				//$cache->createRSSFile('nn_'.$_REQUEST['nn'], $op->getOutput());
			}else{
				echo '<h1>Error</h1>';
			}
		//}else{
			//echo $cache->getRSSFile('nn_'.$_REQUEST['nn']);
		//}
	}
	if (isset($_REQUEST['nnp'])){
		echo"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		echo"<feed xmlns=\"http://www.w3.org/2005/Atom\">\n";
		echo"\t<title>BlueGhost PG Now &amp; Next @ ".date("g:i", time())."</title>\n";
		echo"\t<subtitle>Now &amp; Text TV Data</subtitle>\n";
		echo"\t<link href=\"http://tv.blueghost.co.uk/nn.php\"/>\n";
		echo"\t<author>\n";
		echo"\t\t<name>Michael Pritchard</name>\n";
		echo"\t</author>\n";
		echo"\t<link rel=\"self\" href=\"http://tv.blueghost.co.uk/feeds/atom/nnp/".str_replace(" ", "+", $_REQUEST['nnp'])."\"/>\n";
		echo"\t<id>urn:uuid:atom.nnp:".str_replace(" ", "+", $_REQUEST['nnp'])."</id>\n";
		echo"\t<updated>".date("Y-m-d\TH:i:s\Z")."</updated>\n";
		$date = date("Ymd", time());
		$token = explode("+", $_REQUEST['nnp']);
		$token = explode(" ", $_REQUEST['nnp']);
		//echo $total_op;
		foreach ($token as $tok){
			//echo '<br />token '.$tok.' , ';
			$op = new TV_Outputter(null);
			$sChannel = $Database->getChannel($tok, $date);
			$nn = $sChannel->getNowAndNext(time());
			if ($op->parseMultipleNNForATOM($nn['progs'], $nn['sched'], $sChannel->title)){
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
		$total_op = "</feed>";
		echo $total_op;
	}
	
?>