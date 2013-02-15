<?php
//allow longer execution for this
	print ini_set("max_execution_time", 3600) . "<BR />";
	print ini_set("max_execution_time", 3600) . "<BR />";
	
	echo '<h2>bleb.org tv listings</h2>';
	flush();ob_flush();
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
		echo '<h2>Starting Downloading ...</h2>';
		flush();ob_flush();ob_flush();ob_flush();
		
		require('../includes/paths.php');
		require('../includes/archive.php');
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
		
		$dbase   	= 'blueghos_tv';
		$Database 	= new TV_DBConnection();
		$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
		
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
			
		
			
		/*remove xml files*/
		//exec('rm -rf *.xml');
		//echo '<h2>deleting xml files</h2>';
		//rmdirr($dir.'data/'.$folder.'/');
		//delfile("*.xml");
	}else{
		?>
		<form action="updater_bleb.php" method="post">
		<input name="user" type="text" />
		<input name="submit" type="submit" />
		</form>
	<?php
	}		
?>