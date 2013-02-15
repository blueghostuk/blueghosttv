<?php
require('DB_Connection.php');
//header("Content-Type: image/jpeg");
$db_host = 'localhost';
$db_user = 'amazon';
$db_pwd  = 'DBPASSWORD';
$dbase   = 'amazon';
$Database = new DB_Connection();
$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);	
$sql		= 'SELECT `'.$_REQUEST["size"].'` FROM `image_cache` WHERE `ASIN` = "'.$_REQUEST["ASIN"].'" LIMIT 0, 30';
$search	= $Database->DB_search($sql);
$img		= $Database->DB_row($search);
$num		= $Database->DB_num_results($search);
if ($num == 0){
	$group = 'Images';
	$url = 'http://webservices.amazon.co.uk/onca/xml?Service=AWSECommerceService&SubscriptionId='
		. $subID
		. '&AssociateTag='
		. $assID
		. '&Operation=ItemLookup&ItemId='
		. $_REQUEST['asin']
		. '&ResponseGroup='
		. $group;
	$xml = simplexml_load_file($url);
	$i = 0;
	$item = $xml->Items->Item[$i];
	$small 	= $item->SmallImage->URL;
	$medium 	= $item->MediumImage->URL;
	$large	= $item->LargeImage->URL;
	$smallData = addslashes(fread(fopen($small, "r"), filesize($small)));
	$mediumData = addslashes(fread(fopen($medium, "r"), filesize($medium)));
	$largeData = addslashes(fread(fopen($large, "r"), filesize($large)));
	$sql = 'INSERT INTO `image_cache` ( `ASIN` , `update` , `small` , `medium` , `large` ) VALUES ( '.$_REQUEST['asin'].', UNIX_TIMESTAMP(), '.$smallData.', '.$mediumData.', '.$largeData.' )';
	$insert	= $Database->DB_search($sql);
	$sql		= 'SELECT `'.$_GET["size"].'` FROM `image_cache` WHERE `ASIN` = "'.$_GET["ASIN"].'" LIMIT 0, 30';
	$search	= $Database->DB_search($sql);
	$img		= $Database->DB_row($search);
}
if ($img[0] == null)
	echo 'null';
else
	echo $img[0];
$Database->DB_disconnect();
?>