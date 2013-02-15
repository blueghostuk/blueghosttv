<?php
	class Bleb_ProgramParser extends XML_Parser{
	
		var $tv;
		var $timestamp;
		//boolean
		var $passed10;
		
		var $currentProg;
		var $currrentSched;
		var $currentRelation;
		var $currentAV;

				
		function setChannel($channel, $time){
			$this->tv = $channel;
			$this->timestamp = $time;
		}
		
		function str_split($string, $split_length = 1) {
       		return explode("\r\n", chunk_split($string, $split_length));
   		}
		
		function parseFile(){
    		global $channel, $passed10;
    		$flag 		= "";
			
			$passed10 = false;
    
    		$channel = array();

    		// create parser
    		$xp = xml_parser_create();
	
    		// set element handler
    		xml_set_object($xp, &$this);
    		xml_set_element_handler($xp, "elementBegin", "elementEnd");
    		xml_set_character_data_handler($xp, "characterData");
    
    		// read XML file
    		if (!($fp = fopen($this->file, "r"))){
    			echo"Could not read ".$this->file;
				return false;
    		}
    
    		// parse data
    		while ($xml = fread($fp, 4096)){
    			if (!xml_parse($xp, $xml, feof($fp))){
					echo "XML parser error: " .xml_error_string(xml_get_error_code($xp));
					return false;
      			}
    		}

    		// destroy parser
    		xml_parser_free($xp);
			return true;
		}

  		// opening tag handler
		function elementBegin($parser, $name, $attributes){
    		global $currentTag;
			$currentTag = strtolower($name);
			$name = strtolower($name);
			switch($name){
				case 	'programme': //start new prog
					$this->currentProg = new Bleb_Program;
					$this->currentSched = new Bleb_Program_Schedule;
					$this->currentSched->timestamp = $this->timestamp;
				break;
				case	'infourl':
					$this->currentRelation = new TV_Related_Info;
					$this->currentRelation->setRealationType('infourl');
					$this->currentRelation->name = 'Information';
				break;
				/*case	'flags':
					$this->currentAV = new TV_AVAttributes;
				break;*/
				default:
					//echo 'Entered with '.$name.'</br>';
					//ignore
				break;
			}
		}

		function characterData($parser, $data){
			global $currentTag, $passed10;
			switch ($currentTag){
				case	'desc':
					$this->currentProg->addSynopsis('long',$data);
				break;
				//values contained in ( value_here ) ( value_here ) ...
				/*case	'flags':
					if (strpos("ws", strtolower($data)) !== false){
						$this->currentAV->video = '16:9';
					}
					if (strpos("stereo", strtolower($data)) !== false){
						$this->currentAV->audio = '2';
					}
					if (strpos("repeat", strtolower($data)) !== false){
						$this->currentAV->repeat = true;
					}
					if (strpos("subtitles", strtolower($data)) !== false){
						$this->currentProg->cc = 'yes';
					}
				break;*/
				case	'title':
					$this->currentProg->title .= $data;
					//echo '<br />found prog title = '.$data;
				break;
				case	'start':
					//check for next day
					$arr = $this->str_split($data);
					$hour = $arr[0].$arr[1];
					$mins = $arr[2].$arr[3];
					if ($hour >= 10)
						$passed10 = true;
						
					if ($passed10 && ($hour >= 0 && $hour < 10)){
						$this->currentProg->setIsNextDay(true);
					}
					$this->currentSched->start = $data;
					//echo 'set start date to '.$data.'<br />';
				break;
				case	'end':
					//echo 'set end date to '.$data.' | ';
					$this->currentSched->duration = $data;
				break;
				case	'infourl':
					$this->currentRelation->value .= $data;
				break;
				default:
				break;
			}		
		}

 		// closing tag handler
		function elementEnd($parser, $name){
    		global $currentTag;
			$currentTag = "";
    		// set flag if exiting <channel> or <item> block
			switch(strtolower($name)){
				case 	'programme': //end this prog
					$this->tv->addProgram($this->currentProg);
					$this->tv->addSchedule($this->currentSched);
				break;
				case	'infourl':
					$this->currentProg->addRelatedInfo($this->currentRelation);
				break;
				/*case	'flags':
					$this->currentProg->setAVAttribs($this->currentAV);
				break;*/
				default:
					//nothing
					//echo 'EXITED WITH :'.$name.' , or in lowerwcase : '.strtolower($name).'<br />';
				break;
			}
		}
  
	} //end class
?>