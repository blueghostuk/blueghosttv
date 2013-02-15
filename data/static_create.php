<?php

	require('../includes/paths.php');
	require('../includes/io.php');
	require('../includes/XML_Parser.php');
	require('../includes/TV_Channel.php');
	require('../includes/TV_Program.php');
	require('../includes/TV_Related_Info.php');
	require('../includes/TV_Genre.php');
	require('../includes/TV_AVAttributes.php');
	require('../includes/TV_Program_Schedule.php');
	require('../includes/TV_ProgramParser.php');
	require('../includes/TV_ScheduleParser.php');
	require('../includes/TV_Outputter.php');
	require('../includes/TV_View.php');
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
	
	//menu + google_ig
	$google_ig = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?> 
<Module>
	<ModulePrefs 
		title=\"BlueGhost PG: Channel Listing\"
		title_url=\"http://tv.blueghost.co.uk/\"
		directory_title=\"TV Listings (UK only)\"
		author=\"Michael Pritchard\"
		render_inline=\"optional\" /> 
	<UserPref 
		name=\"channel\" 
		display_name=\"Channel\" 
		required=\"true\" 
		data_type=\"enum\"
		default_value=\"1\">";
	
	$menu =  "<div class=\"chanList\">\n";
	foreach ($channels as $channel){
		$menu .="<a href=\"/channel/".$channel->id."<?php echo $menu_day;?>\">".$channel->title."</a>\n";
		$google_ig .= "<EnumValue value=\"".$channel->id."\" display_value=\"".$channel->title."\"/>";
	}
	$menu .= "</div>\n";
	$google_ig .= "</UserPref>
	<Content 
		type=\"url\" 
		href=\"http://tv.blueghost.co.uk/feeds/html/single/__UP_channel__\" />	
</Module>";
	$file = $dir.'cache/html/menu.php';
	write_header($file, $menu);
	
	$file = $dir.'google_ig_channel.xml';
	write_header($file, $google_ig);
	
	for($days = 0; $days < 8; $days++){
		echo "<br />Loop for day ".$days;
		flush();ob_flush();
		$time = time() + ($days * 24 * 60 * 60);
		foreach ($channels as $channel){
			$dateText = date("l jS F Y", $time);
			$date = date("Ymd", $time);
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
				flush();ob_flush();
			}
		}
	}
?>