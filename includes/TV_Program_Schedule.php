<?php
	class TV_Program_Schedule{
		var $start;
		var $duration;
		var $pid;
		var $index;
		var $channel;
		
		var $isBleb = false;
		
		/**
		 * Taken from http://www.idealog.us/2005/10/php_timestamp_h.html
		 */
		function tstamptotime($tstamp) {
			// converts ISODATE to unix date
			// 1984-09-01T14:21:31Z
			//echo 'got '.$tstamp.'<br />';
			if (strpos($tstamp, 'T') !== false){/*ISODATE*/
				//echo 'ISODATE<br />';
				sscanf($tstamp,"%u-%u-%uT%u:%u:%uZ",$year,$month,$day,$hour,$min,$sec);
				$newtstamp=mktime($hour,$min,$sec,$month,$day,$year);
				if (!$this->isBleb)
					$newtstamp += 60*60;
			}else{/*MYSQL TIMESTAMP -> UNIX_TIMESTAMP*/
				//echo 'SQLDATE<br />';
				$newtstamp = $tstamp;
				if (!$this->isBleb)
					$newtstamp += 60*60;
			}
			//echo 'to '.$newtstamp.'<br />';
			//gmt
			//$newtstamp+=(60*60);
			return $newtstamp;
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
			return date($format, $this->tstamptotime($this->start));
		}
		
		function getStartTimeStamp(){
			return $this->tstamptotime($this->start);
		}
		
		function getEndTimeStamp(){
			sscanf($this->duration,"PT%uH%uM%uS",$hour,$min,$sec);
			$time = (60*60*$hour) + (60*$min) + $sec;
			return $time+$this->tstamptotime($this->start);
		}
		
		function getEndTime($format = 'standard'){
			switch ($format){
				case 'hour'://hour only in 24 hour format no leading zero
					$format = 'G';
				break;
				case 'minute'://min 00- 59
					$format = 'i';
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
				case 'standard'://day date month year hour:minute
				default:
					$format = 'D dS M y H:i';
				break;
			}	
			sscanf($this->duration,"PT%uH%uM%uS",$hour,$min,$sec);
			$time = (60*60*$hour) + (60*$min) + $sec;
			return date($format, ($this->tstamptotime($this->start)+$time));
		}
		
		function getDuration($format = 'standard'){
			switch ($format){
				case 'hours':
					$format = 'G';
				case 'standard': //hour:minute
				default:
					$format = 'G:i';
				break;
			}
			sscanf($this->duration,"PT%uH%uM%uS",$hour,$min,$sec);
			$time = mktime($hour,$min,$sec);
			return date($format, $time);
		}
	}
?>