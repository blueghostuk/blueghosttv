<?php

include('includes.php');
require('../includes/DB_Connection.php');
require('../includes/XML_Parser.php');
require('../includes/AmazonSearch.php');
require('../includes/AmazonSearchResults.php');
require('../includes/AmazonItem.php');
require('../includes/AmazonSearchParser.php');
include('header.php');

if (!$_REQUEST['id']){
	die('No ID GIVEN');
	exit(0);
}
echo '<div class="item_page">';

$group = 'Large,EditorialReview';
$as = new AmazonSearch;
$res = new AmazonSearchResults;
$res->terms = $searchText;
$as->setTerms($_REQUEST['id'], 'item');
$as->group = $group;
$as->operation = 'ItemLookup';
$as->results = $res;
if ($as->checkResultsCache()){
	$as->getResults();
	$as->parseResultsToFile();
}
			
//$results = $as->returnResults();
	
//$url = $as->getXMLUrl();
$url = 'http://webservices.amazon.co.uk/onca/xml?Service=AWSECommerceService&SubscriptionId='
		. $subID
		. '&AssociateTag='
		. $assID
		. '&Operation=ItemLookup&ItemId='
		. $_REQUEST['id']
		. '&ResponseGroup='
		. $group;
	
$xml = simplexml_load_file($url);
$i = 0;
$item = $xml->Items->Item[$i];
$title = $item->ItemAttributes->Title;
$price = $item->OfferSummary->LowestNewPrice->FormattedPrice;
$low_price = $price;
$img_url = $item->MediumImage->URL;
//$img_w = $item->MediumImage->Width;
//$img_h = $item->MediumImage->Height;

$ama_price = (integer)$item->OfferSummary->LowestNewPrice->FormattedPrice;
$ama_price *=2;
$avail = "Not Available from Amazon Direct";
foreach ($item->Offers->Offer as $offer) {
	if ((string)$offer->Merchant->MerchantId == $amID){
   	if ($offer->OfferListing->Price->FormattedPrice){
   		$price = $offer->OfferListing->Price->FormattedPrice;
   		$ama_price = $offer->OfferListing->Price->Amount;
   		$avail = $offer->OfferListing->Availability;
   	}
   }
}
$list_price = (integer)$item->ItemAttributes->ListPrice->Amount;
if ($ama_price < $list_price)
	$saving =  ( 1 - ( $ama_price / $list_price ) ) * 100;

$imdb_title = eregi_replace ("\[([^-]*([^-]|-([^-]|-[^>])))\]", 	"", $title);
$imdb_title = eregi_replace ("\"", 	"", $imdb_title);
//src="'.$img_url.'"
echo'	<a id="search_href-'.$i.'" href="javascript:largeSearchImage('.$i.',\''.$item->LargeImage->URL.'\');;" title="Change Image Size">
			<img id="search_img-'.$i.'" class="item_logo" src="'.$item->MediumImage->URL.'"  />
		</a>
		<h2>'.$title.'</h2>';
echo'<h3><a href="/amazon/cart/add/'.$item->ASIN.'">Buy New for '
	. '<span title="Lowest New Price from all Sellers: '
	. $low_price
	. '">'
	. $price
	. '</span>'
	. '</a>';
if ($saving && ($saving !=100)){
			echo '&nbsp;-&nbsp;'
				. '<span title="Original Price : '
				. $item->ItemAttributes->ListPrice->FormattedPrice
				. '">Save '
				. round($saving, 1)
				. '% off the RRP Price!</span>';
}
echo '</h3>';
echo '<h4>'.$avail.'</h4>';
/* AMAZON PRODUCT DESC */
if ($item->EditorialReviews->EditorialReview[1]){
	echo '<div class="amazon_review"><h3>'. $item->EditorialReviews->EditorialReview[1]->Source
		.' (<a class="review_show_hide" id="review-href--1"href="javascript:showHideReview(-1);">+</a>)</h3>';
	echo '<div class="amazon_review_text" style="display: none" id="review--1>';
	echo $item->EditorialReviews->EditorialReview[1]->Content;
	echo '</div></div>';
}
/* AMAZON PRODUCT REVIEW */
if ($item->EditorialReviews->EditorialReview[0]->Source){
	echo '<div class="amazon_review"><h3>'. $item->EditorialReviews->EditorialReview[0]->Source
		.' <a class="review_show_hide" id="review-href--2"href="javascript:showHideReview(-2);">+</a></h3>';
	echo '<div class="amazon_review_text" style="display: none" id="review--2">';
	echo $item->EditorialReviews->EditorialReview[0]->Content;
	echo '</div></div>';
}

//REVIEWS
$rev = $item->CustomerReviews;
$rev_count = (integer) $rev->TotalReviews;
$rev_avg = $rev->AverageRating;

echo '<h3>'.$rev_count.' Reviews - Average Rating: '.$rev_avg.'</h3>';

$c = -1;
if ($rev_count > 0){
	foreach ($rev->Review as $review) {
		$c++;
		//for ($c = 0; ($c < $rev_count) && ($c <=4) ; $c++){
		//$review = $rev->Review[$c];
		echo '<div class="amazon_review_title">'.$review->Summary.' ';
		for ($s = 0; $s < (integer)$review->Rating; $s++)
			echo '*';
		echo ' (<a class="review_show_hide" id="review-href-'.$c.'" href="javascript:showHideReview('.$c.');">+</a>)</div> ';
		echo '<div class="amazon_review_text" style="display: none" id="review-'.$c.'">'.$review->Content.'</div>';
	}
}
echo '</div>';
include('footer.php');
?>
