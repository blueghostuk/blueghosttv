<?php

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
		
		/*echo '<h2>bleb.org channels</h2>';
		@mkdir($dir.'data/bleb-XML/',0777);
		for ($i=0; $i < 7; $i++){
			@mkdir($dir.'data/bleb-XML/'.$i.'/',0777);
		}
		$b = 0;
		$url = 'http://www.bleb.org/tv/data/listings?days=0..6&channels=';
		$url2 = '&file=tgz&blueghostuk@gmail.com';
		$name = 'blebtv.tar.gz';
		$file = $dir.'data/'.$name;
		$channels = '';
		foreach ($bleb as $bl){
			if ($b <10){
				$channels .= $bl.',';
				$b++;
			}else{
				/*download*/
				/*$channels = substr($channels, 0, (strlen($channels)-1));
				echo '<h2>Downloading '.$url.$channels.$url2.' to '.$file.'</h2>';
				copy($url.$channels.$url2, $file);
				echo '<h2>Finished Download, extracting contents of '.$name.'</h2>';
				$test = new gzip_file($name);
				$test->set_options(array('overwrite' => 1)); //overwrite files
				$test->extract_files();
				echo '<h2>Extraction finished</h2>';
				echo '<h2>deleting tar.gz file</h2>';
				delfile("*.tar.gz");
				sleep(2);
				/*start with next*/
				/*$b = 1;
		<form action="updater.php" method="post">
				$channels = $bl.',';
			}
		}
		
		echo '<h2>Processing files</h2>';
		$channels = $Database->getAllChannels();
		for($day=0; $day <=7 ;$day++){
			$folder = 'bleb-XML/'.$day;
			foreach ($channels as $channel){
				if (!$channel->isTVAny()){/*run bleb parser*/
					//$day = 0;
					//$folder = 'bleb-XML/0';
					//$channel = $Database->getChannelBasic(32);
					/*$time = mktime(0,0,0) + ($day * 24 * 60 * 60);
					echo'<a href="index_bleb.php?channel='.$channel->id.'">'.$channel->title.'</a> - for '.$time.'<br />';
					$sChannel = $channel;
					//$time = time() + ($day * 24 * 60 * 60);
					//$time = mktime(0,0,0);
					$file = $dir.'data/'.$folder.'/'.$sChannel->serviceId.'.xml';
					$parser = new Bleb_ProgramParser($file);
					echo '<p>Source is:'.$parser->getSource().'</p>';
					$parser->setChannel($sChannel, $time);
					$parser->parseFile();
					$Database->addBlebChannel($parser->tv);
				}
			}
		}*/
		
	}else{
		?>
		<input name="user" type="text" />
		<input name="submit" type="submit" />
		</form>
	<?php
	}		
?>