<?php
	class Bleb_Program_Schedule{
		var $start;
		var $duration;
		var $pid;
		var $index;
		var $channel;
		var $timestamp;
		
		function str_split($string, $split_length = 1) {
       		return explode("\r\n", chunk_split($string, $split_length));
   		}
		
		function datediff($date1, $date2) {
			// $date1 is subtracted from $date2.
			// if $date2 is not specified, then current date is assumed.
			
			//Splits date apart
			list($date1_month, $date1_day, $date1_year) = split('[/.-]', $date1);
			
			if (!$date2) {
			  $date2_year = date("Y"); //Gets Current Year
			  $date2_month = date("m"); //Gets Current Month
			  $date2_day = date("d"); //Gets Current Day
			} else {
			  list($date2_month, $date2_day, $date2_year) = split('[/.-]', $date2);
			}
			
			$date1 = mktime(0,0,0,$date1_month, $date1_day, $date1_year); //Gets Unix timestamp for $date1
			$date2 = mktime(0,0,0,$date2_month, $date2_day, $date2_year); //Gets Unix timestamp for $date2
			
			return $date2-$date1; //Calcuates Difference
			//return floor($difference/60/60/24); //Calculates Days Old
		}
		
		function formatDate($format = 'standard'){
			switch ($format){
				case 'hour'://hour only in 24 hour format no leading zero
					$format = 'G';
				break;
				case 'd/m/y time':
					$format = 'd/m/y H:i';
				break;
				case 'hour:min':
					$format = 'H:i';
				break;
				case 'ical':
					$format = "Ymd\THis";
				break;
				case 'sql':
					$format = 'YmdHi00';
				break;
				case 'rss'://day date month year hour:minute
				default:
					$format = 'D, d M Y H:i:s GMT';
				break;
				case 'link':
					$format = 'j_n_Y_H_s';
				break;
				case 'standard'://day date month year hour:minute
				default:
					$format = 'D dS M y H:i';
				break;
			}
			$arr = $this->str_split($this->start);
			$hour = $arr[0].$arr[1];
			//$hour++;
			$mins = $arr[2].$arr[3];
			$time = $this->timestamp + (60*60*$hour) + (60*$mins);
			return date($format, $time);
		}
		
		function getEndTime($format = 'standard'){
			switch ($format){
				case 'hour'://hour only in 24 hour format no leading zero
					$format = 'G';
				break;
				case 'd/m/y time':
					$format = 'd/m/y H:i';
				break;
				case 'ical':
					$format = "Ymd\THis";
				break;
				case 'hour:min':
					$format = 'H:i';
				break;
				case 'standard'://day date month year hour:minute
				default:
					$format = 'D dS M y H:i';
				break;
			}	
			$arr = $this->str_split($this->duration);
			$hour = $arr[0].$arr[1];
			$mins = $arr[2].$arr[3];
			$time = $this->timestamp + (60*60*$hour) + (60*$mins);
			return date($format, $time);
		}
		
		
		function getDuration($format = 'standard'){
			switch ($format){
				case 'sql':
					$format = '\P\TH\Hi\M00';
				break;
				case 'standard': //hour:minute
				default:
					$format = 'G:i';
				break;
			}
			$arr = $this->str_split($this->start);
			$hour = $arr[0].$arr[1];
			$mins = $arr[2].$arr[3];
			$stime = $this->timestamp + (60*60*$hour) + (60*$mins);
			$arr = $this->str_split($this->duration);
			$hour = $arr[0].$arr[1];
			$mins = $arr[2].$arr[3];
			$etime = $this->timestamp + (60*60*$hour) + (60*$mins);
			$time = $etime - $stime;
			$time -=60*60;
			return date($format, $time);
		}
	}
?>