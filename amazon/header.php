<?php
session_start();

//fixes mysterious Â character
header("Content-type: text/html; charset=utf-8");

if ($_COOKIE['cartClear'] == 'yes'){ /* Clear cart now merged/deleted */
	$debug_text .= "Cleared Cart in header.php\n";
	$url = 'http://webservices.amazon.co.uk/onca/xml?Service=AWSECommerceService&SubscriptionId='
					. $subID
					. '&AssociateTag='
					. $assID
					. '&Operation=CartClear&CartId='
					. $_COOKIE['cartID']
					. '&HMAC='
					. $_COOKIE['cartHMAC'];
	setcookie('cartID', $_COOKIE['cartID'], time()-3600*24*90, '/'); 
	setcookie('cartHMAC', $_COOKIE['cartHMAC'], time()-3600*24*90, '/');
	setcookie('cartURL', $_COOKIE['cartURL'], time()-3600*24*90, '/');
	setcookie('cartClear', 'yes' , time()-3600*24*90, '/');
}
if ($_COOKIE['cartClear'] == 'no'){ /* On next load set to clear cart */
	$debug_text .= "COOKIE cartClear set to yes\n";
	setcookie('cartClear', 'yes' , time()+3600*24*90, '/');
}
?>
<!DOCTYPE XHTML PUBLIC "-//W3C//DTD HTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/xhtml1-loose.dtd">

