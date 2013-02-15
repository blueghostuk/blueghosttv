<?php
	class TV_ScheduleParser extends XML_Parser{
	
		var $tv;
		var $sched;
		
		//boolean vars
		var $basicD;
		var $genre;
		var $currentGenre;
		
		function TV_ScheduleParser($file, $tv){
			$this->setSource($file);
			$this->tv = $tv;
		}
		
		function parseFile(){
    		global $channel, $type, $basicD;
    		$flag 		= "";
    		$basicD		= false;
			$genre		= false;
    
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
    		global $currentTag, $basicD, $genre;
			$currentTag = strtolower($name);
			$name = strtolower($name);
			switch($name){
				case 	'scheduleevent': //start sched
					$this->sched = new TV_Program_Schedule;
				break;
				case	'program': //prog id
					$this->sched->pid = $attributes[strtoupper("crid")];
				break;
				default:
					//echo 'Entered with '.$name.'</br>';
					//ignore
				break;
			}
		}

		function characterData($parser, $data){
			global $currentTag, $basicD, $genre;
			//$data = strtr($data, array('<![CDATA['=>'', ']]>'=>''));
			switch ($currentTag){
				case	'publishedstarttime':
					$this->sched->start = $data;
				break;
				case	'publishedduration':
					//needs to be added not new
					$this->sched->duration = $data;
				break;
			}
		}

 		// closing tag handler
		function elementEnd($parser, $name){
    		global $currentTag, $basicD, $genre;
			$currentTag = "";
    		// set flag if exiting <channel> or <item> block
			switch(strtolower($name)){
				case 	'scheduleevent': //end this prog
					//$tpeg		= false;
					$this->tv->addSchedule($this->sched);
					//echo 'Exited tpeg</br>';
				break;
				default:
					//nothing
					//echo 'EXITED WITH :'.$name.' , or in lowerwcase : '.strtolower($name).'<br />';
				break;
			}
		}
  
	} //end class
?>