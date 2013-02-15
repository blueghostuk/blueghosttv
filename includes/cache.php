<?php

	class Cache {
	
		function checkCache($url, $time){
			if (!file_exists($url))
				return true;
			if ( $time >= filectime($url) )
				return true;
			else
				return false;		
		}
		
		function checkCacheWithTime($file, $time){
			//$time = time() - (24*60*60);
			$url = '/home/blueghos/www/tv/cache/'.$file;
			return $this->checkCache($url, $time);
		}
		
		function getFile($file){
			return file_get_contents('/home/blueghos/www/tv/cache/'.$file);
		}
		
		function createFile($file, $data){
			$file = '/home/blueghos/www/tv/cache/'.$file;
			if (!$handle = fopen($file, 'w')) {
   				echo "Cannot Open File ($file)";
				return false;
   			}

   			if (!fwrite($handle, $data)) {
    			echo "Cannot write to file ($file)";
      			return false;
   			}
			return true;
		}
		
		function checkRSSCache($file){
			$time = time() - (24*60*60);
			$url = '/home/blueghos/www/tv/cache/rss/'.$file.'.xml';
			return $this->checkCache($url, $time);
		}
		
		function getRSSFile($file){
			return file_get_contents('/home/blueghos/www/tv/cache/rss/'.$file.'.xml');
		}
		
		function createRSSFile($file, $data){
			$file = '/home/blueghos/www/tv/cache/rss/'.$file.'.xml';
			if (!$handle = fopen($file, 'w')) {
   				echo "Cannot Open File ($file)";
				return false;
   			}

   			if (!fwrite($handle, $data)) {
    			echo "Cannot write to file ($file)";
      			return false;
   			}
			return true;
		}
		
		function checkiCalCache($file){
			$time = time() - (24*60*60);
			$url = '/home/blueghos/www/tv/cache/ical/'.$file.'.ics';
			return $this->checkCache($url, $time);
		}
		
		function getiCalFile($file){
			return file_get_contents('/home/blueghos/www/tv/cache/ical/'.$file.'.ics');
		}
		
		function createiCalFile($file, $data){
			$file = '/home/blueghos/www/tv/cache/ical/'.$file.'.ics';
			if (!$handle = fopen($file, 'w')) {
   				echo "Cannot Open File ($file)";
				return false;
   			}

   			if (!fwrite($handle, $data)) {
    			echo "Cannot write to file ($file)";
      			return false;
   			}
			return true;
		}
	}
?>