<?php
/**
 * Amazon Item Page 
 * Web Location: ???
 * Version 0.1
 * Date:   0.1 - 28/03/2005
 * Copyright Michael Pritchard 2005
 * Web: http://www.blueghost.co.uk
 */
class AmazonItem{
	var $ASIN;
	var $title;
	var $DBC;
	var $smallImage;
	var $mediumImage;
	var $largeImage;
	var $amazonPrice;
	var $lowPrice;
	
	function AmazonItem($ASIN, $Database){
		$this->DBC	= $Database;
		$this->ASIN	= $ASIN;
		$this->initalise();
	}
	
	function initalise(){}
	
}
?>