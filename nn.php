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
	
	
	$dbase   	= 'blueghos_tv';
	$Database 	= new TV_DBConnection();
	$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
		
	//$date = date("Ymd", $time);
	$date = date("Y-m-d", $time);
	include('header.php');
	
	$channels = $Database->getAllChannels();
	//$op = new TV_Outputter(null);
	echo '<div class="rss_links" style="text-align:center;">';
	echo '| Now &amp; Next RSS Feed Builder: <select id="chanSel">';
	foreach ($channels as $channel){
		echo"<option value=\"".$channel->id."\">".$channel->title."</option>\n";
	}
	echo '</select><input type="button" onclick="javascript:addChannel()" value="Add Channel" /><input type="button" onclick="javascript:clearChannels(\'rss_feed_box\', \'http://www.blueghosttv.co.uk/feeds/rss/nnp/\')" value="Reset" /><input id="rss_feed_box" size="50" type="text" value="http://www.blueghosttv.co.uk/feeds/rss/nnp/" /> | ';
	echo '</div>';
	
	echo '<div class="rss_links" style="text-align:center;">';
	echo '| Now &amp; Next <a href="http://www.google.com/ig" target="_blank">google.com/ig</a> Builder: <select id="googleChanSel">';
	foreach ($channels as $channel){
		echo"<option value=\"".$channel->id."\">".$channel->title."</option>\n";
	}
	echo '</select><input type="button" onclick="javascript:addGoogleChannel()" value="Add Channel" /><input type="button" onclick="javascript:clearChannels(\'google_feed_box\', \'\')" value="Reset" /><input id="google_feed_box" size="50" type="text" value="" /> | ';
	echo '<br />To use in <a href="http://www.google.com/ig" target="_blank">google.com/ig</a>, go to <a href="http://www.google.com/ig" target="_blank">google.com/ig</a>, Click "Add Content", select the "Create a Section" option and enter "http://www.blueghosttv.co.uk/google_ig_channel.xml" in the box and press "Go". You can then customise the module using the numbers generated in the box above';
	echo '</div>';
	foreach ($channels as $channel){
		//if ($channel->isTVAny()){
			$op = new TV_Outputter(null);
			$sChannel = $Database->getChannel($channel->id,$date);
			$nn = $sChannel->getNowAndNext(time());
			if ($op->parseNNForChannel($nn['progs'], $nn['sched'], $channel->title, $channel->id)){
				echo $op->getOutput();
			}else{
				echo '<h1>Error</h1>';
			}
		//}
	}
	include('footer.php');
?>