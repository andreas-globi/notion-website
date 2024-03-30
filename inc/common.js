
cleanupDocument();
registerHomeButton();
registerSearch();

function showSearchResult(str) {
	var searchResultDiv = document.querySelector("#globalheader .globalsearchresults");
	searchResultDiv.style.display = "block";
	// don't search for empty strings
	if ( str.trim().length == 0 ) {
		searchResultDiv.innerHTML="";
		searchResultDiv.style.display = "none";
		return;
	}
	// build request to ajaxsearch.php to get results
	if (window.XMLHttpRequest) {
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {  // code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function() {
		if (this.readyState==4 && this.status==200) {
			// show the results
			if ( this.responseText.trim().length == 0 ) {
				searchResultDiv.innerHTML='<p><i>No Results</i></p>';
				return;
			}
			searchResultDiv.innerHTML=this.responseText;
		}
	};
	// send request to server
	xmlhttp.open("GET",window?.globalvars?.pathfromroot+"/inc/ajaxsearch.php?q="+str,true);
	xmlhttp.send();
}

function cleanupDocument() {
	// close details tags
	var elms = document.querySelectorAll("details");
	var i;
	for (i=0; i<elms.length; i++) {
		elms[i].open = false;
	}
	// make external links open new window
	var links = document.links;
	for (i = 0, linksLength = links.length; i < linksLength; i++) {
		if (links[i].hostname != window.location.hostname) {
			links[i].target = '_blank';
		}
	}
}

function registerHomeButton() {
	// click logo to go home
	document.querySelector("#globalheader .globallogo").addEventListener("click", function(e){
		e.preventDefault();
		var url = window?.globalvars?.pathfromroot;
		if ( url == "" ) {
			url = "/";
		}
		document.location.href = url;
	});
}

function registerSearch() {
	var searchResultDiv = document.querySelector("#globalheader .globalsearchresults");
	var inputField = document.getElementById("globalsearchinputfield");
	// hide if disabled
	if ( ! window?.globalvars?.enablesearch ) {
		document.querySelector("#globalheader .globalsearchwrapper").style.display = "none";
		return;
	}
	// trigger on search input changes
	inputField.addEventListener("input", function(){
		if ( this.value.trim() == "" ) {
			searchResultDiv.innerHTML = "";
			searchResultDiv.style.display = "none";
			return;
		}
		showSearchResult(this.value);
	});
	// click on body closes search
	document.addEventListener("click", function(e){
		var div = document.getElementById("globalheader");
		if ( ! div.contains(e.target) ) {
			searchResultDiv.style.display = "none";
			searchResultDiv.innerHTML = "";
			inputField.value = "";
		}
	});
	// escape key closes search
	document.addEventListener("keyup", function(e){
		if ( e.keyCode == 27 ) {
			searchResultDiv.style.display = "none";
			searchResultDiv.innerHTML = "";
			inputField.value = "";
			inputField.blur();
		}
	});
}
