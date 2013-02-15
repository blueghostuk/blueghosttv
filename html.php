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
	$cache = new Cache;
	
	header('Content-type: text/html');
	
	$dbase   	= 'blueghos_tv';
	$Database 	= new TV_DBConnection();
	$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
	$cache = new Cache;
	if (isset($_REQUEST['channel']))
	{
		$cache_time = time() - (6*60*60); //6 hours
		$cache_file = 'html/feeds/channel_'.$_REQUEST['channel'].'.html';
		if ($cache->checkCacheWithTime($cache_file,$cache_time))
		{
			$time = time();
			//$dateText = date("l jS F Y", $time);
			//$date = date("Ymd", $time);
			$date = date("Y-m-d", $time);
			$sChannel = $Database->getChannel($_REQUEST['channel'], $date);
			$op = new TV_Outputter($sChannel);
			//$op->parseSingleChannelForGoogle($date_text, $date);
			$op->parseSingleChannelForGoogle();
			$total_op = "<link rel=\"stylesheet\" type=\"text/css\" href=\"http://www.blueghosttv.co.uk/styles/style.css\">\n<div id=\"google_ig_nnp\">\n";
			echo $total_op;
			$total_op = "<script type=\"text/javascript\">
				function blueghostTVOnLoad(){
					var d = new Date();
					var h = d.getHours();
					document.location = \"#hour_\"+h; 
				}
				</script><body onload=\"blueghostTVOnLoad()\">";
			echo $total_op;
			echo $op->getOutput();
			$cache->createFile($cache_file,  $op->getOutput());
			$total_op = "<br />provided by <a href=\"http://www.blueghosttv.co.uk\" target=\"_blank\" class=\"ext_link\">blueghosttv.co.uk</a></div>";
			echo $total_op;
			$total_op = "<br />supported by <a href=\"http://backstage.bbc.co.uk\" target=\"_blank\" class=\"ext_link\">backstage.bbc.co.uk</a></div>";
			echo $total_op;
		}
		else
		{
			echo '<!--Cached-->';
			$total_op = "<link rel=\"stylesheet\" type=\"text/css\" href=\"http://www.blueghosttv.co.uk/styles/style.css\">\n<div id=\"google_ig_nnp\">\n";
			echo $total_op;
			$total_op = "<script type=\"text/javascript\">
				function blueghostTVOnLoad(){
					var d = new Date();
					var h = d.getHours();
					document.location = \"#hour_\"+h; 
				}
				</script><body onload=\"blueghostTVOnLoad()\">";
			echo $total_op;
			echo $cache->getFile($cache_file);
			$total_op = "<br />provided by <a href=\"http://www.blueghosttv.co.uk\" target=\"_blank\" class=\"ext_link\">blueghosttv.co.uk</a></div>";
			echo $total_op;
			$total_op = "<br />supported by <a href=\"http://backstage.bbc.co.uk\" target=\"_blank\" class=\"ext_link\">backstage.bbc.co.uk</a></div>";
			echo $total_op;
		}
	}
	
	if (isset($_REQUEST['nnp']))
	{
		$cache_time = time() - (5*60); //5 mins
		$cache_file = 'html/feeds/nnp_'.$_REQUEST['nnp'].'.html';
		if ($cache->checkCacheWithTime($cache_file,$cache_time))
		{
			//$date = date("Ymd", time());
			$date = date("Y-m-d", $time);
			$token = explode("+", $_REQUEST['nnp']);
			$token = explode(" ", $_REQUEST['nnp']);
			//print_r($token);
			//echo 'total_op  <br />';
			$total_op = "<link rel=\"stylesheet\" type=\"text/css\" href=\"http://www.blueghosttv.co.uk/styles/style.css\">\n<div id=\"google_ig_nnp\">\n";
			echo $total_op;
			foreach ($token as $tok)
			{
				//echo '<br />token '.$tok.' , ';
				$op = new TV_Outputter(null);
				$sChannel = $Database->getChannel($tok, $date);
				$nn = $sChannel->getNowAndNext(time());
				if ($op->parseMultipleNNForHTML($nn['progs'], $nn['sched'], $sChannel->title))
				{
					$opo .= $op->getOutput();
					echo /*$total_op +=*/ $op->getOutput();
				}
			}
			$cache->createFile($cache_file, $opo);
			$total_op = "<br />provided by <a href=\"http://www.blueghosttv.co.uk\" target=\"_blank\" class=\"ext_link\">blueghosttv.co.uk/a></div>";
			echo $total_op;
			$total_op = "<br />supported by <a href=\"http://backstage.bbc.co.uk\" target=\"_blank\" class=\"ext_link\">backstage.bbc.co.uk</a></div>";
			echo $total_op;
			
		}
		else
		{
			echo '<!--Cached-->';
			$total_op = "<link rel=\"stylesheet\" type=\"text/css\" href=\"http://www.blueghosttv.co.uk/styles/style.css\">\n<div id=\"google_ig_nnp\">\n";
			echo $total_op;
			echo $cache->getFile($cache_file);
			$total_op = "<br />provided by <a href=\"http://www.blueghosttv.co.uk\" target=\"_blank\" class=\"ext_link\">blueghosttv.co.uk</a></div>";
			echo $total_op;
			$total_op = "<br />supported by <a href=\"http://backstage.bbc.co.uk\" target=\"_blank\" class=\"ext_link\">backstage.bbc.co.uk</a></div>";
			echo $total_op;
		}
	}
	
?>