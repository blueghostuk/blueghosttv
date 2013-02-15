<?php
	class TV_View{
		var $channels = array();
		
		function addChannel($chan){
			$this->channels[] = $chan;
		}
		
		function getChannels(){
			return $this->channels;
		}
		
	}
?>