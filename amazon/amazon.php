<?php
/** Amazon Subscription ID */
$subID = '0S1VTTJWDR66QRK6Z982';

/** Amazon ASIN Number */
if ($_GET['itemID'])
	$itemID = $_GET['itemID'];
else
	$itemID = 'B00025E0KY';

/** Rpoints link */
$rpoints = 'http://www.rpoints.com/x/?s=25';

/** Amazon link */
$amazon = 'http://www.amazon.co.uk/exec/obidos/ASIN/'.$itemID;

/** URL For XML search */
$url = 'http://webservices.amazon.co.uk/onca/xml?Service=AWSECommerceService&SubscriptionId='
		.$subID
		.'&Operation=ItemLookup&ItemId='
		.$itemID
		.'&ResponseGroup=Offers';
		
/** The XML Output */
$source = file_get_contents($url);

/* PARSE THE OUTPUT */

/** GET THE PRICE */
$price = eregi_replace ("<?xml", 								"<!--xml", 					$source);
$price = eregi_replace ("<FormattedPrice>", 					"<FormattedPrice>-->", 		$price);
$price = eregi_replace ("</FormattedPrice>", 					"<!--</FormattedPrice>", 	$price);
$price = eregi_replace ("</ItemLookupResponse>", 				"</ItemLookupResponse>-->",	$price);
$price = eregi_replace ("<!--([^-]*([^-]|-([^-]|-[^>])))*-->", 	"", 						$price);
$price = eregi_replace ("<",									"",							$price);
$price = eregi_replace ("\\?",									"",							$price);
$price = eregi_replace ("\\Â",									"",							$price);
if ($_GET['op'])
	echo '<h1>Price is <pre>'.$price.'</pre></h1>';

/** GET AVAILABILTY */
$avail = eregi_replace ("<?xml", 								"<!--xml", 					$source);
$avail = eregi_replace ("<Availability>", 						"<Availability>-->", 		$avail);
$avail = eregi_replace ("</Availability>", 						"<!--</Availability>", 		$avail);
$avail = eregi_replace ("</ItemLookupResponse>", 				"</ItemLookupResponse>-->",	$avail);
$avail = eregi_replace ("<!--([^-]*([^-]|-([^-]|-[^>])))*-->", 	"", 						$avail);
$avail = eregi_replace ("<",									"",							$avail);
$avail = eregi_replace ("\\?",									"",							$avail);

$available = false;
if (strcmp($avail, "This item is currently not available by this merchant") == 0){
	if ($_GET['op'])
		echo '<h1><emp>Not</emp> Available</h1>';
}else{
	if ($_GET['op'])
		echo '<h1><emp>Is</emp> Available</h1>';
	$available = true;
}
if (!$_GET['op']){
header('Content-type: text/xml', true);
echo '<?xml version="1.0" encoding="ISO-8859-1"?>';
?>

<rss version="2.0">
<channel>
  <title>BlueGhost Amazon Nokia 7600 Checker</title>
  <link>http://www.blueghost.co.uk/</link>
  <language>en</language>
  <copyright>Copyright 2004 BlueGhost.co.uk. Data Copyright Amazon.co.uk</copyright>
	<?php 
	 	if ($available){
			echo '
   <item>
     <title>A for '.$price.' -Details:'.$avail.'</title>
     <link>'.$amazon.'</link>
     <description>Click here for Amazon direct link</description>
   </item>
   
   <item>
     <title>Rpoints Amazon Link</title>
     <link>'.$rpoints.'</link>
     <description>Click here for Rpoints refferal link</description>
   </item>';
   		}else{
			echo '
   <item>
     <title>N/A-Details:'.$avail.'</title>
     <link>'.$amazon.'</link>
     <description>Click here for Amazon direct link</description>
   </item>';
   }   
   ?>
</channel>
</rss>
<?php
}
?>


