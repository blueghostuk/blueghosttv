<?php
	class TV_Related_Info{
	
		function setRealationType($type){
			switch ($type){
				case 'infourl': /*bleb url*/
				case 'urn:tva:metadata:cs:HowRelatedCS:2005:10':
					$this->type = 'Website';
				break;
				case 'urn:tva:metadata:cs:HowRelatedCS:2005:14':
					$this->type = 'Email';
				break;
				default:
					$this->type = 'Unknown ('.$type.')';
				break;
			}
		}
		
		var $type;
		var $name;
		var $value;
	}
?>