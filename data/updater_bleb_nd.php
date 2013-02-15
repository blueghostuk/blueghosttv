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
					$sChannel = $channel;
					//$time = time() + ($day * 24 * 60 * 60);
					//$time = mktime(0,0,0);
					$file = $dir.'data/'.$folder.'/'.$sChannel->serviceId.'.xml';
					$parser = new Bleb_ProgramParser($file);
					echo '<p>Source is:'.$parser->getSource().'</p>';
					$parser->setChannel($sChannel, $time);
					$parser->parseFile();
					//echo '<p>Parsed File</p>';
					$Database->addBlebChannel($parser->tv);
					//echo '<p>Added tp DB</p>';
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
		<form action="updater_bleb_nd.php" method="post">
		<input name="user" type="text" />
		<input name="submit" type="submit" />
		</form>
	<?php
	}		
?>