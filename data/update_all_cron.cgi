#!/usr/bin/php -c /home/blueghos/public_html/tv/data/php.ini

<?php

		//set timeout in seconds
		print ini_set("max_execution_time", 3600) . "<BR />";
		print ini_set("max_execution_time", 3600) . "<BR />";
	
		//functions
		function delfile($str){
			foreach(glob($str) as $fn) {
				unlink($fn);
			}
		}
		
		function rmdirr($dir) {
			if($objs = glob($dir."/*")){
				foreach($objs as $obj) {
					is_dir($obj)? rmdirr($obj) : unlink($obj);
				}
			}
			rmdir($dir);
		}
		
		function chmod_R($path, $filemode) {
		   if (!is_dir($path))
			   return chmod($path, $filemode);
		
		   $dh = opendir($path);
		   while ($file = readdir($dh)) {
			   if($file != '.' && $file != '..') {
				   $fullpath = $path.'/'.$file;
				   if(!is_dir($fullpath)) {
					 if (!chmod($fullpath, $filemode))
						 return FALSE;
				   } else {
					 if (!chmod_R($fullpath, $filemode))
						 return FALSE;
				   }
			   }
		   }
		 
		   closedir($dh);
		  
		   if(chmod($path, $filemode))
			 return TRUE;
		   else
			 return FALSE;
		} 
		
		//includes
		require('../includes/paths.php');
		require('../includes/archive.php');
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
		require('/home/blueghos/db.php');
		require('../includes/DB_Connection.php');
		require('../includes/TV_DBConnection.php');
		require('../includes/Bleb_ProgramParser.php');
		require('../includes/Bleb_Program.php');
		require('../includes/Bleb_AVAttributes.php');
		require('../includes/Bleb_Program_Schedule.php');	
		require('../includes/Bleb_Outputter.php');
		require('../includes/Bleb_Channel.php');
		require('../includes/io.php');
		require('../includes/TV_View.php');
		
		//get bbc data files
		$url = 'http://backstage.bbc.co.uk/feeds/tvradio/';
		$date = date("Ymd");//20051030
		$file = $date.'.tar.gz';//20051030.tar.gz

		echo '<p>Making Dir '.$dir.'data/'.$date.'/'.'</p>';
		flush();ob_flush();
		@mkdir($dir.'data/'.$date.'/',0777);
		echo '<p>Downloading '.$url.$file.' to '.$dir.'data/'.$file.'</p>';
		flush();ob_flush();
		if (!copy($url.$file, $dir.'data/'.$file))
                {
                     die('Error Downloading File From '.$url.$file);
                }
		echo '<p>Finished Download, extracting contents</p>';
		flush();ob_flush();
		
		//unzip
		$test = new gzip_file($dir.'data/'.$file);
                echo '<p>Created Zip Instance</p>';
		flush();ob_flush();
		$test->set_options(array('overwrite' => 1)); //overwrite files
                echo '<p>Calling extract_files</p>';
		flush();ob_flush();
		$test->extract_files();
		
		chmod_r($dir.'data/'.$date.'/',0777);
		
		//move unzipped
		echo '<p>Extraction finished</p>';
		flush();ob_flush();
		
		//delete zip file
		echo '<p>deleting tar.gz file</p>';
		flush();ob_flush();
		delfile("*.tar.gz");
		echo '<p>Processing files</p>';
		flush();ob_flush();
		
		//database connection
		$dbase   	= 'blueghos_tv';
		$Database 	= new TV_DBConnection();
		$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
		
		echo '<p>clearing database</p>';
		flush();ob_flush();
		$Database->clearForwardSchedule($date);
		echo '<p>cleared db</p>';
		flush();ob_flush();

		//get list of all channels
		$channels = $Database->getAllChannels();
		
		$date = date("Ymd", time());
		$date += "000000";
		$folder = date("Ymd", time());
		$bleb = array();
		$tv_any = array();
		foreach ($channels as $achannel){
			if ($achannel->isTVAny()){//run tv-anytime parser
				for($day=0; $day <=7 ;$day++){
					$sChannel = $achannel;
					
					$time = time() + ($day * 24 * 60 * 60);
					$file = $dir.'data/'.$folder.'/'.date("Y", $time).date("m", $time).date("d", $time).$sChannel->serviceId.'_pi.xml';
					
					$parser = new TV_ProgramParser($file);
					$parser->setChannel($sChannel);
					$parser->parseFile();
					
					$chan = $parser->tv;
					$file = $dir.'data/'.$folder.'/'.date("Y", $time).date("m", $time).date("d", $time).$sChannel->serviceId.'_pl.xml';
					
					$parser = new TV_ScheduleParser($file, $chan);
					$parser->parseFile();
				}
				$Database->addChannelSchedule($parser->tv);
				echo '<br />'.$parser->tv->title.' -  ('.count($parser->tv->schedule).'):'.mysql_error().'<br />';
				flush();ob_flush();
			}
		}

		//remove xml files
		echo '<p>deleting xml files</p>';
		flush();ob_flush();
		rmdirr($dir.'data/'.$folder.'/');
			
		//updater_bleb.php
		rmdirr($dir.'data/bleb-XML/');
		mkdir($dir.'data/bleb-XML/',0777);
		echo '<p>Made '.$dir.'data/bleb-XML/</p>';
		flush();ob_flush();ob_flush();ob_flush();
		for ($i=-1; $i <= 7; $i++){
			mkdir($dir.'data/bleb-XML/'.$i.'/',0777);
		}
		echo '<p>Made day directories</p>';
		flush();ob_flush();
		for ($i=-1; $i <= 7; $i++){
			chmod_R($dir.'data/bleb-XML/'.$i, 0777);
		}
		echo '<p>chmodded day directories</p>';
		flush();ob_flush();
		
		
		$channels = 'bbc_hd,ch4,five,s4c,fx,abc1,boomerang';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		$name = 'blebtv.tar.gz';
		$file = $dir.'data/'.$name;
		echo '<p>Downloading '.$url.' to '.$file.'</p>';
		flush();ob_flush();
		if (copy($url, $file)){
			echo '<p>Finished Download, extracting contents of '.$name.'</p>';
			flush();ob_flush();
			$test = new gzip_file($name);
			$test->set_options(array('overwrite' => 1)); //overwrite files
			$test->extract_files();
			echo '<p>Extraction finished</p>';
			flush();ob_flush();
			echo '<p>deleting tar.gz file</p>';
			flush();ob_flush();
			delfile("*.tar.gz");
			sleep(2);
		}else{
			echo '<p>Failed Download: '.$url.'</p>';
			flush();ob_flush();
		}
		
		$channels = 'bravo,british_eurosport,cartoon_network,challenge,oneword,paramount,paramount2';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		$name = 'blebtv.tar.gz';
		$file = $dir.'data/'.$name;
		echo '<p>Downloading '.$url.' to '.$file.'</p>';
		flush();ob_flush();
		if (copy($url, $file)){
			echo '<p>Finished Download, extracting contents of '.$name.'</p>';
			flush();ob_flush();
			$test = new gzip_file($name);
			$test->set_options(array('overwrite' => 1)); //overwrite files
			$test->extract_files();
			echo '<p>Extraction finished</p>';
			flush();ob_flush();
			echo '<p>deleting tar.gz file</p>';
			flush();ob_flush();
			delfile("*.tar.gz");
			sleep(2);
		}else{
			echo '<p>Failed Download: '.$url.'</p>';
			flush();ob_flush();
		}
		
		$channels = 'scifi,sky_cinema1,sky_cinema2,sky_movies1,sky_movies2,sky_movies3,sky_movies4,sky_movies5,sky_movies6,sky_movies7';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		echo '<p>Downloading '.$url.' to '.$file.'</p>';
		flush();ob_flush();
		if (copy($url, $file)){
			echo '<p>Finished Download, extracting contents of '.$name.'</p>';
			flush();ob_flush();
			$test = new gzip_file($name);
			$test->set_options(array('overwrite' => 1)); //overwrite files
			$test->extract_files();
			echo '<p>Extraction finished</p>';
			flush();ob_flush();
			echo '<p>deleting tar.gz file</p>';
			flush();ob_flush();
			delfile("*.tar.gz");
			sleep(2);
		}else{
			echo '<p>Failed Download: '.$url.'</p>';
			flush();ob_flush();
		}
		
		$channels = 'sky_movies8,sky_movies9,sky_one,sky_two,sky_sports1,sky_sports2,sky_sports3,sky_sports_news';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		echo '<p>Downloading '.$url.' to '.$file.'</p>';
		flush();ob_flush();
		if (copy($url, $file)){
			echo '<p>Finished Download, extracting contents of '.$name.'</p>';
			flush();ob_flush();
			$test = new gzip_file($name);
			$test->set_options(array('overwrite' => 1)); //overwrite files
			$test->extract_files();
			echo '<p>Extraction finished</p>';
			echo '<p>deleting tar.gz file</p>';
			delfile("*.tar.gz");
			sleep(2);
		}else{
			echo '<p>Failed Download: '.$url.'</p>';
			flush();ob_flush();
		}
		
		$channels = 'sky_sports_xtra,sky_three,sky_travel,tcm,uk_bright_ideas,uk_drama,uk_gold,uk_history,uk_style,uktv_documentary';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		echo '<p>Downloading '.$url.' to '.$file.'</p>';
		flush();ob_flush();
		if (copy($url, $file)){
			echo '<p>Finished Download, extracting contents of '.$name.'</p>';
			flush();ob_flush();
			$test = new gzip_file($name);
			$test->set_options(array('overwrite' => 1)); //overwrite files
			$test->extract_files();
			echo '<p>Extraction finished</p>';
			echo '<p>deleting tar.gz file</p>';
			delfile("*.tar.gz");
			sleep(2);
		}else{
			echo '<p>Failed Download: '.$url.'</p>';
			flush();ob_flush();
		}
		
		
		$channels = 'discovery,discovery_kids,discovery_real_time,disney,e4,film_four,ftn,living_tv';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		echo '<p>Downloading '.$url.' to '.$file.'</p>';
		flush();ob_flush();
		if (copy($url, $file)){
			echo '<p>Finished Download, extracting contents of '.$name.'</p>';
			flush();ob_flush();
			$test = new gzip_file($name);
			$test->set_options(array('overwrite' => 1)); //overwrite files
			$test->extract_files();
			echo '<p>Extraction finished</p>';
			flush();ob_flush();
			echo '<p>deleting tar.gz file</p>';
			flush();ob_flush();
			delfile("*.tar.gz");
			sleep(2);
		}else{
			echo '<p>Failed Download: '.$url.'</p>';
			flush();ob_flush();
		}
		
		$channels = 'men_and_motors,more4,mtv,nick_junior,nickelodeon,uktv_people,vh1,sky_movies10,uktv_g2';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		echo '<p>Downloading '.$url.' to '.$file.'</p>';
		flush();ob_flush();
		if (copy($url, $file)){
			echo '<p>Finished Download, extracting contents of '.$name.'</p>';
			flush();ob_flush();
			$test = new gzip_file($name);
			$test->set_options(array('overwrite' => 1)); //overwrite files
			$test->extract_files();
			echo '<p>Extraction finished</p>';
			flush();ob_flush();
			echo '<p>deleting tar.gz file</p>';
			flush();ob_flush();
			delfile("*.tar.gz");
		}else{
			echo '<p>Failed Download: '.$url.'</p>';
			flush();ob_flush();
		}
		
		for ($i=-1; $i <= 7; $i++){
			chmod_R($dir.'data/bleb-XML/'.$i, 0777);
		}
		echo '<p>Processing files</p>';
		
		
		$channels = $Database->getAllChannels();
		foreach ($channels as $achannel){
			if (!$achannel->isTVAny()){//run bleb parser
				for($day=-1; $day <=7 ;$day++){
					$folder = 'bleb-XML/'.$day;
					$time = mktime(0,0,0) + ($day * 24 * 60 * 60);
					
					$sChannel = $achannel;
					$file = $dir.'data/'.$folder.'/'.$sChannel->serviceId.'.xml';
					$parser = new Bleb_ProgramParser($file);
					
					$parser->setChannel($sChannel, $time);
					$parser->parseFile();
				}
				$Database->addBlebChannel($parser->tv);
				echo '<br />'.$parser->tv->title.' -  ('.count($parser->tv->schedule).'):'.mysql_error().'<br />';
				flush();ob_flush();
			}
		}
		
		
		
		//STATIC_CREATE.PHP
		$channels = $Database->getAllChannels();
		foreach ($channels as $chan){
			mkdir($dir.'cache/html/single/'.$chan->id);
		}
		
		//menu + google_ig
	$google_ig = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?> 
<Module>
	<ModulePrefs 
		title=\"BlueGhost PG: Channel Listing\"
		description=\"TV Listings (UK only)\"
		title_url=\"http://www.blueghosttv.co.uk/\"
		directory_title=\"UK TV Listings\"
		author=\"Michael Pritchard\"
		author_email=\"blueghostuk[remove this text]+no.spam.please@gmail.com\"
		author_location=\"Telford, UK\"
		render_inline=\"optional\"
		scrolling=\"true\" /> 
	<UserPref 
		name=\"channel\" 
		display_name=\"Channel\" 
		required=\"true\" 
		datatype=\"enum\"
		default_value=\"1\">";
	
	foreach ($channels as $channel){
		$google_ig .= "<EnumValue value=\"".$channel->id."\" display_value=\"".$channel->title."\"/>";
	}
	
	$google_ig .= "</UserPref>
	<Content 
		type=\"url\" 
		href=\"http://www.blueghosttv.co.uk/feeds/html/channel/__UP_channel__\" />	
</Module>";
	
	
	
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