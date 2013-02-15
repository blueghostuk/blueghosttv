<?php
	class TV_ProgramParser extends XML_Parser{
	
		var $tv;
		
		//boolean vars
		var $basicD;
		var $genre;
		var $related;
		var $av;
		var $aa;
		var $va;
		/**
		 * Synopsis type
		 * false = short
		 * true  = long
		 */
		var $st;
		
		var $currentGenre;
		var $currentProg;
		var $currentRelation;
		var $currentAV;
				
		function setChannel($channel){
			$this->tv = $channel;
		}
		
		function parseFile(){
    		global $channel, $type, $basicD, $related, $av, $st;
    		$flag 		= "";
    		$basicD		= false;
			$genre		= false;
			$related	= false;
			$av			= false;
			$aa			= false;
			$va			= false;
			$st			= false;
    
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
    		global $currentTag, $basicD, $genre, $related, $av, $aa, $va, $st;
			$currentTag = strtolower($name);
			$name = strtolower($name);
			switch($name){
				case 	'programinformation': //start new prog
					$this->currentProg = new TV_Program;
					$this->currentProg->pid = $attributes[strtoupper("programId")];
				break;
				case	'basicdescription': //prog desc
					$basicD = true;
				break;
				case	'synopsis'://get type
					switch ($attributes[strtoupper("length")]){
						case 'long':
							$st = true;
						break;
						case 'short':
						default:
							$st = false;
						break;
					}
				break;
				case	'genre': //genre
					$genre = true;
					$this->currentGenre = new TV_Genre;
					$this->currentGenre->setGenreType($attributes[strtoupper("href")]);
				break;
				case 	'relatedmaterial':
					$related = true;
					$this->currentRelation = new TV_Related_Info;	
				break;
				case	'howrelated':
					if ($related){ /*check in this section*/
						$this->currentRelation->setRealationType($attributes[strtoupper("href")]);
					}
				break;
				case	'avattributes':
					$this->currentAV = new TV_AVAttributes;
					$av = true;
				break;
				case	'audioattributes':
					$aa = true;
				break;
				case	'videoattributes':
					$va = true;
				break;
				case	'memberof': //tvlink
					$this->currentProg->series = $attributes[strtoupper("crid")];
				break;
				default:
					//echo 'Entered with '.$name.'</br>';
					//ignore
				break;
			}
		}

		function characterData($parser, $data){
			global $currentTag, $basicD, $genre, $related, $av, $aa, $va, $st;
			//$data = strtr($data, array('<![CDATA['=>'', ']]>'=>''));
			if ($basicD){ /*prog descs*/
				switch ($currentTag){
					case	'title':
						$this->currentProg->title = $data;
					break;
					case	'synopsis':
						//needs to be added not new
						if ($st)
							$type = 'long';
						else
							$type = 'short';
						$this->currentProg->addSynopsis($type,htmlspecialchars($data));
					break;
					case	'promotionalinformation':
						$this->currentProg->addPromotional($data);
					break;
					case	'captionlanguage':
						$this->currentProg->cc = $data;
					break;
					case	'signlanguage':
						$this->currentProg->signed = $data;
					break;
					case	'keyword':
						$this->currentProg->addKeyword($data);
					break;
				}
			}
			if ($genre){
				switch ($currentTag){
					case 'name':
						$this->currentGenre->setTitle($data);
					break;
					default:
					break;
				}
			}	
			if ($related){
				switch ($currentTag){
					case 'name':
						$this->currentRelation->name = $data;
					break;
					case 'mpeg7:mediauri':
						$this->currentRelation->value = $data;
					break;
					default:
					break;
				}
			}
			if ($av){
				if ($aa){
					if (isset($this->currentAV->audio)){/*set AD*/
						//echo '<br />current audio = '.$this->currentAV->audio.', set ad to '.$data;
						if ($currentTag == 'audiolanguage')
							$this->currentAV->ad = $data;
					}else{/*set channels*/
						if ($currentTag == 'numofchannels')
							$this->currentAV->audio = $data;
					}
				}elseif ($va && $currentTag == 'aspectratio'){
					$this->currentAV->video = $data;
				}
			}
				
		}

 		// closing tag handler
		function elementEnd($parser, $name){
    		global $currentTag, $basicD, $genre, $related, $av, $aa, $va, $st;
			$currentTag = "";
    		// set flag if exiting <channel> or <item> block
			switch(strtolower($name)){
				case 'programinformation': //end this prog
					$this->tv->addProgram($this->currentProg);
					break;
				case	'basicdescription': //prog desc
					$basicD = false;
				break;
				case	'synopsis'://reset
					$st = false;
				break;
				case	'genre': //genre
					$genre = false;
					$this->currentProg->addGenre($this->currentGenre);
				break;
				case 	'relatedmaterial':
					$related = false;
					$this->currentProg->addRelatedInfo($this->currentRelation);
				break;
				case	'avattributes':
					$av = false;
					$this->currentProg->setAVAttribs($this->currentAV);
				break;
				case	'audioattributes':
					$aa = false;
				break;
				case	'videoattributes':
					$va = false;
				break;
				default:
					//nothing
					//echo 'EXITED WITH :'.$name.' , or in lowerwcase : '.strtolower($name).'<br />';
				break;
			}
		}
  
	} //end class
?>