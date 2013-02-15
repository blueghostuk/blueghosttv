<?php

	if ($_REQUEST['user'] == 'abcde'){
		
		/*get bbc data files*/
		$url = 'http://backstage.bbc.co.uk/feeds/tvradio/';
		//$date = date("Ymd", time());//20051030
		$date = $_REQUEST['file'];
		$file = $date.'.tar.gz';//20051030.tar.gz
		exec('wget '.$url.$file.'');
		exec('tar zxf '.$file.'');
		exec('mv -f '.$date.'/* .');
		exec('rm -rf '.$date.'/');
		exec('rm -rf '.$file.'');
		
		require('../includes/paths.php');
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
		for($day=0; $day <=7 ;$day++){
			foreach ($channels as $channel){
				if ($channel->isTVAny()){/*run tv-anytime parser*/
					echo'<a href="index.php?channel='.$channel->id.'">'.$channel->title.'</a><br />';
					$sChannel = $channel;
					$time = time() + ($day * 24 * 60 * 60);
					$file = $dir.'data/'.date("Y", $time).date("m", $time).date("d", $time).$sChannel->serviceId.'_pi.xml';
				
					$parser = new TV_ProgramParser($file);
					echo '<p>Source is:'.$parser->getSource().'</p>';
					$parser->setChannel($sChannel);
					//echo '<h2>Channel is:'.$channel->title.'</h2>';
				
					$parser->parseFile();
					//echo $parser->getOutput();
					$chan = $parser->tv;
					$file = $dir.'data/'.date("Y", $time).date("m", $time).date("d", $time).$sChannel->serviceId.'_pl.xml';
					$parser = new TV_ScheduleParser($file, $chan);
					//echo '<h2>Source is:'.$parser->getSource().'</h2>';
					$parser->parseFile();
					$Database->addChannelSchedule($parser->tv);
				}
			}
		}
		
		/*remove xml files*/
		exec('rm -rf *.xml');
	}else{
		?>
		<form action="updater.php" method="post">
		<input name="user" type="text" />
		File:<input name="file" type="text" />
		<input name="submit" type="submit" />
		</form>
	<?php
	}		
?>