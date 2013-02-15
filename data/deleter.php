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
		
		//database connection
		$dbase   	= 'blueghos_tv';
		$Database 	= new TV_DBConnection();
		$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
		
		//get list of all channels
		$channels = $Database->getAllChannels();
		
		echo '<p>deleting single channel cache > 20 days</p>';
		flush();ob_flush();
		foreach ($channels as $channel){
			$files_dir = $dir.'cache/html/single/'.$channel->id;
			$timestamp = time() - ( 20 * 24 * 60 * 60 );
			$handle = opendir($files_dir);
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					if (filemtime($files_dir.'/'.$file) < $timestamp){
						unlink($files_dir.'/'.$file);
					}
				}
			}
			closedir($handle);
		}
		echo '<p>deleted single channel cache > 20 days</p>';
		flush();ob_flush();
		
		echo '<p>deleting bbc program cache > 20 days</p>';
		flush();ob_flush();
		foreach ($channels as $channel){
			$files_dir = $dir.'cache/html/bbc_program';
			$timestamp = time() - ( 20 * 24 * 60 * 60 );
			$handle = opendir($files_dir);
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					if (filemtime($files_dir.'/'.$file) < $timestamp){
						unlink($files_dir.'/'.$file);
					}
				}
			}
			closedir($handle);
		}
		echo '<p>deleted bbc program cache > 20 days</p>';
		flush();ob_flush();
		
		//delete bleb program cache
		echo '<p>deleting bleb program cache</p>';
		flush();ob_flush();
		//rmdirr($dir.'cache/html/bleb_program/');
		//@mkdir($dir.'cache/html/bleb_program/',0777);
		$files_dir = $dir.'cache/html/bleb_program';
		//$timestamp = time() - ( 20 * 24 * 60 * 60 );
		$handle = opendir($files_dir);
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				//if (filemtime($files_dir.'/'.$file) < $timestamp){
					unlink($files_dir.'/'.$file);
				//}
			}
		}
		closedir($handle);
		echo '<p>deleted bleb program cache</p>';
		flush();ob_flush();
		
		echo '<p>deleting rss results cache > 5 days</p>';
		flush();ob_flush();
		foreach ($channels as $channel){
			$files_dir = $dir.'cache/rss';
			$timestamp = time() - ( 5 * 24 * 60 * 60 );
			$handle = opendir($files_dir);
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					if (filemtime($files_dir.'/'.$file) < $timestamp){
						unlink($files_dir.'/'.$file);
					}
				}
			}
			closedir($handle);
		}
		echo '<p>deleted rss results cache > 5 days</p>';
		flush();ob_flush();
		
		echo '<p>deleting amazon xml results cache > 1 days</p>';
		flush();ob_flush();
		foreach ($channels as $channel){
			$files_dir = $dir.'cache/amazon/xml_cache';
			$timestamp = time() - ( 1 * 24 * 60 * 60 );
			$handle = opendir($files_dir);
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					if (filemtime($files_dir.'/'.$file) < $timestamp){
						unlink($files_dir.'/'.$file);
					}
				}
			}
			closedir($handle);
		}
		echo '<p>deleted amazon xml results cache > 1 days</p>';
		flush();ob_flush();
		
		echo '<p>deleting amazon html results cache > 1 days</p>';
		flush();ob_flush();
		foreach ($channels as $channel){
			$files_dir = $dir.'cache/amazon/results_cache';
			$timestamp = time() - ( 1 * 24 * 60 * 60 );
			$handle = opendir($files_dir);
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					if (filemtime($files_dir.'/'.$file) < $timestamp){
						unlink($files_dir.'/'.$file);
					}
				}
			}
			closedir($handle);
		}
		echo '<p>deleted amazon html results cache > 1 days</p>';
		flush();ob_flush();
?>