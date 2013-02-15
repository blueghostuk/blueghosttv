<?php
	class AmazonSearch{
		var $terms;
		var $results;
		var $cat;
		var $sort_var;
		var $condition;
		var $group;
		var $operation;
		var $page;	
		
		
		function setTerms($terms, $cat = "Blended"){
			$terms = trim($terms);
			$terms = str_replace(" ", "+", $terms);
			$this->terms = $terms;
			$this->cat = $cat;
			$this->sort_var		= 'salesrank';
			$this->condition 	= 'New';
			$this->group 		= 'Medium,Offers';
			$this->operation	= 'ItemSearch';
		}
		
		/**
		 * checks against the search cache
		 * @return true if if item is out of date, false if still valid
		 */
		function checkCache(){
			//echo 'checkCache<br />';
			$url = '/home/blueghos/www/tv/cache/amazon/xml_cache/'.$this->terms.'_'.$this->cat.'.xml';
			if (!file_exists($url))
				return true;;
			if ( (time() - (24*60*60)) >= filectime($url) )
				return true;
			else
				return false;		
		}
		
		function getPathName(){
			return '/home/blueghos/www/tv/cache/amazon/xml_cache/'.$this->terms.'_'.$this->cat.'.xml';
		}
		
		/**
		 * checks against the search cache
		 * @return true if if item is out of date, false if still valid
		 */
		function checkResultsCache(){
			//echo 'checkResultsCache<br />';
			if (isset($this->page)){
				$page_ref = '_'.$this->page;
			}else{
				$page_ref = '';
			}
			$url = '/home/blueghos/www/tv/cache/amazon/results_cache/'.$this->terms.'_'.$this->cat.$page_ref.'.html';
			if (!file_exists($url))
				return true;
			if ( (time() - (24*60*60)) >= filectime($url) )
				return true;
			else
				return false;		
		}
		
		function returnResults(){
			if (isset($this->page)){
				$page_ref = '_'.$this->page;
			}else{
				$page_ref = '';
			}
			$url = '/home/blueghos/www/tv/cache/amazon/results_cache/'.$this->terms.'_'.$this->cat.$page_ref.'.html';
			if (file_exists($url))
				return file_get_contents($url);
			else
				return false;
		}
		
		function getResults(){
			//echo 'getResults<br />';
			if ($this->checkCache())
				$this->getXML();
			$url = '/home/blueghos/www/tv/cache/amazon/xml_cache/'.$this->terms.'_'.$this->cat.'.xml';
			$parser = new AmazonSearchParser($url);
			$parser->results = $this->results;
			$parser->parseFile();
			$this->results = $parser->results;
			unlink($url);
		}
		
		function parseResultsToFile(){
			$count = 0;
			foreach ($this->results->items as $item){
				if ($item->isMediaType()){
					$count++;
					//@copy($item->getImage('small'), '/home/blueghos/www/tv/cache/amazon/image_cache/'.$item->asin.'.jpg');
					$op .= '<li><a href="http://www.blueghosttv.co.uk/amazon/item/'.$item->asin.'" title="See more on this item"><img src="'.$item->getImage('small').'" alt="item image" valign="absmiddle" />'.$item->title.' ('.$item->type.') - '.$item->ama_price.'</a> - <a href="'.$item->item_page.'" target="_blank" title="See this item on Amazon">Buy Now</a> or <a href="http://www.blueghosttv.co.uk/amazon/cart.php?op=add&id='.$item->asin.'" title="Add this item to your cart">Add To Cart</a></li>';
				}
			}
			if ($count >0){
				$op = '<ul>'.$op.'</ul>';
				$op .= '<br /><span class="disclaimer">Prices correct as of '.date("l jS F Y H:i").'. Check prices on the Amazon Website before purchase.</span><br />';
				$file = '/home/blueghos/www/tv/cache/amazon/results_cache/'.$this->terms.'_'.$this->cat.'.html';
				if (!$handle = fopen($file, 'w')) {
   					echo "Cannot Open File ($file)";
					//exit;
   				}

   				if (!fwrite($handle, $op)) {
    				echo "Cannot write to file ($file)";
      				//exit;
   				}
				return true;
			}else{
				return false;
			}
		}
		
		function getXML(){
			//echo 'getXML<br />';
			$url = '/home/blueghos/www/tv/cache/amazon/xml_cache/'.$this->terms.'_'.$this->cat.'.xml';
			copy($this->generateXMLUrl(), $url);
		}
		
		function getXMLUrl(){
			//echo 'getXMLUrl<br />';
			return '/home/blueghos/www/tv/cache/amazon/xml_cache/'.$this->terms.'_'.$this->cat.'.xml';
		}
		
		
		function generateXMLUrl(){
			//echo 'generateXMLUrl<br />';
			/** Amazon Subscription ID */
			$subID = '0S1VTTJWDR66QRK6Z982';

			/** Amazon Associates ID */
			$assID = 'blueghost-21';
			
			//$sIndex		= 'Blended';
			$sort		= $this->sort_var;//'salesrank';
			$condition 	= $this->condition;//'New';
			$group 		= $this->group;//'Medium,Offers';
			
			$this->terms = trim($this->terms);
			$this->terms = str_replace(" ", "+", $this->terms);
			if ($this->operation == 'ItemSearch'){
				$url = 'http://webservices.amazon.co.uk/onca/xml?Service=AWSECommerceService&SubscriptionId='
					. $subID
					. '&AssociateTag='
					. $assID
					. '&Operation=ItemSearch&Keywords='
					. $this->terms
					. '&SearchIndex='
					. $this->cat;
				if ($this->cat != "Blended"){
					$url	.= '&Sort='
							. $sort;
				}
				$url .= '&Condition='
					. $condition;
				if (isset($this->page)){
					$url	.=  '&ItemPage='
							. $page;
				}
				$url .= '&ResponseGroup='
					. $group;
			}else{
				$url = 'http://webservices.amazon.co.uk/onca/xml?Service=AWSECommerceService&SubscriptionId='
					. $subID
					. '&AssociateTag='
					. $assID
					. '&Operation=ItemLookup&ItemId='
					. $this->terms
					. '&ResponseGroup='
					. $group;
			}
			return $url;
		}
	
	}
?>