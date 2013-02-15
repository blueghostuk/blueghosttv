<?php

	class AmazonItem {
		var $asin;
		var $item_page;
		var $title;
		var $images = array();
		var $list_price;
		var $ama_price;
		var $avail;
		var $actors = array();
		var $ama_review;
		var $type;
		
		function addImage($size, $value){
			$this->images[$size] = $value;
		}
		
		function getImage($size){
			return $this->images[$size];
		}
		
		function addActor($actor){
			$this->actors[] = $actor;
		}
		
		function isMediaType(){
			if ($this->type == 'DVD')
				return true;
			if ($this->type == 'Music')
				return true;
			if ($this->type == 'Book')
				return true;
			if ($this->type == 'DVD')
				return true;
			return false;
		}
	}
?>