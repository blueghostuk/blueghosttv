<?php

	class TV_FileSearcher 
	{
		/* 
		 * an array of the channel id numbers to search
		 */
		var $channels;
		
		/* 
		 * the search query
		 */
		var $query;
		
		/* 
		 * each item in array is an array of a result holding:
		 * 		: matching text		= matched text
		 *		: link_href 		= nearest link to result in page
		 *		: file_url			= url of file to public
		 */
		var $results = array();
		
		function TV_FileSearcher($channels, $query)
		{
			$this->channels	= $channels;
			$this->query 	= $query;
		}
		
		/*
		 * Performs the search with the given channels and query given in constructor.
		 * fills the $this->results array with results
		 */
		function performSearch()
		{
			$dir 		= '/home/blueghos/www/tv/';
			$cache_dir = $dir.'cache/html/single/';
	
			for($i = -31; $i < 7; $i++)
			{
				$time = time() + ($i * 24 * 60 * 60);
				$date = date("Ymd", $time);
				foreach($this->channels as $channel)
				{
					$file = $cache_dir.$channel.'/'.$date.'.html';
					if(file_exists($file))
					{
						$data = strtolower(strip_tags(file_get_contents($file)));
						if (ereg($this->query, $data))
						{
							$arr = array();
							$arr['matching_text']	= '';
							$arr['link_href'] 		= '';
							$arr['file_url'] 		= $site_url.'channel/'.$channel.'/'.$i;
							$this->results[]		= $arr;
						}
					}
				}
			}
		}
		
		/*
		 * simply returns $this->results. Use this calling method incase getting results
		 * requires more than just returning the one variable in future
		 */
		function getResults()
		{
			return $this->results;
		}
	}
?>