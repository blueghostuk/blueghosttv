<?php

	print ini_set("max_execution_time", 3600) . "<BR />";
	print ini_set("max_execution_time", 3600) . "<BR />";

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
	
	if ($_REQUEST['user'] == 'abcde'){
	
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
		
		/*get bbc data files*/
		$url = 'http://backstage.bbc.co.uk/feeds/tvradio/';
		$date = date("Ymd", time());//20051030
		$file = $date.'.tar.gz';//20051030.tar.gz
		
		//disbaled
		//exec('wget '.$url.$file.'');
		//exec('tar zxf '.$file.'');
		//exec('mv -f '.$date.'/* .');
		//exec('rm -rf '.$date.'/');
		//exec('rm -rf '.$file.'');
		
		//alternative
		echo '<h2>Downloading '.$url.$file.' to '.$dir.'data/'.$file.'</h2>';
		flush();ob_flush();
		copy($url.$file, $dir.'data/'.$file);
		echo '<h2>Making Dir '.$dir.'data/'.$date.'/'.'</h2>';
		flush();ob_flush();
		@mkdir($dir.'data/'.$date.'/',0777);
		echo '<h2>Finished Download, extracting contents</h2>';
		flush();ob_flush();
		//unzip
		$test = new gzip_file($file);
		$test->set_options(array('overwrite' => 1)); //overwrite files
		$test->extract_files();
		//move unzipped
		echo '<h2>Extraction finished</h2>';
		flush();ob_flush();
		//chdir($date);
		//echo '<h2>Moving Files</h2>';
		//moveFiles("*.xml", "../");
		//echo '<h2>rmdir</h2>';
		//rmdir($site_url.'data/'.$date.'/');
		//chdir($site_url.'data/');
		echo '<h2>deleting tar.gz file</h2>';
		flush();ob_flush();
		delfile("*.tar.gz");
		echo '<h2>Processing files</h2>';
		flush();ob_flush();
		
		$dbase   	= 'blueghos_tv';
		$Database 	= new TV_DBConnection();
		$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
		
		$channels = $Database->getAllChannels();
		$date = date("Ymd", time());
		$date += "000000";
		$Database->clearForwardSchedule($date);
		$folder = date("Ymd", time());
		$bleb = array();
		for($day=0; $day <=7 ;$day++){
			foreach ($channels as $channel){
				if ($channel->isTVAny()){/*run tv-anytime parser*/
					$sChannel = $channel;
					echo'<a href="index.php?channel='.$channel->id.'">'.$channel->title.'</a>';
					flush();ob_flush();
					$time = time() + ($day * 24 * 60 * 60);
					$file = $dir.'data/'.$folder.'/'.date("Y", $time).date("m", $time).date("d", $time).$sChannel->serviceId.'_pi.xml';
				
					$parser = new TV_ProgramParser($file);
					echo '<p>Source is:'.$parser->getSource().'</p>';
					$parser->setChannel($sChannel);
					//echo '<h2>Channel is:'.$channel->title.'</h2>';
				
					$parser->parseFile();
					//echo $parser->getOutput();
					$chan = $parser->tv;
					$file = $dir.'data/'.$folder.'/'.date("Y", $time).date("m", $time).date("d", $time).$sChannel->serviceId.'_pl.xml';
					$parser = new TV_ScheduleParser($file, $chan);
					//echo '<h2>Source is:'.$parser->getSource().'</h2>';
					$parser->parseFile();
					$Database->addChannelSchedule($parser->tv);
				}else{/*is bleb*/
					$bleb[] = $channel->serviceId;
				}
			}
		}
		
		
		/*remove xml files*/
		//exec('rm -rf *.xml');
		echo '<h2>deleting xml files</h2>';
		flush();ob_flush();
		rmdirr($dir.'data/'.$folder.'/');
		//delfile("*.xml");
			
		/*updater_bleb.php*/
		mkdir($dir.'data/bleb-XML/',0777);
		echo '<h2>Made '.$dir.'data/bleb-XML/</h2>';
		flush();ob_flush();ob_flush();ob_flush();
		for ($i=-1; $i <= 7; $i++){
			mkdir($dir.'data/bleb-XML/'.$i.'/',0777);
		}
		echo '<h2>Made day directories</h2>';
		flush();ob_flush();
		for ($i=-1; $i <= 7; $i++){
			chmod_R($dir.'data/bleb-XML/'.$i, 0777);
		}
		echo '<h2>chmodded day directories</h2>';
		flush();ob_flush();
		
		$channels = 'itv1,ch4,five,s4c,itv4';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		$name = 'blebtv.tar.gz';
		$file = $dir.'data/'.$name;
		echo '<h2>Downloading '.$url.' to '.$file.'</h2>';
		flush();ob_flush();
		copy($url, $file);
		echo '<h2>Finished Download, extracting contents of '.$name.'</h2>';
		flush();ob_flush();
		$test = new gzip_file($name);
		$test->set_options(array('overwrite' => 1)); //overwrite files
		$test->extract_files();
		echo '<h2>Extraction finished</h2>';
		flush();ob_flush();
		echo '<h2>deleting tar.gz file</h2>';
		flush();ob_flush();
		delfile("*.tar.gz");
		sleep(2);
		
		$channels = 'abc1,boomerang,bravo,british_eurosport,cartoon_network,challenge,oneword,paramount,paramount2';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		$name = 'blebtv.tar.gz';
		$file = $dir.'data/'.$name;
		echo '<h2>Downloading '.$url.' to '.$file.'</h2>';
		flush();ob_flush();
		copy($url, $file);
		echo '<h2>Finished Download, extracting contents of '.$name.'</h2>';
		flush();ob_flush();
		$test = new gzip_file($name);
		$test->set_options(array('overwrite' => 1)); //overwrite files
		$test->extract_files();
		echo '<h2>Extraction finished</h2>';
		flush();ob_flush();
		echo '<h2>deleting tar.gz file</h2>';
		flush();ob_flush();
		delfile("*.tar.gz");
		sleep(2);
		
		$channels = 'scifi,sky_cinema1,sky_cinema2,sky_movies1,sky_movies2,sky_movies3,sky_movies4,sky_movies5,sky_movies6,sky_movies7';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		echo '<h2>Downloading '.$url.' to '.$file.'</h2>';
		flush();ob_flush();
		copy($url, $file);
		echo '<h2>Finished Download, extracting contents of '.$name.'</h2>';
		flush();ob_flush();
		$test = new gzip_file($name);
		$test->set_options(array('overwrite' => 1)); //overwrite files
		$test->extract_files();
		echo '<h2>Extraction finished</h2>';
		flush();ob_flush();
		echo '<h2>deleting tar.gz file</h2>';
		flush();ob_flush();
		delfile("*.tar.gz");
		sleep(2);
		
		$channels = 'sky_movies8,sky_movies9,sky_movies_cinema,sky_movies_cinema2,sky_one,sky_one_mix,sky_sports1,sky_sports2,sky_sports3,sky_sports_news';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		echo '<h2>Downloading '.$url.' to '.$file.'</h2>';
		flush();ob_flush();
		copy($url, $file);
		echo '<h2>Finished Download, extracting contents of '.$name.'</h2>';
		flush();ob_flush();
		$test = new gzip_file($name);
		$test->set_options(array('overwrite' => 1)); //overwrite files
		$test->extract_files();
		echo '<h2>Extraction finished</h2>';
		echo '<h2>deleting tar.gz file</h2>';
		delfile("*.tar.gz");
		sleep(2);
		
		$channels = 'sky_sports_xtra,sky_three,sky_travel,tcm,uk_bright_ideas,uk_drama,uk_gold,uk_history,uk_style,uktv_documentary';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		echo '<h2>Downloading '.$url.' to '.$file.'</h2>';
		flush();ob_flush();
		copy($url, $file);
		echo '<h2>Finished Download, extracting contents of '.$name.'</h2>';
		flush();ob_flush();
		$test = new gzip_file($name);
		$test->set_options(array('overwrite' => 1)); //overwrite files
		$test->extract_files();
		echo '<h2>Extraction finished</h2>';
		echo '<h2>deleting tar.gz file</h2>';
		delfile("*.tar.gz");
		sleep(2);
		
		$channels = 'discovery,discovery_kids,discovery_real_time,disney,e4,film_four,ftn,itv2,itv3,living_tv';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		echo '<h2>Downloading '.$url.' to '.$file.'</h2>';
		flush();ob_flush();
		copy($url, $file);
		echo '<h2>Finished Download, extracting contents of '.$name.'</h2>';
		flush();ob_flush();
		$test = new gzip_file($name);
		$test->set_options(array('overwrite' => 1)); //overwrite files
		$test->extract_files();
		echo '<h2>Extraction finished</h2>';
		flush();ob_flush();
		echo '<h2>deleting tar.gz file</h2>';
		flush();ob_flush();
		delfile("*.tar.gz");
		sleep(2);
		
		$channels = 'men_and_motors,more4,mtv,nick_junior,nickelodeon,uktv_people,vh1';
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		echo '<h2>Downloading '.$url.' to '.$file.'</h2>';
		flush();ob_flush();
		copy($url, $file);
		echo '<h2>Finished Download, extracting contents of '.$name.'</h2>';
		flush();ob_flush();
		$test = new gzip_file($name);
		$test->set_options(array('overwrite' => 1)); //overwrite files
		$test->extract_files();
		echo '<h2>Extraction finished</h2>';
		flush();ob_flush();
		echo '<h2>deleting tar.gz file</h2>';
		flush();ob_flush();
		delfile("*.tar.gz");
		
		for ($i=-1; $i <= 7; $i++){
			chmod_R($dir.'data/bleb-XML/'.$i, 0777);
		}
		
		echo '<h2>Processing files</h2>';
		
		//$dbase   	= 'blueghos_tv';
		//$Database 	= new TV_DBConnection();
		//$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
		
		$channels = $Database->getAllChannels();
		for($day=-1; $day <=7 ;$day++){
			$folder = 'bleb-XML/'.$day;
			foreach ($channels as $channel){
				if (!$channel->isTVAny()){/*run bleb parser*/
					//$day = 0;
					//$folder = 'bleb-XML/0';
					//$channel = $Database->getChannelBasic(32);
					$time = mktime(0,0,0) + ($day * 24 * 60 * 60);
					echo'<a href="index_bleb.php?channel='.$channel->id.'">'.$channel->title.'</a> - for '.$time.'<br />';
					flush();ob_flush();
					$sChannel = $channel;
					//$time = time() + ($day * 24 * 60 * 60);
					//$time = mktime(0,0,0);
					$file = $dir.'data/'.$folder.'/'.$sChannel->serviceId.'.xml';
					$parser = new Bleb_ProgramParser($file);
					echo '<p>Source is:'.$parser->getSource().'</p>';
					flush();ob_flush();
					$parser->setChannel($sChannel, $time);
					$parser->parseFile();
					$Database->addBlebChannel($parser->tv);
				}
			}
		}
		
		/*STATIC_CREATE.PHP*/
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
		description=\"TV Listings (UK only)\"
		title_url=\"http://tv.blueghost.co.uk/\"
		directory_title=\"NTV Listings (UK only)\"
		author=\"Michael Pritchard\"
		author_email=\"blueghostuk[remove this text]+no.spam.please@gmail.com\"
		author_location=\"Telford, UK\"
		render_inline=\"optional\" /> 
	<UserPref 
		name=\"channel\" 
		display_name=\"Channel\" 
		required=\"true\" 
		datatype=\"enum\"
		default_value=\"1\">";
	
	//$menu =  "<div class=\"chanList\">\n";
	foreach ($channels as $channel){
		//$menu .="<a href=\"/channel/".$channel->id."\">".$channel->title."</a>\n";
		$google_ig .= "<EnumValue value=\"".$channel->id."\" display_value=\"".$channel->title."\"/>";
	}
	//$menu .= "</div>\n";
	$google_ig .= "</UserPref>
	<Content 
		type=\"url\" 
		href=\"http://tv.blueghost.co.uk/feeds/html/channel/__UP_channel__\" />	
</Module>";
	//$file = $dir.'cache/html/menu.php';
	//write_header($file, $menu);
	
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
	}else{
		?>
		<form action="update_all.php" method="post">
		<input name="user" type="text" />
		<input name="submit" type="submit" />
		</form>
	<?php
	}		
?>