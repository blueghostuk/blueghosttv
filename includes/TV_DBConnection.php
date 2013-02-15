<?php

	class TV_DBConnection extends DB_Connection{
	
		//var $addedProgs = array();
		
		function addChannelSchedule($channel)
		{
			for ($i=0; $i < count($channel->schedule); $i++)
			{
				/*for each prog in schedule*/
				$this->addSchedule($channel->schedule[$i], $channel->id);
			}
			for ($i=0; $i < count($channel->programmes); $i++)
			{
				$prog = $channel->programmes[$i];
				$this->addProgram($prog);
				$this->addedPrgos[] = $prog->pid;
			}
		}
		
		function addSchedule($sched, $channel)
		{
			$query = 'INSERT INTO `channel_schedule` ( `chann_id`, `start`, `duration`, `pid` )'
						.' VALUES ( "'.$channel.'", "'.$sched->start.'", "'.$sched->duration.'", "'.$sched->pid.'" )';
			$result = $this->DB_search($query);
		}
		
		function addProgram($prog)
		{
			//if ($this->checkProgramExists(trim($prog->pid)) <= 1){
				$av = $prog->getAVAttribs();
				if ($av->isAD())
				{
					$ad = "EN-UK";
				}
				else
				{
					$ad = '';
				}
				$query = 'INSERT INTO `programs` ( `crid`, `title`, `synopsys`, `long_sym`, `audio`, `video`, `audio_desc`, `signed`, `cc`, `series` )'
						.' VALUES ( "'.trim($prog->pid).'",  "'.$prog->title.'", "'.$prog->getShortSynopsis().'", "'.$prog->getLongSynopsis().'", "'.$av->getAudioChannels().'", "'.$av->getAspectRatio().'", "'.$ad.'", "'.$prog->signed.'", "'.$prog->cc.'", "'.$prog->series.'" )';
				$result = $this->DB_search($query);
				$this->addPromotional($prog->promotional, $prog->pid);
				$this->addGenres($prog->genres, $prog->pid);
				$this->addRelatedInfo($prog->related, $prog->pid);
				$this->addKeywords($prog->keywords, $prog->pid);
			//}/*else don't add*/
		}
		
		function addPromotional($array, $crid)
		{
			foreach ($array as $prom)
			{
				$query = 'INSERT INTO `promotional_info` ( `progid`, `value` )'
						.' VALUES ( "'.$crid.'", "'.$prom.'" )';
				$result = $this->DB_search($query);
			}
		}
		
		function addGenres($array, $crid)
		{
			$crid = $this->getProgIdFromCRID($crid);
			foreach($array as $genre)
			{
				$query = 'INSERT INTO `genre_types` ( `name` ) VALUES ( "'.$genre->type.'" )';
				$result = $this->DB_search($query);
				$query = 'SELECT `id` FROM `genre_types` WHERE `name` = \''.$genre->type.'\' LIMIT 1';
				$result = $this->DB_search($query);
				$result = $this->DB_row($result);
				$type = $result[0];
				$query = 'INSERT INTO `genre_tags` ( `genre_type`, `name` ) VALUES ( "'.$type.'", "'.$genre->title.'" )';
				$result = $this->DB_search($query);
				$query = 'SELECT `id` FROM `genre_tags` WHERE `name` = \''.$genre->title.'\' LIMIT 1';
				$result = $this->DB_search($query);
				$result = $this->DB_row($result);
				$tag = $result[0];
				$query = 'INSERT INTO `program_genre_tags` ( `prog_id`, `tag_id` ) VALUES ( "'.$crid.'", "'.$tag.'" )';
				$result = $this->DB_search($query);
			}
		}
		
		function addRelatedInfo($array, $crid)
		{
			foreach($array as $rinfo)
			{
				$query = 'INSERT INTO `related_info` ( `progid`, `type`, `name`, `value` )'
							.' VALUES ( "'.$crid.'", "'.$rinfo->type.'", "'.$rinfo->name.'", "'.$rinfo->value.'" )';
				$result = $this->DB_search($query);
			}
		}
		
		function addKeywords($array, $crid)
		{
			foreach($array as $genre)
			{
				$query = 'INSERT INTO `genre_types` ( `name` ) VALUES ( "keyword" )';
				$result = $this->DB_search($query);
				$query = 'SELECT `id` FROM `genre_types` WHERE `name` = \'keyword\' LIMIT 1';
				$result = $this->DB_search($query);
				$result = $this->DB_row($result);
				$type = $result[0];
				$query = 'INSERT INTO `genre_tags` ( `genre_type`, `name` ) VALUES ( "'.$type.'", "'.$genre->title.'" )';
				$result = $this->DB_search($query);
				$query = 'SELECT `id` FROM `genre_tags` WHERE `name` = \''.$genre->title.'\' LIMIT 1';
				$result = $this->DB_search($query);
				$result = $this->DB_row($result);
				$tag = $result[0];
				$query = 'INSERT INTO `program_genre_tags` ( `prog_id`, `tag_id` ) VALUES ( "'.$type.'", "'.$tag.'" )';
				$result = $this->DB_search($query);
			}
		}
		
		function addBlebChannel($channel)
		{
			$pids = array();
			echo 'Prog Count = '.count($channel->programmes).'<br />';
			flush();ob_flush();
			for ($i=0; $i < count($channel->programmes); $i++)
			{
				/*for each prog channel*/
				$pids [] = $this->addBlebProgram($channel->programmes[$i]);
			}
			echo 'Sched Count = '.count($channel->schedule).'<br />';
			flush();ob_flush();
			for ($i=0; $i < count($channel->schedule); $i++)
			{
				/*for each prog in schedule*/
				$channel->schedule[$i]->pid = $pids[$i];
				if ($channel->programmes[$i]->isOnNextDay())
				{ /*add 1 day to ts*/
					$channel->schedule[$i]->timestamp += (24*60*60);
				}
				$this->addBlebSchedule($channel->schedule[$i], $channel->id);
			}
			
		}
		
		function addBlebSchedule($sched, $channel)
		{
			$query = 'INSERT INTO `channel_schedule` ( `chann_id`, `start`, `duration`, `pid` )'
							.' VALUES ( "'.$channel.'", "'.$sched->formatDate('sql').'", "'.$sched->getDuration('sql').'", "'.$sched->pid.'" )';
			$result = $this->DB_search($query);
		}
		
		function addBlebProgram($prog)
		{
			$query = 'INSERT INTO `programs` ( `title`, `synopsys` )'
					.' VALUES (  "'.$prog->title.'", "'.$prog->getShortSynopsis().'" )';
			$result = $this->DB_search($query);
			$pid	= $this->Identity();
			$this->addRelatedInfo($prog->related, $pid);
			return $pid;
		}
		
		/*date in format
		 * YYYYMMDDHHMMSS
		 */
		function clearForwardSchedule($date)
		{
			$query = 'TRUNCATE `channel_schedule`';
			//echo '<p>Ran query: '.$query.'</p>';
			echo '<p>TRUNCATE `channel_schedule`</p>';
			flush();ob_flush();
			$result = $this->DB_search($query);
			//echo '<p>GOT ERROR '.mysql_errno() . ": " . mysql_error().'</p>';
			$query = 'TRUNCATE `program_genre_tags`';
			//echo '<p>Ran query: '.$query.'</p>';
			$result = $this->DB_search($query);
			echo '<p>TRUNCATE `program_genre_tags`</p>';
			flush();ob_flush();
			//echo '<p>GOT ERROR '.mysql_errno() . ": " . mysql_error().'</p>';
			$query = 'TRUNCATE `programs`';
			//echo '<p>Ran query: '.$query.'</p>';
			$result = $this->DB_search($query);
			echo '<p>TRUNCATE `programs`</p>';
			flush();ob_flush();
			//echo '<p>GOT ERROR '.mysql_errno() . ": " . mysql_error().'</p>';
			$query = 'TRUNCATE `promotional_info`';
			//echo '<p>Ran query: '.$query.'</p>';
			$result = $this->DB_search($query);
			echo '<p>TRUNCATE `promotional_info`</p>';
			flush();ob_flush();
			//echo '<p>GOT ERROR '.mysql_errno() . ": " . mysql_error().'</p>';
			$query = 'TRUNCATE `related_info`';
			//echo '<p>Ran query: '.$query.'</p>';
			$result = $this->DB_search($query);
			echo '<p>TRUNCATE `related_info`</p>';
			flush();ob_flush();
			//echo '<p>GOT ERROR '.mysql_errno() . ": " . mysql_error().'</p>';
		}
			
		
		
		/*function checkProgramExists($crid){
			$query = 'SELECT COUNT(*) FROM `programs` WHERE `crid` = \''.$crid.'\'';
			//echo "<br/>query was :".$query."<br />";
			$result = $this->DB_search($query);
			//echo '<p>GOT ERROR '.mysql_errno() . ": " . mysql_error().'</p>';
			$result = $this->DB_row($result);
			echo '<p>GOT RESULT '.$result[0].'</p>';
			return $result[0];
		}*/
		
		
		
		function findSeries($serid)
		{
			return $this->getSeries($serid);
		}
		
		function findBlebSeries($serid)
		{
			return $this->getBlebSeries($serid);
		}
		
		function getSeries($serid)
		{
			$query = 'SELECT * FROM `programs` WHERE `series` = \''.$serid.'\' LIMIT 1000';
			$result = $this->DB_search($query);
			$series = array();
			$schedules = array();
			while ($prog = $this->DB_array($result))
			{
				$tv = new TV_Program;
				$tv->loadFromDB($prog);
				$promos = $this->findProgramPromotionalInfo($tv->pid);
				for ($i =0; $i < count($promos); $i++)
				{
					$tv->addPromotional($promos[$i]);
				}
				$genres = $this->findProgramGenres($tv->pid);
				for ($i =0; $i < count($genres); $i++)
				{
					$tv->addGenre($genres[$i]);
				}
				$related = $this->findRelatedInfo($tv->pid);
				for ($i =0; $i < count($related); $i++)
				{
					$tv->addRelatedInfo($related[$i]);
				}
				$series[] = $tv;
				$schedules[] = $this->findSchedules($tv->pid);
				//echo 'found one of series<br />';
			}
			$ret = array();
			$ret['progs'] = $series;
			$ret['sched'] = $schedules;
			return $ret;
		}
		
		function getBlebSeries($serid){
			$serid = str_replace("'", "\'", $serid);
			$query = 'SELECT * FROM `programs` WHERE `title` = \''.$serid.'\' LIMIT 1000';
			//echo "<br />bleb query was ".$query;
			$result = $this->DB_search($query);
			$series = array();
			$schedules = array();
			while ($prog = $this->DB_array($result))
			{
				$tv = new Bleb_Program;
				$tv->loadFromDB($prog);
				/*$promos = $this->findProgramPromotionalInfo($crid);
				for ($i =0; $i < count($promos); $i++){
					$tv->addPromotional($promos[$i]);
				}
				$genres = $this->findProgramGenres($crid);
				for ($i =0; $i < count($genres); $i++){
					$tv->addGenre($genres[$i]);
				}*/
				$related = $this->findRelatedInfo($tv->pid);
				for ($i =0; $i < count($related); $i++)
				{
					$tv->addRelatedInfo($related[$i]);
				}
				$series[] = $tv;
				$schedules[] = $this->findBlebSchedules($tv->pid);
				//echo 'found one of series<br />';
			}
			$ret = array();
			$ret['progs'] = $series;
			$ret['sched'] = $schedules;
			return $ret;
		}
	
		function findProgram($crid)
		{
			//echo "row called in findProgram(".$crid.");\n";
			$query = 'SELECT * FROM `programs` WHERE `crid` = \''.$crid.'\' LIMIT 1';
			//echo "<br />Looking for prog:".$crid;
			$result = $this->DB_search($query);
			$result = $this->DB_row($result);
			$tv = new TV_Program;
			$tv->loadFromDB($result);
			$promos = $this->findProgramPromotionalInfo($crid);
			for ($i =0; $i < count($promos); $i++)
			{
				$tv->addPromotional($promos[$i]);
			}
			$genres = $this->findProgramGenres($crid);
			for ($i =0; $i < count($genres); $i++)
			{
				$tv->addGenre($genres[$i]);
			}
			$related = $this->findRelatedInfo($crid);
			for ($i =0; $i < count($related); $i++)
			{
				$tv->addRelatedInfo($related[$i]);
			}
			return $tv;
		}
		
		function findBlebProgram($pid)
		{
			//echo "row called in findProgram(".$crid.");\n";
			$query = 'SELECT * FROM `programs` WHERE `pid` = \''.$pid.'\' LIMIT 1';
			//echo "<br />Looking for prog:".$pid;
			$result = $this->DB_search($query);
			$result = $this->DB_row($result);
			$tv = new Bleb_Program;
			$tv->loadFromDB($result);
			/*$promos = $this->findProgramPromotionalInfo($crid);
			for ($i =0; $i < count($promos); $i++){
				$tv->addPromotional($promos[$i]);
			}*/
			/*$genres = $this->findProgramGenres($crid);
			for ($i =0; $i < count($genres); $i++){
				$tv->addGenre($genres[$i]);
			}*/
			$related = $this->findRelatedInfo($pid);
			for ($i =0; $i < count($related); $i++)
			{
				$tv->addRelatedInfo($related[$i]);
			}
			return $tv;
		}
		
		function getChannelName($id)
		{
			$query = 'SELECT `name` FROM `channels` WHERE `id` = '.$id.' LIMIT 1';
			$result = $this->DB_search($query);
			$result = $this->DB_row($result);
			return $result[0];
		}
		
		/**
		 * Get all schedules (upto 100) of a program (e.g repeats)
		 * returns array of TV_Program_Schedule
		 */
		function findSchedules($crid)
		{
			$query = 'SELECT `chann_id`, UNIX_TIMESTAMP(`start`) ,`duration` FROM `channel_schedule` WHERE `pid` = \''.$crid.'\' ORDER BY `start` ASC LIMIT 100';
			$result = $this->DB_search($query);
			$scheds = array();
			while ($sched = $this->DB_array($result))
			{
				$s = new TV_Program_Schedule;
				$s->start = $sched[1];
				$s->duration = $sched[2];
				$s->pid = $crid;
				$s->channel = $this->getChannelName($sched[0]);
				$scheds[] = $s;
			}
			//echo 'Found channel:'.$result[0].' set to '.$s->channel.' with starttime: '.$s->start.'<br />';
			return $scheds;
		}
		
		function findBlebSchedules($crid)
		{
			$query = 'SELECT `chann_id`, UNIX_TIMESTAMP(`start`) ,`duration` FROM `channel_schedule` WHERE `pid` = \''.$crid.'\' ORDER BY `start` ASC LIMIT 100';
			$result = $this->DB_search($query);
			$scheds = array();
			while ($sched = $this->DB_array($result))
			{
				//don't need bleb as is stored as standard in db
				//$s = new Bleb_Program_Schedule;
				$s = new TV_Program_Schedule;
				$s->isBleb = true;
				$s->start = $sched[1];
				$s->duration = $sched[2];
				$s->pid = $crid;
				$s->channel = $this->getChannelName($sched[0]);
				$scheds[] = $s;
			}
			//echo 'Found channel:'.$result[0].' set to '.$s->channel.' with starttime: '.$s->start.'<br />';
			return $scheds;
		}
		
		function findRelatedInfo($crid)
		{
			$query = 'SELECT DISTINCT `type`,`name`,`value` FROM `related_info` WHERE `progid` = \''.$crid.'\' LIMIT 100';
			$result = $this->DB_search($query);
			$rel = array();
			while ($related = $this->DB_array($result)){
				$r = new TV_Related_Info;
				$r->type = $related[0];
				$r->name = $related[1];
				$r->value = $related[2];
				//echo "<br />found related type = ".$r->type." name = ".$related[1]." value = ".$related[2]."";
				$rel[] = $r;
			}
			return $rel;
		}
				
		function getProgIdFromCRID($crid)
		{
			//echo "row called in getProgIdFromCRID(".$crid.");\n";
			$query = 'SELECT `pid` FROM `programs` WHERE `crid` = \''.$crid.'\' LIMIT 0, 30';
			$result = $this->DB_search($query);
			$result = $this->DB_row($result);
			return $result[0];
		}
		
		function getProgCRIDFromId($id)
		{
			//echo "row called in getProgCRIDFromId(".$id.");\n";
			$query = 'SELECT `crid` FROM `programs` WHERE `pid` = \''.$id.'\' LIMIT 0, 30';
			$result = $this->DB_search($query);
			$result = $this->DB_row($result);
			return $result[0];
		}
		
		function findProgramPromotionalInfo($crid)
		{
			$promos = array();
			$query = 'SELECT `value` FROM `promotional_info` WHERE `progid` = \''.$crid.'\' LIMIT 100';
			//echo "<br />query was ".$query;
			$result = $this->DB_search($query);
			while ($promo = $this->DB_array($result)){
				$promos[] = $promo['value'];
			}
			return $promos;
		}
		
		function findProgramGenres($crid)
		{
			$crid = $this->getProgIdFromCRID($crid);
			$query = 'SELECT DISTINCT gtype.name, gtag.name FROM program_genre_tags pgt, genre_tags gtag, genre_types gtype '
						.'WHERE pgt.prog_id = \''.$crid.'\' '
						.'AND gtag.id = pgt.tag_id '
						.'AND gtype.id = gtag.genre_type '
						.'LIMIT 100';
			$result = $this->DB_search($query);
			//echo "query was ".$query."<br />";
			$genres = array();
			while ($genre = $this->DB_array($result))
			{
				$g = new TV_Genre;
				$g->type = $genre[0];
				$g->setTitle($genre[1]); 
				$genres[] = $g;
				//echo "found genre: ".$genre['gtype.name']." value = ".$genre['gtag.name']."<br />";
			}
			return $genres;
		}
		
		function getChannelList()
		{
			$query =  'SELECT `id`,`serviceId`,`name`,`video` FROM `channels` LIMIT 0, 30';
			$result = $this->DB_search($query);
			$channels = array();
			while ($chan = $this->DB_array($result))
			{
				$c = new TV_Channel;
				$c->id = $chan['id'];
				$c->serviceId = $chan['serviceId'];
				$c->title = $chan['name'];
				if ($chan['video'] != 1){
					$c->audio = true;
				}
				$channels[] = $c;
			}
			return $channels;
		}
		
		function getAllChannels()
		{
			$query =  'SELECT `id`,`serviceId`,`name`,`video`, `tv_any` FROM `channels` ORDER BY `sky_epg` ASC LIMIT 1000';
			$result = $this->DB_search($query);
			$channels = array();
			while ($chan = $this->DB_array($result))
			{
				if ($chan[4] != 1)
				{/*bleb channel*/
					$c = new Bleb_Channel;
				}
				else
				{
					$c = new TV_Channel;
				}
				//$c = new TV_Channel;
				$c->id = $chan['id'];
				$c->serviceId = $chan['serviceId'];
				$c->title = $chan['name'];
				if ($chan['video'] != 1)
				{
					$c->audio = true;
				}
				$c->setTVAny($chan['tv_any']);
				$channels[] = $c;
			}
			return $channels;
		}
		
		function getChannel($id, $date)
		{
			//echo "row called in getChannel(".$id.", ".$date.");\n";
			$query =  'SELECT `serviceId`,`name`,`video`, `tv_any` FROM `channels` WHERE `id` = '.$id.' LIMIT 1';
			$result = $this->DB_search($query);
			$result = $this->DB_row($result);
			if ($result[3] != 1)
			{
				/*bleb channel*/
				//echo'is bleb channel ('.$result[3].')<br/>';
				return $this->getBlebChannel($id,$date,$result);
			}
			else
			{
				//echo'is tv-any channel ('.$result[3].')<br/>';
				return $this->getTVChannel($id,$date,$result);
			}
			
		}
		
		function getTVChannel($id, $date, $row)
		{
			$c = new TV_Channel;
			$c->id = $id;
			$c->serviceId = $row[0];
			$c->title = $row[1];
			if ($row[2] != 1)
			{
				$c->audio = true;
			}
			$c->tvany = true;
			$c->schedule = $this->getChannelSchedule($c->id, $date);
			foreach ($c->schedule as $sched)
			{
				$p = $this->findProgram($sched->pid);
				$c->addProgram($p);
			}
			return $c;
		}
		
		function getBlebChannel($id, $date, $row)
		{
			$c = new Bleb_Channel;
			$c->id = $id;
			$c->serviceId = $row[0];
			$c->title = $row[1];
			/*if ($result[2] != 1){
				$c->audio = true;
			}*/
			$c->tvany = false;
			$c->schedule = $this->getBlebChannelSchedule($c->id, $date);
			foreach ($c->schedule as $sched)
			{
				$p = $this->findBlebProgram($sched->pid);
				$c->addProgram($p);
			}
			return $c;
		}
		
		function getBlebChannelSchedule($id, $date)
		{
			$query = 'SELECT `chann_id` , UNIX_TIMESTAMP(`start`) ,`duration`, `pid` FROM `channel_schedule` WHERE `chann_id` = '.$id.' AND `start` REGEXP \'^'.$date.'[/^/]*\' ORDER BY `start` ASC LIMIT 1000';
			//echo "<br/>query was :".$query."<br />";
			$result = $this->DB_search($query);
			$schedules = array();
			while ($sched = $this->DB_array($result))
			{
				//stored in db so use normal tv-prog-sched but set isBleb to true for date/time fix
				$s = new TV_Program_Schedule;
				$s->isBleb = true;
				$s->start = $sched[1];
				$s->duration = $sched[2];
				$s->pid = $sched[3];
				$s->channel = $sched[0];
				$schedules[] = $s;
				//echo "<br />Found channel schedule for pid:".$s->pid;
			}
			return $schedules;
		}
		
		function getChannelBasic($id)
		{
			//echo "row called in getChannelBasic(".$id.");\n";
			$query =  'SELECT `serviceId`,`name`,`video`, `tv_any` FROM `channels` WHERE `id` = '.$id.' LIMIT 1';
			$result = $this->DB_search($query);
			$result = $this->DB_row($result);
			if ($result[3] != 1)
			{
				/*bleb channel*/
				$c = new Bleb_Channel;
			}
			else
			{
				$c = new TV_Channel;
			}
			//$c = new TV_Channel;
			$c->id = $id;
			$c->serviceId = $result[0];
			$c->title = $result[1];
			$c->setTVAny($chan['tv_any']);
			if ($result[2] != 1){
				$c->audio = true;
			}
			return $c;
		}
		
		function getChannelSchedule($id, $date)
		{
			$query = 'SELECT UNIX_TIMESTAMP(`start`) ,`duration`, `pid` FROM `channel_schedule` WHERE `chann_id` = '.$id.' AND `start` REGEXP \'^'.$date.'[/^/]*\' ORDER BY `start` ASC LIMIT 1000';
			//echo "<br/>query was :".$query."<br />";
			$result = $this->DB_search($query);
			$schedules = array();
			while ($sched = $this->DB_array($result))
			{
				$s = new TV_Program_Schedule;
				$s->start = $sched[0];
				$s->duration = $sched[1];
				$s->pid = $sched[2];
				$schedules[] = $s;
				//echo "<br />Found channel schedule for pid:".$s->pid;
			}
			return $schedules;
		}
		
		function findProgsWithGenre($genreName)
		{
			//echo "row called in findProgsWithGenre(".$genreName.");\n";
			$query = 'SELECT `id` FROM `genre_tags` WHERE `name` = \''.$genreName.'\' LIMIT 1';
			$result = $this->DB_search($query);
			$result = $this->DB_row($result);
			$gID = $result[0];
			$query = 'SELECT DISTINCT `prog_id` FROM `program_genre_tags` WHERE `tag_id` = '.$gID.' LIMIT 0, 50';
			$result = $this->DB_search($query);
			$progs = array();
			$scheds = array();
			while ($p = $this->DB_array($result))
			{
				$pid = $this->getProgCRIDFromId($p[0]);
				$prog = $this->findProgram($pid);
				$sched = $this->findSchedules($pid);
				$progs[] = $prog;
				$scheds[] = $sched;
			}
			$ret = array();
			$ret['progs'] = $progs;
			$ret['sched'] = $scheds;
			return $ret;
		}
		
		function won($channels, $date)
		{
		
		}
		
		function ajaxResults($query)
		{
			$query = 'SELECT `pid`,`crid`,`title`,`synopsys` FROM `programs` WHERE `title` LIKE \'%'.$query.'%\' LIMIT 0, 15';
			$result = $this->DB_search($query);
			$progs = array();
			$scheds = array();
			while ($p = $this->DB_array($result))
			{
				if (strlen($p[1]) > 0)
				{/*tv-any*/
					$scheds[] = $this->findSchedules($p[1]);
					$progs[] = $this->findProgram($p[1]);
				}
				else
				{/*bleb*/
					$scheds[] = $this->findBlebSchedules($p[0]);
					$progs[] = $this->findBlebProgram($p[0]);
				}
			}
			$ret = array();
			$ret['progs'] = $progs;
			$ret['sched'] = $scheds;
			return $ret;
		}
		
		
	}
?>