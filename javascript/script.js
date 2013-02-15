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
		if (text.length >1){
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
			r_box.innerHTML = 'Need more than 1 character';
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
	
	function setElementStyle(element, style)
	{
		element.className = style;	
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
	
	function doAmazonSearch(){
		var sTerm	= document.getElementById('search');
		var sCat	= document.getElementById('category');
		if ( (sTerm.value == null) || (sTerm.value == '') ){
			alert('No search term specified');
		}else{
			document.location.href = 'http://www.blueghosttv.co.uk/amazon/search.php?search=' + sTerm.value + '&category=' + sCat.value;
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
		//sb.className = 'hidden';
		sbt.value= "Enter Search Query";
	}
	
	function goToView(page){
		if (page != -1)
			window.location = 'http://www.blueghosttv.co.uk'+page;
	}
	
	function goToDay(day){
		if (day != -1)
			window.location = 'http://www.blueghosttv.co.uk'+day;
	}
	
	function addChannel(){
		var sel = document.getElementById('chanSel');
		var box = document.getElementById('rss_feed_box');
		if (box.value == "http://www.blueghosttv.co.uk/feeds/rss/nnp/"){
			box.value += sel.options[sel.selectedIndex].value;
		}else{
			box.value += "+"+sel.options[sel.selectedIndex].value;
		}
	}
	
	function addGoogleChannel(){
		var sel = document.getElementById('googleChanSel');
		var box = document.getElementById('google_feed_box');
		if (box.value.length == 0){
			box.value += sel.options[sel.selectedIndex].value;
		}else{
			box.value += "+"+sel.options[sel.selectedIndex].value;
		}
	}
	
	function clearChannels(box, value){
		var box = document.getElementById(box);
		box.value = value;
	}
	
	function HideMenu()
	{
		var cl = document.getElementById('channelList');
		cl.className = "hidden";
		var sc = document.getElementById('showChannels');
		sc.className = "chanListSmall";
	}
	
	function ShowMenu()
	{
		var sc = document.getElementById('showChannels');
		sc.className = "hidden";
		var cl = document.getElementById('channelList');
		cl.className = "chanList";	
	}