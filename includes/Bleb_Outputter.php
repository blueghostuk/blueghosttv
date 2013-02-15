<?php
	require('paths.php');
	class Bleb_Outputter{
		var $tv;
		var $output;
		
		function Bleb_Outputter($tv){
			$this->tv = $tv;
		}
		
		function returnParseProgramForTable($prog, $sched)
		{
			$prog_link = $prog->pid.'_'.$sched->channel.'_'.$sched->formatDate('link');
			$output = '<strong>'.$sched->formatDate('hour:min').'</strong><br/>';
			$output.= '<a class="program" href="/program/'.$prog_link.'" title="'.$prog->getShortSynopsis().'">'.$prog->title.'</a><br/>';
			return $output;
		}
		
		function returnParseProgramForTableWD($prog, $sched)
		{
			$prog_link = $prog->pid.'_'.$sched->channel.'_'.$sched->formatDate('link');
			if ($prog->isOnNextDay())
			{
				$nd = ' - Next Day';
			}
			else
			{
				$nd = '';
			}
			$output = '<strong>'.$sched->formatDate('hour:min').$nd.'</strong><br/>';
			$output.= '<a class="program" href="/program/'.$prog_link.'" title="'.$prog->getShortSynopsis().'">'.$prog->title.'</a>';
			$output.= '<p class="prog_desc">'.$prog->getShortSynopsis().'</p>';
			return $output;
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
					$prog_link = $progs[$i]->pid.'_'.$sched->channel.'_'.$sched->formatDate('link');
					$this->output .= '<li><a href="/program/'.$prog_link.'">';
					$this->output .= $sched->formatDate('d/m/y time').' - '.$progs[$i]->getShortSynopsis();
					$this->output .= '</a></li>';
				}
			}
			$this->output .= '</ul></p></div>';
			return true;
		}
		
		function parseProgramLongDescription($scheds)
		{
			$com_title = str_replace(" ", "+", $this->tv->title);
			$com_title = str_replace("'", "\'", $com_title);
			$this->output .= '<div class="rss_links">';
			$this->output .= '| <a title="HTML Feed for this Series" href="/feeds/html/series/'.$com_title.'">HTML FEED</a> | ';
			$this->output .= '<a title="RSS Feed for this Series" href="/feeds/rss/series/'.$com_title.'">RSS FEED</a> | ';
			$this->output .= '<a href="webcal://www.blueghosttv.co.uk/feeds/ical/series/'.$com_title.'">iCal FEED</a> |';
			$this->output .= '</div>';
			$seriesText = '[ SERIES: <a href="/feeds/html/series/'.$com_title.'" onclick="doSeriesSearch(\''.$com_title.'\');return false;">'.$this->tv->title.'</a> ]<div id="seriesResults" class="hidden"></div>';
			$this->output .= '<div class="prog">';
			foreach ($scheds as $sched){
				$this->output .= '<p><strong>'.$sched->channel.' - '.$sched->formatDate('standard').' - '.$sched->getEndTime('hour:min').'</strong></p>';
			}
			$this->output .= '<p class="prog_title">'.$this->tv->title.'&nbsp;'.$seriesText.'</p>';
			$this->output .= '<p class="prog_syn">'.$this->tv->getLongSynopsis().'</p>';
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
			$this->output .= '</p>';
			/*if (count($this->tv->genres) > 0){
				$this->output .= '<p><strong>Program Genres</strong></p>';
				$this->output .= '<p>';
				foreach($this->tv->genres as $g){
					$this->output .= '<a href="genre.php?g='.$g->title.'">'.$g->title.'</a> - '.$g->type.'<br />';
				}
			}*/
			
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
			<!-- <option value="Blended">Blended</option> -->
		</select>
		<input type="button" value="Search" onclick="javascript:doAmazonSearch();">
	</form>';
				$this->output .= '</div>';
			}
			
			$this->output .= '</div>';
			$this->output .= '<div class="rss_links">';
			$this->output .= '| <a title="HTML Feed for this Series" href="/feeds/html/series/'.$com_title.'">HTML FEED</a> | ';
			$this->output .= '<a title="RSS Feed for this Series" href="/feeds/rss/series/'.$com_title.'">RSS FEED</a> | ';
			$this->output .= '<a title="iCal Calendar File for this Series" href="webcal://www.blueghosttv.co.uk/feeds/ical/series/'.$com_title.'">iCal FEED</a> |';
			$this->output .= '</div>';
			return true;
		}
		
		/*$this->tv = genre name*/
		function parseGenreList($progs, $scheds)
		{
			$this->output .= '<div class="series_list">';
			$this->output .= '<h1>First 50 Programs for Genre: '.$this->tv.'</h1>';
			$this->output .= '<p><ul>';
			for($i=0; $i < count($progs); $i++)
			{
				$prog_link = $progs[$i]->pid.'_'.$sched->channel.'_'.$sched->formatDate('link');
				$this->output .= '<li><a href="/program/'.$prog_link.'">';
				$this->output .= $scheds[$i]->formatDate('d/m/y time').' - '.$progs[$i]->title;
				$this->output .= '</a></li>';
			}
			$this->output .= '</ul></p></div>';
			return true;
		}
		
		function parseSingleChannel($date_text, $date)
		{
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
			$this->output .= '| <a href="/feeds/rss/channel/'.$this->tv->id.'/'.$date.'">RSS FEED</a> | ';
			$this->output .= '<a href="webcal://www.blueghosttv.co.uk/feeds/ical/channel/'.$this->tv->id.'/'.$date.'">iCal FEED</a> |';
			$this->output .= '</div></div>';
			return true;
		}
		
		function getOutput()
		{
			return $this->output;
		}
	}
?>