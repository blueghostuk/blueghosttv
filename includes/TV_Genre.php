<?php
	class TV_Genre{
		
		function setGenreType($type){
			if (strpos(strtolower($type), "content") !== false)
				$this->type = 'Content';
			elseif (strpos(strtolower($type), "intention") !== false)
				$this->type = 'Intention';
			elseif (strpos(strtolower($type), "intendedaudience") !== false)
				$this->type = 'Intended Audience';
			elseif (strpos(strtolower($type), "format") !== false)
				$this->type = 'Format';
			elseif (strpos(strtolower($type), "atmosphere") !== false)
				$this->type = 'Atmosphere';
			elseif (strpos(strtolower($type), "origination") !== false)
				$this->type = 'Origination';
			elseif (strpos(strtolower($type), "intention") !== false)
				$this->type = 'Intended Audience';
			elseif (strpos(strtolower($type), "intention") !== false)
				$this->type = 'Intended Audience';
			else
				$this->type = 'Other';
		}
		
		function setTitle($title){
			$this->title = ucfirst(strtolower($title));
		}
		var $type;
		var $title;
	}
?>