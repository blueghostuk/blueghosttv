<?php

	class AmazonSearchResults {
		var $terms;
		var $items = array();
		
		function addResult($item){
			$this->items[] = $item;
		}
	
	}
?>