<?php
	//set timeout in seconds
	print ini_set("max_execution_time", 3600) . "<BR />";
	print ini_set("max_execution_time", 3600) . "<BR />";
	
	//functions
	function delfile($str)
	{
		foreach(glob($str) as $fn) 
		{
			unlink($fn);
		}
	}
		
	function rmdirr($dir) 
	{
		if($objs = glob($dir."/*"))
		{
			foreach($objs as $obj) 
			{
				is_dir($obj)? rmdirr($obj) : unlink($obj);
			}
		}
		rmdir($dir);
	}
		
	function chmod_R($path, $filemode) 
	{
	   if (!is_dir($path))
		   return chmod($path, $filemode);
		
	   $dh = opendir($path);
	   while ($file = readdir($dh)) 
	   {
		   if($file != '.' && $file != '..') 
		   {
			   $fullpath = $path.'/'.$file;
			   if(!is_dir($fullpath)) 
			   {
				 if (!chmod($fullpath, $filemode))
					 return FALSE;
			   } 
			   else 
			   {
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
	
	function DownloadBlebList($dir, $channels)
	{
		$url = 'http://www.bleb.org/tv/data/listings?days=-1..6&channels='.$channels.'&file=tgz&blueghostuk@gmail.com';
		$name = 'blebtv.tar.gz';
		$file = $dir.'data/'.$name;
		echo '<p>Downloading '.$url.' to '.$file.'</p>';
		flush();ob_flush();
		if (copy($url, $file))
		{
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
		}
		else
		{
			echo '<p>Failed Download: '.$url.'</p>';
			flush();ob_flush();
		}
	}
		//includes
		require('../includes/paths.php');
		require('../includes/archive.php');
		
		//get bbc data files
		$url = 'http://backstage.bbc.co.uk/feeds/tvradio/';
		$date = date("Ymd");//20051030
		$file = $date.'.tar.gz';//20051030.tar.gz

		echo '<p>Making Dir '.$dir.'data/'.$date.'/'.'</p>';
		flush();ob_flush();
		@mkdir($dir.'data/'.$date.'/',0777);
		echo '<p>Downloading '.$url.$file.' to '.$dir.'data/'.$file.'</p>';
		flush();ob_flush();
		if(!copy($url.$file, $dir.'data/'.$file))
		{
			die('Failed to download BBC Data File '.$url.$file.' to '.$dir.'data/'.$file);
		}
		echo '<p>Finished Download, extracting contents</p>';
		flush();ob_flush();
		
		//unzip
		$test = new gzip_file($file);
		$test->set_options(array('overwrite' => 1)); //overwrite files
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
		
		//updater_bleb.php
		echo '<p>Downloading BLEB Files</p>';
		flush();ob_flush();
		@mkdir($dir.'data/bleb-XML/',0777);
		echo '<p>Made '.$dir.'data/bleb-XML/</p>';
		flush();ob_flush();ob_flush();ob_flush();
		for ($i=-1; $i <= 7; $i++)
		{
			@mkdir($dir.'data/bleb-XML/'.$i.'/',0777);
		}
		echo '<p>Made day directories</p>';
		flush();ob_flush();
		for ($i=-1; $i <= 7; $i++)
		{
			chmod_R($dir.'data/bleb-XML/'.$i, 0777);
		}
		echo '<p>chmodded day directories</p>';
		flush();ob_flush();
		
		
		DownloadBlebList($dir,'itv1,ch4,five,abc1,bbc_hd,boomerang,bravo,british_eurosport,cartoon_network,challenge');
		
		DownloadBlebList($dir,'citv,discovery,discovery_kids,discovery_real_time,disney,e4,extreme_sports,film_four,five_life,five_us');
				
		DownloadBlebList($dir,'ftn,fx,ideal_world,itv2,itv3,itv4,living_tv,men_and_motors,more4,mtv');
		
		DownloadBlebList($dir,'nick_junior,nickelodeon,oneword,paramount,paramount2,qvc,s4c,s4c2,s4c_digidol,scifi');
		
		DownloadBlebList($dir,'sky_cinema1,sky_cinema2,sky_movies1,sky_movies10,sky_movies2,sky_movies3,sky_movies4,sky_movies5,sky_movies6,sky_movies7');
		
		DownloadBlebList($dir,'sky_movies8,sky_movies9,sky_news,sky_one,sky_sports1,sky_sports2,sky_sports3,sky_sports_news,sky_sports_xtra,sky_three');
		
		DownloadBlebList($dir,'sky_travel,sky_two,tcm,tmf,uk_bright_ideas,uk_drama,uk_gold,uk_history,uk_style,uktv_documentary');
		
		DownloadBlebList($dir,'uktv_g2,uktv_people,vh1');
		
		for ($i=-1; $i <= 7; $i++)
		{
			chmod_R($dir.'data/bleb-XML/'.$i, 0777);
		}
		
		echo '<p>DATA DOWNLOAD COMPLETE</p>';
		flush();ob_flush();
?>