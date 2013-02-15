<?php
	
	class Bleb_Program{
		var $pid;					//not used
		var $title;
		var $synopsis;				//= array();
		var $promotional = array();	//not uses
		var $genres = array();		//not used
		var $related = array();
		var $keywords = array();	//not used
		var $series;				//not used
		var $avattribs;				//new type Bleb_AVAttributes
		var $signed;				//not used
		var $cc;					//not used
		var $subs;					//new attrib
		var $sched;
		var $isnextday;
		
		function loadFromDB($array){
			$this->pid = $array[0];
			$this->title = $array[2];
			$this->addSynopsis('short', $array[3]);
			if (strlen($array[4]) > 0)
				$this->addSynopsis('long', $array[4]);
			$av = new TV_AVAttributes;
			$av->audio = $array[5];
			$av->video = $array[6];
			$av->ad = $array[7];
			$this->setAVAttribs($av);
			$this->signed = $array[8];
			$this->cc = $array[9];
			$this->series = $array[10];
		}
		
		function setIsNextDay($value){
			$this->isnextday = $value;
		}
		
		function isOnNextDay(){
			if (isset($this->isnextday) && $this->isnextday)
				return true;
			else
				return false;
		}
		
		function addPromotional($data){
			$this->promotional[] = $data;
		}
		
		function addSynopsis($type, $value){
			$this->synopsis .= $value;
		}
		
		function getLongSynopsis(){
			//if (isset($this->synopsis['long']))
				//return $this->synopsis['long'];
			//else
				return $this->getShortSynopsis();
		}
		
		function getShortSynopsis(){
			return $this->synopsis;
		}
		
		/**
		 * Keywords added as tags under keywords category
		 */
		function addKeyword($data){
			//$this->keywords[] = $data;
		}
		
		/**
		 * If type == keyword then add title to keywords
		 */
		function addGenre($genre){
			/*if (strtolower($genre->type) == 'keyword')
				$this->addKeyword($genre->title);
			else
				$this->genres[] = $genre;*/
		}
		
		function addRelatedInfo($relation){
			$this->related[] = $relation;
		}
		
		function setAVAttribs($av){
			$this->avattribs = $av;
		}
		
		function getAVAttribs(){
			return $this->avattribs;
		}
	}
?>