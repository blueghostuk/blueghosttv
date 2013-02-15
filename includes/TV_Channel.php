<?php
	class TV_Channel{
		var $id;
		var $serviceId;
		/*if not set isset($audio) == false
		 * then is video channel
		 */
		var $audio;
		var $title;
		var $programmes = array();
		var $schedule = array();
		var $tvany;
		
		function addProgram($prog){
			$this->programmes[] = $prog;
		}
		
		function setTVAny($tv){
			if ($tv != 1){
				$this->tvany = false;
			}else{
				$this->tvany = true;
			}
		}
		
		function isTVAny(){
			if ($this->tvany)
				return true;
			else
				return false;
		}
		
		function findProgram($pid){
			for ($i=0; $i < count($this->programmes); $i++){
				if ($this->programmes[$i]->pid == $pid){
					return $this->programmes[$i];
				}
			}
			return false;
		}
		
		/**
		 * hour = 0 - 24
		 */
		function getProgsInHour($hour){
			$scheds = array();
			$progs = array();
			$result = array();
			foreach($this->schedule as $s){
				if ($s->formatDate('hour') == $hour){
					$scheds[] = $s;
				}
			}
			foreach($scheds as $s){
				$progs[] = $this->findProgram($s->pid);
			}
			$result['sched'] = $scheds;
			$result['progs'] = $progs;
			//echo '<h2>found '.count($scheds).' scheds and '.count($progs).' progs in hour '.$hour.'</h2>';
			//echo '<h2>from  '.count($this->schedule).' scheds and '.count($this->programmes).' progs in hour '.$hour.'</h2>';
			return $result;
		}
		
		function getNowAndNext($time){
			$scheds = array();
			$progs = array();
			$result = array();
			$i = 0;
			$id = -1;
			foreach($this->schedule as $s){
				if ($id == $i){//next
					$scheds[] = $s;
				}else{
					if ($time <= $s->getEndTimeStamp() && $time >= $s->getStartTimeStamp()){//now
						$scheds[] = $s;
						$id = ($i+1);
					}
				}
				$i++;
			}
			foreach($scheds as $s){
				$progs[] = $this->findProgram($s->pid);
			}
			$result['sched'] = $scheds;
			$result['progs'] = $progs;
			//echo '<h2>found '.count($scheds).' scheds and '.count($progs).' progs in hour '.$hour.'</h2>';
			return $result;
		}
		
		function findProgramIndex($pid){
			for ($i=0; $i < count($this->programmes); $i++){
				//echo "<br />Looking for prog id:".$pid." against ".$this->programmes[$i]->pid."";
				if ($this->programmes[$i]->pid == $pid){
					return $i;
				}
			}
			return false;
		}
		
		function updateProgram($prog){
			$index = findProgramIndex($prog->pid);
			if ($index != false){
				$this->programmes[$index] = $prog;
				return true;
			}
			return false;
		}
		
		function addSchedule($sched){
			$sched->index = $this->findProgramIndex($sched->pid);
			$this->schedule[] = $sched;
		}
		
	}
?>