<?php
echo $_SERVER['HTTP_HOST'];
/*
	if ($_SERVER['HTTP_HOST'] != 'www.blueghosttv.co.uk' || $_SERVER['HTTP_HOST'] != 'blueghosttv.co.uk'){
		$me = $_SERVER['PHP_SELF'];
		$Apathweb = explode("/", $me);
		$myFileName = array_pop($Apathweb); 
		header("Location: http://www.blueghosttv.co.uk/".$myFileName."?".$_SERVER['QUERY_STRING']);
	}*/
?>