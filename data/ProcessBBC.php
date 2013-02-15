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

		echo '<p>Processing files</p>';
		
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
		foreach ($channels as $achannel)
		{
			if ($achannel->isTVAny())
			{//run tv-anytime parser
				for($day=0; $day <=7 ;$day++)
				{
					$sChannel = $achannel;
					
					$time = time() + ($day * 24 * 60 * 60);
					$file = $dir.'data/'.$folder.'/'.date("Y", $time).date("m", $time).date("d", $time).$sChannel->serviceId.'_pi.xml';
					if (file_exists($file))
					{
						$parser = new TV_ProgramParser($file);
						$parser->setChannel($sChannel);
						$parser->parseFile();
						
						$chan = $parser->tv;
						$file = $dir.'data/'.$folder.'/'.date("Y", $time).date("m", $time).date("d", $time).$sChannel->serviceId.'_pl.xml';
						
						$parser = new TV_ScheduleParser($file, $chan);
						$parser->parseFile();
					}
				}
				$Database->addChannelSchedule($parser->tv);
				echo '<br />'.$parser->tv->title.' -  ('.count($parser->tv->schedule).'):'.mysql_error().'<br />';
				flush();ob_flush();
			}
		}

		//remove xml files
		echo '<p>deleting BBC xml files</p>';
		flush();ob_flush();
		rmdirr($dir.'data/'.$folder.'/');
		
		//STATIC_CREATE.PHP
		$channels = $Database->getAllChannels();
		foreach ($channels as $chan)
		{
			if ($chan->isTVAny())
				@mkdir($dir.'cache/html/single/'.$chan->id);
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
	
	foreach ($channels as $channel)
	{
		$google_ig .= "<EnumValue value=\"".$channel->id."\" display_value=\"".$channel->title."\"/>";
	}
	
	$google_ig .= "</UserPref>
	<Content 
		type=\"url\" 
		href=\"http://www.blueghosttv.co.uk/feeds/html/channel/__UP_channel__\" />	
</Module>";
	
	
	
	$file = $dir.'google_ig_channel.xml';
	write_header($file, $google_ig);
	
	for($days = 0; $days < 8; $days++)
	{
		echo "<br />Loop for day ".$days;
		flush();ob_flush();
		$time = time() + ($days * 24 * 60 * 60);
		foreach ($channels as $channel)
		{
			if ($channel->isTVAny())
			{
				$dateText = date("l jS F Y", $time);
				$date = date("Ymd", $time);
				$sChannel = $Database->getChannel($channel->id, $date);
				if ($days == 0)
				{
					$date = 'today';
					$xml_text =  '- for Today';
				}
				else
				{
					$xml_text =  '- for '.$dateText;
				}
				$xml_link = '/feeds/rss/channel/'.$sChannel->id.'/'.$date;
				$xml_title = 'RSS Feed for CHANNEL:'.$sChannel->title.$xml_text;
				
				$op = new TV_Outputter($sChannel);
				
				$date_text = date("l jS F Y", $time);
					
				if ($op->parseSingleChannel($date_text, $date))
				{
					$file_date = date("Ymd", $time);
					$file = $dir.'cache/html/single/'.$sChannel->id.'/'.$file_date.'.html';
					write_header($file, $op->getOutput());
					echo "<br />Wrote data for ".$sChannel->id." on ".$file_date."";
					flush();ob_flush();
				}
			}
		}
	}	
?>