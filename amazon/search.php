<?php

include('includes.php');
include('header.php');
require('../includes/DB_Connection.php');
require('../includes/XML_Parser.php');
require('../includes/AmazonSearch.php');
require('../includes/AmazonSearchResults.php');
require('../includes/AmazonItem.php');
require('../includes/AmazonSearchParser.php');

//$Database = new DB_Connection();
//$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);	

echo '<div class="search_results">';
if ($_GET['page']){
	$page = (integer)$_GET['page'];
	//echo 'PAGE = '.$page;
	//$form_val_plus = '<input type="hidden" name="page" value="'.($page+1).'">';
	//$form_val_minus = '<input type="hidden" name="page" value="'.($page-1).'">';
}else{
	$page = 1;
	//echo 'PAGE = SET TO 1, '.$page;
	//$form_val_plus = '<input type="hidden" name="page" value="2">';
	//$form_val_minus = '<input type="hidden" name="page" value="2">';
}

if ($_REQUEST['search'] || $_REQUEST['searching']){
	
	//session details
	if ($_REQUEST['category']){
   		$_SESSION['category'] = $_REQUEST['category'];
		//echo 'CATEGORY SET TO= '.$_SESSION['category'];
		$sIndex = $_REQUEST['category'];//'Electronics';
	}else
		$sIndex = $_SESSION['category'];
		
	if ($_REQUEST['search']){
		$_SESSION['search'] = $_REQUEST['search'];
		//echo 'SEARCH SET TO= '.$_SESSION['search'];
		$searchText = $_REQUEST['search'];
	}else
		$searchText =$_SESSION['search'];
	//$_SESSION['page'] = $page;
	//echo 'PAGE = '.$_SESSION['page'];
	//$page = $_REQUEST['page'];
	
	$searchText = eregi_replace (" ", ",", $searchText);
	
	$num	= -1;
	$new	= true;

	$as = new AmazonSearch;
	$res = new AmazonSearchResults;
	$res->terms = $searchText;
	$as->setTerms($searchText, $sIndex);
	$as->results = $res;
	if (isset($_REQUEST['page'])){
		$page = $_REQUEST['page'];
		$as->page = $page;
	}
	//echo "<p>Amazon URL:".$as->generateXMLUrl()."</p>\n";
	if ($as->checkResultsCache()){
		$as->getResults();
		$as->parseResultsToFile();
	}
			
	//$results = $as->returnResults();
	
	//$url = $as->getXMLUrl();
	$url = $as->generateXMLUrl();
	$xml = simplexml_load_file($url);
	$total = $xml->Items->TotalResults;
	
	
	echo '<h1>Amazon Search Results :</h1>';
	echo "\n";
	
	if (!$total || $total == 0){
		echo 'No Results Found';
		echo "\n";
		$range = 0;
		$next = false;
		$past = false;
	}else{
		if ($total < 10){
			echo 'Results: 1 to '.$total.' of '.$total.'';
			echo "\n";
			$range = (integer)$total;
			$next = false;
		}else{
			if (($page * 10) > $total){
				echo 'Results: '.((($page-1)*10)+1).' to '.$total.' of '.$total.'';
				echo "\n";
				$range = ( $total - (($page-1)*10) );
				$next = false;
				$past = true;
			}else{
				echo 'Results: '.((($page-1)*10)+1).' to '.((($page-1)*10)+10).' of '.$total.'';
				echo "\n";
				$range = 10;	
				$next = true;
				$past = true;
			}
		}
	}
	if ($page == 1)
		$past = false;
	if ($past){
		echo '( <a href="search.php?searching=true&amp;page='.($page-1).'">Previous Page</a> )';
		echo "\n";
	}
	if ($next){
		echo '( <a href="search.php?searching=true&amp;page='.($page+1).'">Next Page</a> )';
		echo "\n";
	}
	
		$i = -1;
		foreach ($xml->Items->Item as $item) {
			$i++;
			//for ($i = 0; $i < $range; $i++){
			//$item = $xml->Items->Item[$i];
			$title = $item->ItemAttributes->Title;
			$price = 'Best New Price: '.$item->OfferSummary->LowestNewPrice->FormattedPrice;
			$low_price = $price;
			//$used_price = $item->OfferSummary->LowestUsedPrice->FormattedPrice;
			$img_url = 'http://tv.blueghost.co.uk/cache/amazon/image_cache/'.$item->ASIN.'.jpg';//$item->SmallImage->URL;
			$img_path = '/home/blueghos/www/tv/cache/amazon/image_cache/'.$item->ASIN.'.jpg';
			if (!file_exists($img_path))
				$img_url = $item->SmallImage->URL;
			//$img_w = $item->SmallImage->Width;
			//$img_h = $item->SmallImage->Height;
			//$offers = (integer)$item->Offers->TotalOffers;
			//for ($o = 0; $o < $offers; $o++){
			$ama_price = (integer)$item->OfferSummary->LowestNewPrice->FormattedPrice;
			$ama_price *=2;
			foreach ($item->Offers->Offer as $offer) {
   				if ((string)$offer->Merchant->MerchantId == $amID){
   					if ($offer->OfferListing->Price->FormattedPrice){
   						$price 		= 'Amazon Price : '.$offer->OfferListing->Price->FormattedPrice;
   						$ama_price 	= $offer->OfferListing->Price->Amount;
						$avail 		= $offer->OfferListing->Availability;
   					}
   				}
			}
			$list_price = (integer)$item->ItemAttributes->ListPrice->Amount;
			if ($ama_price < $list_price)
				$saving =  ( 1 - ( $ama_price / $list_price ) ) * 100;
			echo '<h4><a id="search_href-'.$i.'" href="javascript:largeSearchImage('.$i.',\''.$item->MediumImage->URL.'\');" title="Change Image Size">'
				. '<img id="search_img-'.$i.'" class="item_logo" src="'.$img_url.'" />'
				. '</a>'
				. '<a title="View more details" class="search_item" href="/amazon/item/'.$item->ASIN.'">'
				. $title
				. '</a>'
				. '&nbsp;-&nbsp;'
				. '<span title="Lowest New Price from all Sellers: '
				. $low_price
				. '">'
				. $price
				. '</span>'
				. '&nbsp;-&nbsp;'
				. '<a href="cart.php?op=add&amp;id='.$item->ASIN.'">'
				. 'Add To Cart'
				. '</a>';
			echo "\n";
			if ($saving && ($saving !=100)){
				echo '&nbsp;-&nbsp;'
					. '<span title="Original Price : '
					. $item->ItemAttributes->ListPrice->FormattedPrice
					. '">Save '
					. round($saving, 1)
					. '% off the RRP Price!</span>';
				echo "\n";
			}
			echo '</h4>';
			echo "\n";
		}
}else{
	?>
	<h1>Amazon.co.uk Search</h1>
	<form action="search.php" method="post">
		<input type="text" name="search">
		<select name="category">
			<!-- <option value="Blended">Blended</option> -->
			<option value="Books">Books</option>
			<option value="Classical">Classical</option>
			<option value="DVD">DVD</option>
			<option value="Electronics">Electronics</option>
			<option value="HealthPersonalCare">Health/Personal Care</option>
			<option value="HomeGarden">Home/Garden</option>
			<option value="Kitchen">Kitchen</option>
			<option value="Music">Music</option>
			<option value="MusicTracks">Music Tracks</option>
			<option value="OutdoorLiving">Outdoor Living</option>
			<option value="Software">Software</option>
			<option value="SoftwareVideoGames">Software Video Games</option>
			<option value="Toys">Toys</option>
			<option value="VHS">VHS</option>
			<option value="Video">Video</option>
			<option value="VideoGames">Video Games</option>
		</select>
		<input type="submit" value="Search">
	</form>
	<?
}
echo '</div>';
include('footer.php');
?>
