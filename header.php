<?php
	if ($_SERVER['HTTP_HOST'] != 'www.blueghosttv.co.uk' && $_SERVER['HTTP_HOST'] != 'blueghosttv.co.uk'){
		$me = $_SERVER['PHP_SELF'];
		$Apathweb = explode("/", $me);
		$myFileName = array_pop($Apathweb); 
		header("Location: http://www.blueghosttv.co.uk/".$myFileName."?".$_SERVER['QUERY_STRING']);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php
	if (!isset($pg_title)){
		$pg_title = "";
	}else{
		$pg_title = " : ".$pg_title;
	}
?>
<title>BluegGhost PG<?php echo $pg_title;?></title>
<link rel="stylesheet" type="text/css" href="/styles/style.css">
<link rel="icon" href="/favicon.ico" />
<link rel="shortcut icon" href="/favicon.ico" />
<?php
	if (isset($xml_link)){
?>
<link rel="alternate" type="application/rss+xml" title="<?php echo $xml_title;?>" href="<?php echo $xml_link;?>" />
<?php
	}
?>
<link title="BlueGhost TV" rel="search" type="application/opensearchdescription+xml" href="http://www.blueghosttv.co.uk/OpenSearch.xml" />
<script type="text/javascript">
function goToTime(){
		loadLinks();
		var d = new Date();
		var h = d.getHours();
		var href = document.location.href;
		if (href == 'http://www.blueghosttv.co.uk/'){
			document.location.href = document.location.href + '#hour_'+h;
			return;
		}
		if (href.indexOf('http://www.blueghosttv.co.uk/index.php') != -1){
			document.location.href = document.location.href + '#hour_'+h;
			return;
		}
		if (href.indexOf('http://www.blueghosttv.co.uk/index_view.php') != -1){
			document.location.href = document.location.href + '#hour_'+h;
			return;
		}
		if (href.indexOf('http://www.blueghosttv.co.uk/index_side_view.php') != -1){
			document.location.href = document.location.href + '#hour_'+h;
			return;
		}
		if (href.indexOf('http://www.blueghosttv.co.uk/channel/') != -1){
			document.location.href = document.location.href + '#hour_'+h;
			return;
		}
	}
	</script>
<script src="/javascript/script.js" type="text/javascript">
</script>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-67957-3";
urchinTracker();
</script>
</head>

<body onload="javascript:goToTime();">
<div id="header">
<a href="/" title="Home Page">BlueGhost P(rogram) G(uide)</a> : <a href="http://www.blueghosttv.co.uk/pc/">Also BlueGhost PC TV Guide
</a></div>
<div class="menu">
| VIEW:&nbsp;
<select onchange="goToView(this.options[this.selectedIndex].value);">
	<?php
		if (strpos($_SERVER['PHP_SELF'], 'index_view.php') !== false){
			echo '<option value="/">SINGLE CHANNEL</option>';
			echo '<option value="/index_view.php" selected="true">TOP MULTI-VIEW</option>';
			echo '<option value="/index_side_view.php">SIDE MULTI-VIEW</option>';
			echo '<option value="/nn.php" title="Now and Next">Now &amp; Next</option>';
		}elseif (strpos($_SERVER['PHP_SELF'], 'index_side_view.php') !== false){
			echo '<option value="/">SINGLE CHANNEL</option>';
			echo '<option value="/index_view.php">TOP MULTI-VIEW</option>';
			echo '<option value="/index_side_view.php" selected="true">SIDE MULTI-VIEW</option>';
			echo '<option value="/nn.php" title="Now and Next">Now &amp; Next</option>';
		}elseif (strpos($_SERVER['PHP_SELF'], 'nn.php') !== false){
			echo '<option value="/">SINGLE CHANNEL</option>';
			echo '<option value="/index_view.php">TOP MULTI-VIEW</option>';
			echo '<option value="/index_side_view.php" selected="true">SIDE MULTI-VIEW</option>';
			echo '<option value="/nn.php" title="Now and Next" selected="true">Now &amp; Next</option>';
		}else{
			echo '<option value="-1" selected="true">SELECT VIEW</option>';
			echo '<option value="/">SINGLE CHANNEL</option>';
			echo '<option value="/index_view.php">TOP MULTI-VIEW</option>';
			echo '<option value="/index_side_view.php">SIDE MULTI-VIEW</option>';
			echo '<option value="/nn.php" title="Now and Next">Now &amp; Next</option>';
		}
	?>
  </select>
 | DAY:&nbsp;
 <select onchange="goToDay(this.options[this.selectedIndex].value);">
	<?php
	if ($_REQUEST['channel']){
		$addon = '?channel='.$_REQUEST['channel'].'&amp;';
		$cn = $_REQUEST['channel'];
	}else{
		$addon = '?';
		$cn = 1;
	}
	if (strpos($_SERVER['PHP_SELF'], 'index.php') !== false){
		$url = '/channel/'.$cn.'/';
	}else{
		$url = $_SERVER['PHP_SELF'].$addon.'day=';
	}
	if (!isset($_REQUEST['day']))
		echo '<option value="'.$url.'0" selected="true">TODAY</option>';
	else
		echo '<option value="'.$url.'0">TODAY</option>';
	
	for ($i=1; $i <= 7; $i++){
		if ($_REQUEST['day'] == $i)
			$sel = 'selected="true"';
		$dd = time() + (24*60*60*$i);
		echo'<option value="'.$url.$i.'" '.$sel.'>'.date("l jS", $dd).'</option>';
		$sel = '';
	}
	?>
	<option value="-1" class="selection_dark">Select a Previous Day</option>
	<?php
	if ($_REQUEST['channel']){
		$addon = '?channel='.$_REQUEST['channel'].'&amp;';
		$cn = $_REQUEST['channel'];
	}else{
		$addon = '?';
		$cn = 1;
	}
	if (strpos($_SERVER['PHP_SELF'], 'index.php') !== false){
		$url = '/channel/'.$cn.'/';
	}else{
		$url = $_SERVER['PHP_SELF'].$addon.'day=';
	}	
	for ($i=-1; $i >= -21; $i--){
		if ($_REQUEST['day'] == $i)
			$sel = 'selected="true"';
		$dd = time() + (24*60*60*$i);
		echo'<option value="'.$url.$i.'" '.$sel.'>'.date("l jS M", $dd).'</option>';
		$sel = '';
	}
	?>
  </select>
	| <span id="status_text"></span>
	<?php
		if ( ( $_COOKIE['cartID'] && !$_COOKIE['cartClear'] ) || $_GET['op'] == "add"){
			echo' | <a href="/amazon/cart/view/">View Cart</a> |';
			if ($debug)
				echo ' - '.$_COOKIE['cartID'];
		}else{
			//echo'<a href="cart.php?op=view">Cart is Empty</a>';
		}
	?>
</div>
<div class="menu">
|&nbsp;Search This Page: <input id="sBoxText"type="text" size="20" maxlength="100" onkeyup="javascript:doLocalSearch(this.value);" onblur="javascript:redo(this);" onfocus="javascript:clearS(this);" value="Enter Search Query" /> <a href="javascript:newS('sBoxText');">Clear Search</a>&nbsp;|&nbsp;Search All Programs: <input id="sABoxText"type="text" size="20" maxlength="100" onkeyup="javascript:doAllSearch(this.value);" onblur="javascript:redo(this);" onfocus="javascript:clearS(this);" value="Enter Search Query" /> <a href="javascript:newS('sABoxText');">Clear Search</a>&nbsp;|&nbsp;<a href="/advanced_search.php" title="Search the past and more">Advanced Search</a>&nbsp;|
<div class="sResults" id="searchResults"></div>
</div>
<div id="content">