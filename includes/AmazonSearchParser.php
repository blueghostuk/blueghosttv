<?php
	class AmazonSearchParser extends XML_Parser{
	
		/*$this->file = search url*/
		
		var $inItem;
		var $sImage;
		var $mImage;
		var $lImage;
		var $iAtrib;
		var $ama_pr;
		
		var $results;
		var $currentItem;
		//boolean vars
				
		function parseFile(){
    		global $channel, $inItem, $sImage, $mImage, $lImage, $iAtrib, $ama_pr;
    		$flag 		= "";
			
 
			$inItem = false;
    		$sImage = false;
			$mImage = false;
			$lImage = false;
			$iAtrib = false;
			$ama_pr = false;
			
    		$channel = array();

    		
    		// create parser
    		$xp = xml_parser_create();
	
    		// set element handler
    		xml_set_object($xp, &$this);
    		xml_set_element_handler($xp, "elementBegin", "elementEnd");
    		xml_set_character_data_handler($xp, "characterData");
    
    		// read XML file
    		if (!($fp = fopen($this->file, "r"))){
    			die("Could not read $file");
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
    		global $currentTag, $inItem, $sImage, $mImage, $lImage, $iAtrib;
			$currentTag = strtolower($name);
			$name = strtolower($name);
			switch($name){
				case 	'item': //start new prog
					$inItem = true;
					$this->currentItem = new AmazonItem;
				break;
				case	'smallimage':
					$sImage = true;
				break;
				case	'mediumimage':
					$mImage = true;
				break;
				case	'largeimage':
					$lImage = true;
				break;
				case	'itemattributes':
					$iAtrib = true;
				break;
				default:
					//echo 'Entered with '.$name.'</br>';
					//ignore
				break;
			}
		}

		function characterData($parser, $data){
			global $currentTag, $inItem, $sImage, $mImage, $lImage, $iAtrib, $ama_pr;
			//$data = strtr($data, array('<![CDATA['=>'', ']]>'=>''));
			if ($inItem){
				switch ($currentTag){
					case 'asin':
						$this->currentItem->asin = $data;
					break;
					case 'detailpageurl':
						$this->currentItem->item_page .= $data;
					break;
					case 'merchantid':
						if ($data == 'A3P5ROKL5A1OLE')
							$ama_pr = true;
					break;
					case 'formattedprice':
						if ($ama_pr)
							$this->currentItem->ama_price = str_replace("Â", "", $data);
					break;
					case 'availability':
						if ($ama_pr)
							$this->currentItem->avail = $data;
					break;
				}	
			}	
			if ($sImage){
				if ($currentTag == 'url')
					$this->currentItem->addImage('small', $data);
			}
			if ($mImage){
				if ($currentTag == 'url')
					$this->currentItem->addImage('medium', $data);
			}
			if ($lImage){
				if ($currentTag == 'url')
					$this->currentItem->addImage('large', $data);
			}
			if ($iAtrib){
				switch ($currentTag){
					case 'actor':
						$this->currentItem->addActor($data);
					break;
					case 'formattedprice':
						$this->currentItem->list_price = str_replace("Â", "", $data);
					break;
					case 'title':
						$this->currentItem->title = $data;
					break;
					case 'productgroup':
						$this->currentItem->type = $data;
					break;
					default:
					break;
				}
			}
		}

 		// closing tag handler
		function elementEnd($parser, $name){
    		global $currentTag, $inItem, $currentItem, $sImage, $mImage, $lImage, $iAtrib, $ama_pr;
			$currentTag = "";
    		// set flag if exiting <channel> or <item> block
			switch(strtolower($name)){
				case 'item': //end this prog
					$this->results->addResult($this->currentItem);
				break;
				case	'smallimage':
					$sImage = false;
				break;
				case	'mediumimage':
					$mImage = false;
				break;
				case	'largeimage':
					$lImage = false;
				break;
				case	'itemattributes':
					$iAtrib = false;
				break;
				case	'offer':
					$ama_pr = false;
				default:
					//nothing
					//echo 'EXITED WITH :'.$name.' , or in lowerwcase : '.strtolower($name).'<br />';
				break;
			}
		}
  
	} //end class
?>