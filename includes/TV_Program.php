<?php
	
	class TV_Program{
		var $pid;
		var $title;
		var $synopsis = array();
		var $promotional = array();
		var $genres = array();
		var $related = array();
		var $keywords = array();
		var $series;
		var $avattribs;
		var $signed;
		var $cc;
		
		function loadFromDB($array){
			$this->pid = $array[1];
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
		
		function addPromotional($data){
			$this->promotional[] = $data;
		}
		
		function addSynopsis($type, $value){
			$this->synopsis[$type] .= $value;
		}
		
		function getLongSynopsis(){
			if (isset($this->synopsis['long']))
				return $this->synopsis['long'];
			else
				return $this->getShortSynopsis();
		}
		
		function getShortSynopsis(){
			return $this->synopsis['short'];
		}
		
		/**
		 * Keywords added as tags under keywords category
		 */
		function addKeyword($data){
			$this->keywords[] = $data;
		}
		
		/**
		 * If type == keyword then add title to keywords
		 */
		function addGenre($genre){
			if (strtolower($genre->type) == 'keyword')
				$this->addKeyword($genre->title);
			else
				$this->genres[] = $genre;
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