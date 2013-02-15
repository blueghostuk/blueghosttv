<?php
	require('paths.php');
	class TV_Outputter{
		var $tv;
		var $output;
		
		function TV_Outputter($tv){
			$this->tv = $tv;
		}
		
		function regexFile()
		{
			$input = file_get_contents($this->tv);
			$date = date("Ymd");
			return eregi_replace($date, "", $input);
		}
		
		function parseView()
		{
			$this->output .= '<div id="sched_data"><table id="chann_navigator"><tr><td class="time">Time</td>';
			foreach ($this->tv->channels as $c){
				$this->output .= '<td class="channel"><a class="c" href="/channel/'.$c->id.'" title="See this channel in full">'.$c->title.'</a></td>';
			}
			$this->output .= '</tr>';
			for ($i=0; $i < 24; $i++)
			{
				if ($i % 2 == 0)
					$row = 'A';
				else
					$row = 'B';
				$this->output .= '<tr class="row'.$row.'"><td id="hour_'.$i.'" class="time">'.$i.'</td>';
				foreach ($this->tv->channels as $c)
				{
					$data = $c->getProgsInHour($i);
					$schedules = $data['sched'];
					$programs  = $data['progs'];
					$output = '';
					for($x = 0; $x < count($schedules); $x++){
						$output .= $this->returnParseProgramForTable($programs[$x], $schedules[$x]);
					}
					$this->output .= '<td class="channel" onmouseover="javascript:showStatus(\''.$c->title.' :: '.$i.' - '.($i+1).'\');" onmouseout="javascript:showStatus(\'\');" title="'.$c->title.' :: '.$i.' - '.($i+1).'">'.$output.'</td>';
				}
				$this->output .= '</tr>';
			}
			$this->output .= '</table></div>';
			return true;
		}
		
		function parseSideView()
		{
			$this->output .= '<div id="sched_data_side"><table id="chann_navigator"><tr><td class="channel">Time</td>';
			for ($i=0; $i < 24; $i++)
			{
				$this->output .= '<td id="hour_'.$i.'" class="channel">'.$i.'</td>';
			}
			$this->output .= '</tr>';
			
			$i = 0;
			foreach ($this->tv->channels as $c)
			{
				if ($i % 2 == 0)
					$row = 'A';
				else
					$row = 'B';
				$this->output .= '<tr class="row'.$row.'"><td class="time"><a class="c" href="/channel/'.$c->id.'" title="See this channel in full">'.$c->title.'</a></td>';	
				for ($k=0; $k < 24; $k++)
				{
					$data = $c->getProgsInHour($k);
					$schedules = $data['sched'];
					$programs  = $data['progs'];
					$output = '';
					for($x = 0; $x < count($schedules); $x++){
						$output .= $this->returnParseProgramForTable($programs[$x], $schedules[$x]);
					}
					$this->output .= '<td class="channel" onmouseover="javascript:showStatus(\''.$c->title.' :: '.$k.' - '.($k+1).'\');" onmouseout="javascript:showStatus(\'\');" title="'.$c->title.'  :: '.$k.' - '.($k+1).'">'.$output.'</td>';
				}
				$this->output .= '</tr>';
				$i++;
			}
			
			$this->output .= '</table></div>';
			return true;
		}
		
		function returnParseSeriesResults($progs, $scheds)
		{
			$this->output .= '<div class="series_list">';
			$this->output .= '<h1>Series: '.$progs[0]->title.'</h1>';
			$this->output .= '<p><ul>';
			for($i=0; $i < count($progs); $i++)
			{
				foreach ($scheds[$i] as $sched)
				{
					$this->output .= '<li><a href="/program/'.$progs[$i]->pid.'">';
					$this->output .= $sched->formatDate('d/m/y time').' - '.$progs[$i]->getShortSynopsis();
					$this->output .= '</a></li>';
				}
			}
			$this->output .= '</ul></p></div>';
			return true;
		}
		
		function returnParseSeriesResultsForAjax($progs, $scheds)
		{
			if (strlen($progs[0]->series) < 1)
			{
				$sText = str_replace(" ", "+", $progs[0]->title);
			}
			else
			{
				$sText = $progs[0]->series;
			}
			$this->output .= '<div class="series_list">';
			$this->output .= '<h1>Series: '.$progs[0]->title.'</h1>';
			$this->output .= '<p><a href="javascript:setStyle(\'seriesResults\',\'hidden\');">Clear Results</a>';
			$this->output .= '<p><a href="/feeds/rss/series/'.$sText.'">RSS FEED</a>';
			$this->output .= '<p><a href="webcal://www.blueghosttv.co.uk/feeds/ical/series/'.$sText.'">iCal FEED</a>';
			$this->output .= '<ul>';
			for($i=0; $i < count($progs); $i++)
			{
				foreach ($scheds[$i] as $sched){
					$this->output .= '<li><a href="/program/'.$progs[$i]->pid.'">'.$progs[$i]->title;
					$this->output .= '. ( '.$sched->channel.' '.$sched->formatDate('d/m/y time').' ) - '.$progs[$i]->getShortSynopsis();
					$this->output .= '</a></li>';
				}
			}
			$this->output .= '</ul></p></div>';
			return true;
		}
		
		function returnParseSeriesResultsForRSS($progs, $scheds)
		{
			$this->output = '<?xml version="1.0"?><rss version="2.0">
	<channel>
		<title>BlueGhost PG - '.$progs[0]->title.'</title>
		<description>'.$progs[0]->title.'</description>
		<link>http://www.blueghosttv.co.uk/</link>
		<copyright>Data From backstage.bbc.co.uk, bleb.org, code by Michael Pritchard (blueghost.co.uk)</copyright>';
			for($i=0; $i < count($progs); $i++)
			{
				foreach ($scheds[$i] as $sched)
				{
					$this->output .= "\t\t<item>\n";
					$this->output .= "\t\t\t<title>".htmlspecialchars($progs[$i]->title)." ( ".$sched->channel." ".$sched->formatDate("d/m/y time")." )</title>\n";
					$this->output .= "\t\t\t<pubDate>".$sched->formatDate("rss")."</pubDate>\n";
					$this->output .= "\t\t\t<link>http://www.blueghosttv.co.uk/program/".$progs[$i]->pid."</link>\n";
					$this->output .= "\t\t\t<description>".$progs[$i]->getShortSynopsis()."</description>\n";
					$this->output .= "\t\t</item>\n";
				}
			}
			$this->output .= '</channel></rss>';
			return true;
		}
		
		function returnParseSeriesResultsForiCal($progs, $scheds)
		{
			$this->output = "BEGIN:VCALENDAR\n";
			$this->output .= "VERSION\n";
 			$this->output .= " :2.0\n";
			$this->output .= "PRODID\n";
			$this->output .= " :-//www.blueghosttv.co.uk/\n";
			$this->output .= "METHOD\n";
			$this->output .= " :PUBLISH\n";
			for($i=0; $i < count($progs); $i++)
			{
				foreach ($scheds[$i] as $sched)
				{
					$this->output .= "BEGIN:VEVENT\n";
					$this->output .= "UID\n";
					$this->output .= " :".$progs[$i]->pid.":".$sched->formatDate('hour:min')."\n";
					$this->output .= "SUMMARY\n";
					$this->output .= " :".$progs[$i]->title."\n";
					$this->output .= "DESCRIPTION\n";
					$this->output .= " :".$progs[$i]->getShortSynopsis()."\n";
					if (count($progs[$i]->related) > 0){
						foreach($progs[$i]->related as $rel){
							if ($rel->type == "Website"){
								$this->output .= "URL\n";
								$this->output .= " :".$rel->value."\n";
								break;
							}
						}
					}
					$this->output .= "LOCATION\n";
					$this->output .= " :".$sched->channel."\n";
					$this->output .= "DTSTART\n";
					$this->output .= " :".$sched->formatDate('ical')."\n";
					$this->output .= "DTEND\n";
					$this->output .= " :".$sched->getEndTime('ical')."\n";
					$this->output .= "END:VEVENT\n";
				}
			}
			$this->output .= 'END:VCALENDAR';
			return true;
		}
		
		function parseSearchResultsForAjax($progs, $scheds, $query)
		{
			if (count($progs) > 0)
			{
				$this->output .= '<div class="rss_links">';
				$this->output .= '| <a title="RSS Feed for this Search" href="http://www.blueghosttv.co.uk/rss.php?search='.$query.'">RSS FEED</a> | ';
				$this->output .= '<a title="iCal Calendar File for this Search" href="webcal://www.blueghosttv.co.uk/ical.php?search='.$query.'">iCal FEED</a> |';
				$this->output .= '</div>';
			}
			for($i=0; $i < count($progs); $i++)
			{
				foreach ($scheds[$i] as $sched)
				{
					$this->output .= '<a href="/program/'.$progs[$i]->pid.'">'.$progs[$i]->title;
					$this->output .= '. ( '.$sched->channel.' '.$sched->formatDate('d/m/y time').' ) - '.$progs[$i]->getShortSynopsis();
					$this->output .= '</a><br />';
				}
			}
			if (count($progs) > 0)
			{
				$this->output .= '<div class="rss_links">';
				$this->output .= '| <a title="RSS Feed for this Search" href="http://www.blueghosttv.co.uk/rss.php?search='.$query.'">RSS FEED</a> | ';
				$this->output .= '<a title="iCal Calendar File for this Search" href="webcal://www.blueghosttv.co.uk/ical.php?search='.$query.'">iCal FEED</a> |';
				$this->output .= '</div>';
			}
			return true;
		}
		
		function parseSearchResultsForRSS($progs, $scheds, $query)
		{
			$this->output = '<?xml version="1.0"?><rss version="2.0">
	<channel>
		<title>BlueGhost PG - '.$query.'</title>
		<description>Search for '.$query.'</description>
		<link>http://www.blueghosttv.co.uk/</link>
		<copyright>Data From backstage.bbc.co.uk, bleb.org, code by Michael Pritchard (blueghost.co.uk)</copyright>';
			for($i=0; $i < count($progs); $i++)
			{
				foreach ($scheds[$i] as $sched)
				{
					$this->output .= "\t\t<item>\n";
					$this->output .= "\t\t\t<title>".htmlspecialchars($progs[$i]->title)." ( ".$sched->channel." ".$sched->formatDate("d/m/y time")." )</title>\n";
					$this->output .= "\t\t\t<pubDate>".date("D, d M Y H:i:s T")."</pubDate>\n";
					$this->output .= "\t\t\t<link>http://www.blueghosttv.co.uk/program/".$progs[$i]->pid."</link>\n";
					$this->output .= "\t\t\t<description>".htmlspecialchars($progs[$i]->getShortSynopsis())."</description>\n";
					$this->output .= "\t\t</item>\n";
				}
			}
			$this->output .= '</channel></rss>';
			return true;
		}
		
		function parseSearchResultsForiCal($progs, $scheds)
		{
			$this->output = "BEGIN:VCALENDAR\n";
			$this->output .= "VERSION\n";
 			$this->output .= " :2.0\n";
			$this->output .= "PRODID\n";
			$this->output .= " :-//www.blueghosttv.co.uk/\n";
			$this->output .= "METHOD\n";
			$this->output .= " :PUBLISH\n";
			for($i=0; $i < count($progs); $i++)
			{
				foreach ($scheds[$i] as $sched)
				{
					$this->output .= "BEGIN:VEVENT\n";
					$this->output .= "UID\n";
					$this->output .= " :".$progs[$i]->pid.":".$sched->formatDate('hour:min')."\n";
					$this->output .= "SUMMARY\n";
					$this->output .= " :".$progs[$i]->title."\n";
					$this->output .= "DESCRIPTION\n";
					$this->output .= " :".$progs[$i]->getShortSynopsis()."\n";
					$this->output .= "LOCATION\n";
					$this->output .= " :".$sched->channel."\n";
					$this->output .= "DTSTART\n";
					$this->output .= " :".$sched->formatDate('ical')."\n";
					$this->output .= "DTEND\n";
					$this->output .= " :".$sched->getEndTime('ical')."\n";
					$this->output .= "END:VEVENT\n";
				}
			}
			$this->output .= 'END:VCALENDAR';
			return true;
		}
		
		function parseProgramLongDescription($scheds)
		{
			$seriesText = '';
			if (isset($this->tv->series) && (strlen($this->tv->series) > 0))
			{
				$this->output .= '<div class="rss_links">';
				$this->output .= '| <a title="HTML Feed for this Series" href="/feeds/html/series/'.$this->tv->series.'">HTML FEED</a> | ';
				$this->output .= '<a title="RSS Feed for this Series" href="/feeds/rss/series/'.$this->tv->series.'">RSS FEED</a> | ';
				$this->output .= '<a title="iCal Calendar File for this Series" href="webcal://www.blueghosttv.co.uk/feeds/ical/series/'.$this->tv->series.'">iCal FEED</a> |';
				$this->output .= '</div>';
				$seriesText = '[ SERIES: <a href="/feeds/html/series/'.$this->tv->series.'" onclick="doSeriesSearch(\''.$this->tv->series.'\');return false;">'.$this->tv->title.'</a> ]<div id="seriesResults" class="hidden"></div>';
			}
			$this->output .= '<div class="prog">';
			foreach ($scheds as $sched)
			{
				$this->output .= '<p><strong>'.$sched->channel.' - '.$sched->formatDate('standard').' - '.$sched->getEndTime('hour:min').'</strong></p>';
			}
			$this->output .= '<p class="prog_title">'.$this->tv->title.'&nbsp;'.$seriesText.'</p>';
			$tva = $this->tv->getAVAttribs();
			if ($tva->isWideScreen())
			{
				$add = '[W]';
			}
			if ($tva->isAD())
			{
				$add .= ' [AD]';
			}
			$this->output .= '<p class="prog_syn">'.$this->tv->getLongSynopsis().' '.$add.'</p>';
			$this->output .= '<p><strong>Program Links</strong></p>';
			if ( (count($this->tv->related)) > 0 || (count($this->tv->promotional) > 0) )
			{
				
				$this->output .= '<p>';
				foreach($this->tv->related as $r)
				{
					if (strpos($r->value, 'crid') === false)
						$this->output .= '<a href="'.$r->value.'" target="_blank">'.$r->value.'</a> - '.$r->type.' - '.$r->name.'<br />';
					//else
						//$this->output .= '<a href="/program/'.$r->value.'">'.$r->value.'</a> - '.$r->type.' - '.$r->name.'<br />';
				}
				foreach($this->tv->promotional as $p)
				{
					$this->output .= $p.'<br/>';
				}
			}
			$this->output .= '<p><a href="http://uk.imdb.com/Find?select=All&for='.str_replace(" ", "+",$this->tv->title).'" target="_blank" title="Go to the IMDB Listing for this program">IMDB Link</a> -  (may be direct or list of results)</p>';
			$this->output .= '<p><a href="http://www.tv.com/search.php?type=11&stype=program&qs='.str_replace(" ", "+",$this->tv->title).'&x=0&y=0" target="_blank" title="Go to the TV.com listing for this program">TV.com Link</a> -  (may be direct or list of results)</p>';
			$this->output .= '</p>';
			if (count($this->tv->genres) > 0)
			{
				$this->output .= '<p><strong>Program Genres</strong></p>';
				$this->output .= '<p>';
				foreach($this->tv->genres as $g)
				{
					$this->output .= '<a href="/genre/'.str_replace(" ", "+", $g->title).'">'.$g->title.'</a> - '.$g->type.'<br />';
				}
			}
			
			//amazon
			$as = new AmazonSearch;
			$res = new AmazonSearchResults;
			$res->terms = $this->tv->title;
			$as->setTerms($this->tv->title);
			$as->results = $res;
			//echo "<p>Amazon URL:".$as->generateXMLUrl()."</p>\n";
			if ($as->checkResultsCache())
			{
				$as->getResults();
				$as->parseResultsToFile();
			}
			
			$results = $as->returnResults();
			
			if ($results != false)
			{
				$this->output .= '<div class="amazon_results">';
				$this->output .= '<h1>Items from Amazon.co.uk</h1>';
				$this->output .= '<p><a href="http://www.amazon.co.uk/exec/obidos/redirect?tag=blueghost-21&creative=7274&camp=1962&link_code=sub&path=http://www.amazon.co.uk/gp/subs/rentals/help/learn-more.html" target="_blank">Consider signing up for Amazon DVD Rental</a></p>';
				$this->output .= $results;
				$this->output .= '<br />Search Amazon : <form style="display: inline;" action="search.php" method="post">
		<input type="text" name="search" id="search" value="">
		<select id="category" name="category">
			<option value="Blended">All</option>
			<option value="Books">Books</option>
			<option value="Classical">Classical</option>
			<option value="DVD">DVD</option>
			<option value="Electronics">Electronics</option>
			<option value="HealthPersonalCare">Health/Personal Care</option>
			<option value="HomeGarden">Home/Garden</option>
			<option value="Kitchen">Kitchen</option>
			<option value="Music">Music</option>
			<option value="MusicTracks">Music Tracks</option>
			<option value="OutdoorLiving">Outdoor Living</option>
			<option value="Software">Software</option>
			<option value="SoftwareVideoGames">Software Video Games</option>
			<option value="Toys">Toys</option>
			<option value="VHS">VHS</option>
			<option value="Video">Video</option>
			<option value="VideoGames">Video Games</option>
		</select>
		<input type="button" value="Search" onclick="javascript:doAmazonSearch();">
	</form>';
				$this->output .= '</div>';
			}
			
			$this->output .= '</div>';
			if (isset($this->tv->series) && (strlen($this->tv->series) > 0))
			{
				$this->output .= '<div class="rss_links">';
				$this->output .= '| <a title="HTML Feed for this Series" href="/feeds/html/series/'.$this->tv->series.'">HTML FEED</a> | ';
				$this->output .= '<a title="RSS Feed for this Series" href="/feeds/rss/series/'.$this->tv->series.'">RSS FEED</a> | ';
				$this->output .= '<a title="iCal Calendar File for this Series" href="webcal://www.blueghosttv.co.uk/feeds/ical/series/'.$this->tv->series.'">iCal FEED</a> |';
				$this->output .= '</div>';
			}
			return true;
		}
		
		/*$this->tv = genre name*/
		function parseGenreList($progs, $scheds)
		{
			$ag = str_replace(" ", "+", $this->tv);
			$this->output .= '<div class="rss_links">';
			$this->output .= '| <a title="RSS Feed for this Genre" href="/feeds/rss/genre/'.$ag.'">RSS FEED</a> | ';
			$this->output .= '<a title="iCal Calendar File for this Genre" href="webcal://www.blueghosttv.co.uk/feeds/ical/genre/'.$ag.'">iCal FEED</a> |';
				$this->output .= '</div>';
			$this->output .= '<div class="series_list">';
			$this->output .= '<h1>First 50 Programs for Genre: '.$this->tv.'</h1>';
			$this->output .= '<p><ul>';
			for($i=0; $i < count($progs); $i++)
			{
				foreach ($scheds[$i] as $sched)
				{
					$this->output .= '<li><a href="/program/'.$progs[$i]->pid.'">'.$progs[$i]->title;
					//show first showing only at present
					$this->output .= ' ( '.$sched->channel.' '.$sched->formatDate('d/m/y time').' )';
					$this->output .= '</a></li>';
				}
			}
			$this->output .= '</ul></p></div>';
			$this->output .= '<div class="rss_links">';
			$this->output .= '| <a title="RSS Feed for this Genre" href="/feeds/rss/genre/'.$ag.'">RSS FEED</a> | ';
			$this->output .= '<a title="iCal Calendar File for this Genre" href="webcal://www.blueghosttv.co.uk/feeds/ical/genre/'.$ag.'">iCal FEED</a> |';
			$this->output .= '</div>';
			return true;
		}
		
		function parseGenreListForRSS($progs, $scheds)
		{
			$this->output = '<?xml version="1.0"?><rss version="2.0">
	<channel>
		<title>BlueGhost PG - '.$this->tv.'</title>
		<description>'.$this->tv.'</description>
		<link>http://www.blueghosttv.co.uk/</link>
		<copyright>Data From backstage.bbc.co.uk, bleb.org, code by Michael Pritchard (blueghost.co.uk)</copyright>';
			for($i=0; $i < count($progs); $i++)
			{
				foreach ($scheds[$i] as $sched)
				{
					$this->output .= "\t\t<item>\n";
					$this->output .= "\t\t\t<title>".htmlspecialchars($progs[$i]->title)." ( ".$sched->channel." ".$sched->formatDate("d/m/y time")." )</title>\n";
					$this->output .= "\t\t\t<pubDate>".date("D, d M Y H:i:s T")."</pubDate>\n";
					$this->output .= "\t\t\t<link>http://www.blueghosttv.co.uk/program/".$progs[$i]->pid."</link>\n";
					$this->output .= "\t\t\t<description>".htmlspecialchars($progs[$i]->getShortSynopsis())."</description>\n";
					$this->output .= "\t\t</item>\n";
				}
			}
			$this->output .= '</channel></rss>';
			return true;
		}
		
		function parseGenreListForiCal($progs, $scheds)
		{
			$this->output = "BEGIN:VCALENDAR\n";
			$this->output .= "VERSION\n";
 			$this->output .= " :2.0\n";
			$this->output .= "PRODID\n";
			$this->output .= " :-//www.blueghosttv.co.uk/\n";
			$this->output .= "METHOD\n";
			$this->output .= " :PUBLISH\n";
			for($i=0; $i < count($progs); $i++){
				foreach ($scheds[$i] as $sched)
				{
					$this->output .= "BEGIN:VEVENT\n";
					$this->output .= "UID\n";
					$this->output .= " :".$progs[$i]->pid."\n";
					$this->output .= "SUMMARY\n";
					$this->output .= " :".$progs[$i]->title."\n";
					$this->output .= "DESCRIPTION\n";
					$this->output .= " :".$progs[$i]->getShortSynopsis()."\n";
					$this->output .= "LOCATION\n";
					$this->output .= " :".$sched->channel."\n";
					$this->output .= "DTSTART\n";
					$this->output .= " :".$sched->formatDate('ical')."\n";
					$this->output .= "DTEND\n";
					$this->output .= " :".$sched->getEndTime('ical')."\n";
					$this->output .= "END:VEVENT\n";
				}
			}
			$this->output .= 'END:VCALENDAR';
			return true;
		}
		
		function parseSingleChannel($date_text, $date)
		{
			$dt = date("Ymd");
			if ($date == $dt)
			{
				$date = 'today';
			}
			$this->output = '<div id="single_view"><h1>TV Guide for '.$this->tv->title.' on '.$date_text.'</h1>';
			$this->output .= '<div class="rss_links">';
			$this->output .= '| <a title="You can include this HTML File in your website inside an iframe" href="/feeds/html/channel/'.$this->tv->id.'/'.$date.'">HTML FEED</a> | ';
			$this->output .= '<a title="RSS Feed for this Channel" href="/feeds/rss/channel/'.$this->tv->id.'/'.$date.'">RSS FEED</a> | ';
			$this->output .= '<a title="iCal Calendar File for this TV Channel" href="webcal://www.blueghosttv.co.uk/feeds/ical/channel/'.$this->tv->id.'/'.$date.'">iCal FEED</a> |';
			$this->output .= '</div>';
			$this->output .= '<table id="chann_navigator"><tr><td class="time">Time</td>';
			$this->output .= '<td class="channel">'.$this->tv->title.'</td>';
			$this->output .= '</tr>';
			for ($i=0; $i < 24; $i++)
			{
				if ($i % 2 == 0)
					$row = 'A';
				else
					$row = 'B';
				$this->output .= '<tr class="row'.$row.'"><td id="hour_'.$i.'" class="time">'.$i.'</td>';
				$data = $this->tv->getProgsInHour($i);
				$schedules = $data['sched'];
				$programs  = $data['progs'];
				$output = '';
				for($x = 0; $x < count($schedules); $x++)
				{
					$output .= $this->returnParseProgramForTableWD($programs[$x], $schedules[$x]);
				}
				$this->output .= '<td class="channel">'.$output.'</td>';
				$this->output .= '</tr>';
			}
			$this->output .= '</table>';
			$this->output .= '<div class="rss_links">';
			$this->output .= '| <a title="You can include this HTML File in your website inside an iframe" href="/feeds/html/channel/'.$this->tv->id.'/'.$date.'">HTML FEED</a> | ';
			$this->output .= '| <a title="RSS Feed for this Channel" href="/feeds/rss/channel/'.$this->tv->id.'/'.$date.'">RSS FEED</a> | ';
			$this->output .= '<a title="iCal Calendar File for this TV Channel" href="webcal://www.blueghosttv.co.uk/feeds/ical/channel/'.$this->tv->id.'/'.$date.'">iCal FEED</a> |';
			$this->output .= '</div></div>';
			return true;
		}
		
		//function parseSingleChannelForGoogle($date_text, $date){
		function parseSingleChannelForGoogle()
		{
			/*$dt = date("Ymd");
			if ($date == $dt){
				$date = 'today';
			}*/
			for ($i=0; $i < 24; $i++)
			{
				if ($i % 2 == 0)
					$row = 'A';
				else
					$row = 'B';
				$this->output .= '<a id="hour_'.$i.'"></a>';
				$data = $this->tv->getProgsInHour($i);
				$schedules = $data['sched'];
				$programs  = $data['progs'];
				for($x = 0; $x < count($schedules); $x++){
					$this->output .= $this->returnParseProgramForGoogleTable($programs[$x], $schedules[$x]);
				}
			}
			return true;
		}
		
		function parseSingleChannelForRSS($date)
		{
			$this->output = '<?xml version="1.0"?><rss version="2.0">
	<channel>
		<title>BlueGhost PG - '.$this->tv->title.'</title>
		<description>'.$this->tv->title.'</description>
		<link>http://www.blueghosttv.co.uk/</link>
		<copyright>Data From backstage.bbc.co.uk, bleb.org, code by Michael Pritchard (blueghost.co.uk)</copyright>';
			for ($i=0; $i < 24; $i++)
			{
				if ($i % 2 == 0)
					$row = 'A';
				else
					$row = 'B';
				$data = $this->tv->getProgsInHour($i);
				$schedules = $data['sched'];
				$programs  = $data['progs'];
				$output = '';
				for($x = 0; $x < count($schedules); $x++)
				{
					$this->output .= $this->returnParseProgramForRSS($programs[$x], $schedules[$x]);
				}
				
			}
			$this->output .= '</channel></rss>';
			return true;
		}
		
		function parseSingleChannelForXML()
		{
			$this->output = "<?xml version=\"1.0\"?>\n";
			$this->output .= "\t<channel>\n";
			$this->output .= "\t<name>".$this->tv->title."</name>\n";
			$this->output .= "\t<id>".$this->tv->id."</id>\n";
			$this->output .= "\t<service_id>".$this->tv->serviceId."</service_id>\n";
			for ($i=0; $i < 24; $i++)
			{
				if ($i % 2 == 0)
					$row = 'A';
				else
					$row = 'B';
				$data = $this->tv->getProgsInHour($i);
				$schedules = $data['sched'];
				$programs  = $data['progs'];
				$output = '';
				for($x = 0; $x < count($schedules); $x++)
				{
					$this->output .= $this->returnParseProgramForXML($programs[$x], $schedules[$x]);
				}
				
			}
			$this->output .= "\t</channel>\n";
			return true;
		}
		
		function parseSingleChannelForiCal($date)
		{
			$this->output = "BEGIN:VCALENDAR\n";
			$this->output .= "VERSION\n";
 			$this->output .= " :2.0\n";
			$this->output .= "PRODID\n";
			$this->output .= " :-//www.blueghosttv.co.uk/\n";
			$this->output .= "METHOD\n";
			$this->output .= " :PUBLISH\n";

			for ($i=0; $i < 24; $i++){
				$data = $this->tv->getProgsInHour($i);
				$schedules = $data['sched'];
				$programs  = $data['progs'];
				$output = '';
				for($x = 0; $x < count($schedules); $x++){
					$this->output .= $this->returnParseProgramForiCal($programs[$x], $schedules[$x]);
				}
				
			}
			$this->output .= 'END:VCALENDAR';
			return true;
		}
		
		function returnParseProgramForGoogleTable($prog, $sched)
		{
			$output = '<strong>'.$sched->formatDate('hour:min').' - '.$sched->getEndTime('hour:min').'</strong><br/>';
			$output.= '<a class="program" target="_parent" href="/program/'.$prog->pid.'" title="'.htmlspecialchars($prog->getShortSynopsis()).'">'.$prog->title.'</a><br/>';
			return $output;
		}
		
		function returnParseProgramForTable($prog, $sched)
		{
			$output = '<strong>'.$sched->formatDate('hour:min').'</strong><br/>';
			$output.= '<a class="program" href="/program/'.$prog->pid.'" title="'.htmlspecialchars($prog->getShortSynopsis()).'">'.$prog->title.'</a><br/>';
			return $output;
		}
		
		function returnParseProgramForTableWD($prog, $sched)
		{
			$output = '<div onmouseover="javascript:setElementStyle(this, \'programHighlight\');" onmouseout="javascript:setElementStyle(this, \'\');">';
			$output.= '<strong>'.$sched->formatDate('hour:min').'</strong><br/>';
			$output.= '<a class="program" href="/program/'.$prog->pid.'" title="'.htmlspecialchars($prog->getShortSynopsis()).'">'.$prog->title.'</a>';
			$output.= '<p class="prog_desc">'.$prog->getShortSynopsis().'</p>';
			$output.= '</div>';
			return $output;
		}
		
		function returnParseProgramForRSS($prog, $sched)
		{
			$output = "\t\t<item>\n";
			$output .= "\t\t\t<title>".htmlspecialchars($prog->title)." ( ".$sched->channel." ".$sched->formatDate("d/m/y time")." )</title>\n";
			$output .= "\t\t\t<pubDate>".date("D, d M Y H:i:s T")."</pubDate>\n";
			$output .= "\t\t\t<link>http://www.blueghosttv.co.uk/program/".$prog->pid."</link>\n";
			$output .= "\t\t\t<description>".htmlspecialchars($prog->getShortSynopsis())."</description>\n";
			$output .= "\t\t</item>\n";
			return $output;
		}
		
		function returnParseProgramForXML($prog, $sched)
		{
			$output = "\t\t<program>\n";
			$output .= "\t\t\t<name>".htmlspecialchars($prog->title)."</name>\n";
			$output .= "\t\t\t<start_time>".$sched->formatDate('hour:min')."</start_time>\n";
			$output .= "\t\t\t<end_time>".$sched->getEndTime('hour:min')."</end_time>\n";
			$output .= "\t\t\t<description>".htmlspecialchars($prog->getShortSynopsis())."</description>\n";
			$output .= "\t\t\t<link>http://www.blueghosttv.co.uk/program/".$prog->pid."</link>\n";
			$output .= "\t\t</program>\n";
			return $output;
		}
		
		function returnParseProgramForiCal($prog, $sched)
		{
			$output .= "BEGIN:VEVENT\r";
			$output .= "UID\r";
			$output .= " :".$prog->pid.":".$sched->formatDate('hour:min')."\r";
			$output .= "SUMMARY\r";
			$output .= " :".htmlspecialchars($prog->title)."\r";
			$output .= "DESCRIPTION\r";
			$output .= " :".htmlspecialchars($prog->getShortSynopsis())."\r";
			$output .= "LOCATION\r";
			$output .= " :".$sched->channel."\r";
			$output .= "DTSTART\r";
			$output .= " :".$sched->formatDate('ical')."\r";
			$output .= "DTEND\r";
			$output .= " :".$sched->getEndTime('ical')."\r";
			$output .= "END:VEVENT\r";
			return $output;
		}
		
		function getOutput()
		{
			return $this->output;
		}
				
		function parseNNForChannel($progs, $scheds, $channel, $cid)
		{
			$this->output = '<div class="nnext_box">';
			$this->output .= '<h1>'.$channel.'</h1>';
			$this->output .= '<div class="rss_links">';
			$this->output .= '| <a title="RSS Now &amp; Next Feed for this Channel" href="/feeds/rss/nn/'.$cid.'">RSS FEED</a> | ';
			//$this->output .= '<a title="iCal Calendar File for this TV Channel" href="/feeds/ical/nn/'.$cid.'">iCal FEED</a> |';
			$this->output .= '</div>';
			$i = 0;
			foreach ($scheds as $s)
			{
				$this->output .= '<a href="/program/'.$progs[$i]->pid.'">'.$progs[$i]->title;
				$this->output .= '. ( '.$s->channel.' '.$s->formatDate('d/m/y time').' ) - '.$progs[$i]->getShortSynopsis();
				$this->output .= '</a>';
				$i++;
			}
			$this->output .= '</div>';
			return true;
		}
		
		function parseNNForRSS($progs, $scheds, $channel)
		{
			$this->output = '<?xml version="1.0"?><rss version="2.0">
	<channel>
		<title>BlueGhost PG Now &amp; Next for '.$channel.'</title>
		<description>Now and Next Data for '.$channel.'</description>
		<link>http://www.blueghosttv.co.uk/nn.php</link>
		<copyright>Data From backstage.bbc.co.uk, bleb.org, code by Michael Pritchard (blueghost.co.uk)</copyright>';
			$i = 0;
			foreach ($scheds as $s)
			{
				$progs[$i]->synopsis["short"] = str_replace("£", "&pound;", $progs[$i]->getShortSynopsis());
				$this->output .= "\t\t<item>\n";
				$this->output .= "\t\t\t<title>".htmlspecialchars($progs[$i]->title)." ( ".$s->formatDate("hour:min")." )</title>\n";
				$this->output .= "\t\t\t<pubDate>".date("D, d M Y H:i:s T")."</pubDate>\n";
				$this->output .= "\t\t\t<link>http://www.blueghosttv.co.uk/program/".$progs[$i]->pid."</link>\n";
				$this->output .= "\t\t\t<description>".htmlspecialchars($progs[$i]->getShortSynopsis())."</description>\n";
				$this->output .= "\t\t</item>\n";
				$i++;
			}
			$this->output .= '</channel></rss>';
			return true;
		}
		
		function parseMultipleNNForRSS($progs, $scheds, $channel)
		{
			$i = 0;
			foreach ($scheds as $s)
			{
				$progs[$i]->synopsis["short"] = str_replace("£", "&pound;", $progs[$i]->getShortSynopsis());
				$this->output .= "\t\t<item>\n";
				$this->output .= "\t\t\t<title>".$channel." - ".htmlspecialchars($progs[$i]->title)." ( ".$s->formatDate("hour:min")." )</title>\n";
				$this->output .= "\t\t\t<pubDate>".date("D, d M Y H:i:s T")."</pubDate>\n";
				$this->output .= "\t\t\t<link>http://www.blueghosttv.co.uk/program/".$progs[$i]->pid."</link>\n";
				$this->output .= "\t\t\t<description>".htmlspecialchars($progs[$i]->getShortSynopsis())."</description>\n";
				$this->output .= "\t\t</item>\n";
				$i++;
			}
			return true;
		}
		
		function parseMultipleNNForXML($progs, $scheds, $channel)
		{
			$i = 0;
			foreach ($scheds as $s)
			{
				$this->output .= "\t\t<item channel=\"".$channel."\" title=\"".htmlspecialchars($progs[$i]->title)."\" start_time=\"".$s->formatDate("hour:min")."\" end_time=\"".$s->getEndTime("hour:min")."\" link=\"http://www.blueghosttv.co.uk/program/".$progs[$i]->pid."\" synopsis=\"".htmlspecialchars($progs[$i]->getShortSynopsis())."\" />\n";
				$i++;
			}
			return true;
		}
		function parseMultipleNNForHTML($progs, $scheds, $channel)
		{
			$i = 0;
			foreach ($scheds as $s)
			{
				$this->output .= "<a href=\"http://www.blueghosttv.co.uk/program/".$progs[$i]->pid."\" target=\"_blank\" title=\"".htmlspecialchars($progs[$i]->getShortSynopsis())."\">".$channel." - ".htmlspecialchars($progs[$i]->title)." - ( ".$s->formatDate("hour:min")." - ".$s->getEndTime("hour:min")." )</a><br />\n";
				$i++;
			}
			return true;
		}
		
		function parseMultipleNNForATOM($progs, $scheds, $channel)
		{
			$i = 0;
			foreach ($scheds as $s)
			{
				$progs[$i]->synopsis["short"] = str_replace("£", "&pound;", $progs[$i]->getShortSynopsis());
				$this->output .= "\t<entry>\n";
				$this->output .= "\t\t<title>".$channel." - ".htmlspecialchars($progs[$i]->title)." ( ".$s->formatDate("hour:min")." )</title>\n";
				$this->output .= "\t\t<link href=\"http://www.blueghosttv.co.uk/program/".$progs[$i]->pid."\"/>\n";
				$id = str_replace("crid://", "", $progs[$i]->pid);
				$id = str_replace("/", ":", $id);
				$this->output .= "\t\t<id>urn:uuid:".$id."</id>\n";
				$this->output .= "\t\t<updated>".date("Y-m-d\TH:i:s\Z")."</updated>\n";
				$this->output .= "\t\t<summary>".htmlspecialchars($progs[$i]->getShortSynopsis())."</summary>\n";
				$this->output .= "\t</entry>\n";
				$i++;
			}
			return true;
		}
		
		function parseNNForiCal($progs, $scheds, $channel){
			return true;
		}		
	}
?>