<?php

	require('includes/DB_Connection.php');
	require('includes/AmazonItem.php');
	
	$db_host = 'localhost';
   $db_user = 'amazon';
   $db_pwd  = 'DBPASSWORD';
   $dbase   = 'amazon';
   $Database = new DB_Connection();
	$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);	
	$Item = new AmazonItem('0563486295', $Database);	
	echo "<h4>Small Image </h4>\n";
	echo '<img src="includes/AmazonImage.php?ASIN='.$Item->ASIN.'&amp;size=small" />';
	echo "<h4>Medium Image </h4>\n";
	echo '<img src="includes/AmazonImage.php?ASIN='.$Item->ASIN.'&amp;size=medium" />';
	echo "<h4>Large Image </h4>\n";
	echo '<img src="includes/AmazonImage.php?ASIN='.$Item->ASIN.'&amp;size=large" />';
	$Database->DB_disconnect();
?>