<html>
<head>
<link rel="stylesheet" type="text/css" href="/amazon/style.css" />
<link rel="stylesheet" type="text/css" href="/styles/style.css">
<link rel="Shortcut Icon" href="/amazon/favicon.ico" type="image/x-icon" />
<script type="text/javascript">

		function goToTime(){
		loadLinks();
		var d = new Date();
		var h = d.getHours();
		var href = document.location.href;
		if (href == 'http://tv.blueghost.co.uk/'){
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
		if (href.indexOf('http://www.blueghosttv.co.uk/index_bleb.php') != -1){
			document.location.href = document.location.href + '#hour_'+h;
			return;
		}
	}
	
	function showStatus(txt){
		var s = document.getElementById('status_text');
		s.innerHTML = txt;
	}
	
	var links = [];
	var link_titles = [];
	var as = [];
	
	function loadLinks(){
		var l = document.getElementsByTagName('a');
		for (var i = 0; i < l.length; i++){
			links.push(l[i].href);
			link_titles.push(l[i].innerHTML);
			as.push(l[i]);
		}
	}
	
	function doLocalSearch(text){
		var r_box = document.getElementById('searchResults');
		if (text.length >3){
			var results = [];
			r_box.className = 'sResults';
			r_box.innerHTML = '';
			for (var i = 0; i < link_titles.length; i++){
				if (link_titles[i].toLowerCase().indexOf(text) !=-1){
					var prev = as[i].previousSibling; //br
					if (prev != null)
						prev = prev.previousSibling; //strong - time
					var td = as[i].parentNode;
					var chan = td.title;
					chan = chan.substring(0,chan.indexOf(':'));
					//alert(prev);
					var result = document.createElement('a');
					result.href= links[i];
					if (prev != null)
						result.innerHTML = link_titles[i] + ' ('+chan + prev.innerHTML+')';
					else
						result.innerHTML = link_titles[i];
					result.title = as[i].title;
					results.push(result);
				}
			}
			if (results.length > 0){
				for(var i = 0; i < results.length; i++){
					r_box.innerHTML += '<a href="'+results[i].href+'">'+results[i].innerHTML+'</a> - ' + results[i].title + '<br />';
				}
			}else{
				r_box.innerHTML = 'No Results Found';
			}
		}else{
			r_box.className = 'sResults';
			r_box.innerHTML = 'Need more than 3 characters';
		}
	}
	
	function createXML(){
		if (window.XMLHttpRequest) {
        	//req = new XMLHttpRequest();
			return new XMLHttpRequest();
    		// branch for IE/Windows ActiveX version
    	} else if (window.ActiveXObject) {
        	//req = new ActiveXObject("Microsoft.XMLHTTP");
			return new ActiveXObject("Microsoft.XMLHTTP");
    	}
	}
	
	function doXMLHTTPRequest(f_name, url){
		var request = createXML();
		request.open("GET", url	, true);
		request.onreadystatechange = function() {
			if (request.readyState == 4) {
				if (request.status == 200) {
					f_name(request);
				}
			}
		};
		request.send(null);
	}
	
	function setStyle(box, style){
		box = document.getElementById(box);
		box.className = style;
	}
	
	function doSeriesSearch(prog){
		var r_box = document.getElementById('seriesResults');
		r_box.className = 'sResults';
		r_box.innerHTML = 'Searching...';
		doXMLHTTPRequest(getSeriesResults, 'http://www.blueghosttv.co.uk/ajax_series.php?query='+prog);
	}
	
	function getSeriesResults(response){
		var r_box = document.getElementById('seriesResults');
		r_box.innerHTML = response.responseText
	}
	
	function getSearchResults(response){
		var r_box = document.getElementById('searchResults');
		r_box.innerHTML = response.responseText
	}
	
	function doAllSearch(text){
		var r_box = document.getElementById('searchResults');
		if (text.length >3){
			r_box.className = 'sResults';
			r_box.innerHTML = 'Searching...';
			doXMLHTTPRequest(getSearchResults, 'http://www.blueghosttv.co.uk/ajax_search.php?query='+text);
		}else{
			r_box.className = 'sResults';
			r_box.innerHTML = 'Need more than 3 characters';
		}
	}
	
	function clearS(obj){
		if (obj.value == "Enter Search Query")
			obj.value= "";
	}
		
	function redo(obj){
		if (obj.value == ""){
			obj.value= "Enter Search Query";
			var sb = document.getElementById('searchResults');
			sb.innerHTML = '';
			sb.className = 'hidden';
		}
	}
	
	function newS(box){
		var sbt = document.getElementById(box);
		var sb = document.getElementById('searchResults');
		sb.innerHTML = '';
		sb.className = 'hidden';
		sbt.value= "Enter Search Query";
	}

	function showHideReview(id){
		var rev  = document.getElementById('review-'+id);
		var link = document.getElementById('review-href-'+id);
		if (rev.style.display == "none"){
			 rev.style.display = "";
			 link.innerHTML = "-";
		}else{
			 rev.style.display = "none";
			 link.innerHTML = "+";
		}
	}
	
	function largeImage(url){
		var img  = document.getElementById('review_img');
		var hr  = document.getElementById('img_href');
		hr.href = 'javascript:largeImage(\''+img.src+'\');';
		hr.title = 'Show Small Image';
		img.src = url;
		
	}
	
	function largeSearchImage(id,url){
		var img  = document.getElementById('search_img-'+id);
		var hr  = document.getElementById('search_href-'+id);
		//var link  = document.getElementById('img_loading');
		//link.innerHTML = 'Loading';
		hr.href = 'javascript:largeSearchImage('+id+',\''+img.src+'\');';
		hr.title = 'Show Small Image';
		img.src = url;	
		//link.innerHTML = '';
	}
	function doSearch(){
		var sTerm	= document.getElementById('search');
		var sCat	= document.getElementById('category');
		if ( (sTerm.value == null) || (sTerm.value == '') ){
			alert('No search term specified');
			return false;
		}else{
			document.location.href = 'http://www.blueghosttv.co.uk/amazon/search.php?search=' + sTerm.value + '&category=' + sCat.value;
		}
	}
</script>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-67957-3";
urchinTracker();
</script>
</head>
<body>
<div id="header">
<a href="/" title="Home Page">BlueGhost P(rogram) G(uide)</a></div>

<div class="menu">
| SELECT VIEW : <select onchange="goToView(this.options[this.selectedIndex].value);">
<option value="-1" selected="true">SELECT VIEW</option>
<option value="/">SINGLE CHANNEL</option>
<option value="/index_view.php">TOP MULTI-VIEW</option>
<option value="/index_side_view.php">SIDE MULTI-VIEW</option>
<option value="/nn.php" title="Now and Next">Now &amp; Next</option>
	</select>
&nbsp;|&nbsp;Search Amazon:&nbsp;
<form style="display: inline;" onsubmit="javascript:doSearch();return false;">
		<input type="text" name="search" id="search" value="<?php echo $_REQUEST['search'];?>">
		<select id="category" name="category">
			<option value="Blended">All</option>
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
			<!-- <option value="Blended">Blended</option> -->
		</select>
		<input type="button" value="Search" onclick="javascript:doSearch();">
	</form>&nbsp;|&nbsp;
	<?php
		if ( ( $_COOKIE['cartID'] && !$_COOKIE['cartClear'] ) || $_GET['op'] == "add"){
			echo'<a href="/amazon/cart/view/">View Cart</a>';
			if ($debug)
				echo ' - '.$_COOKIE['cartID'];
		}else{
			echo'<a href="/amazon/cart/view/">Cart is Empty</a>';
		}
	?>
</div>