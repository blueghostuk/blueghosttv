<?php

	class Bleb_AVAttributes{		
		var $audio;
		var $ad;
		var $video;
		
		function isWideScreen(){
			if ($this->video == '16:9')
				return true;
			else
				return false;
		}
		
		function getAspectRatio(){
			return $this->video;
		}
		
		function getAudioChannels(){
			return $this->audio;
		}
		
		function isAD(){
			if (isset($this->ad)){
				return true;
			}else{
				return false;
			}
		}
	}
?>