<?php

// called from website to search for things

require_once __DIR__ . "/siteFunctions.php";

//get the q parameter from URL
if ( ! isset($_GET['q']) ) {
	exit();
}

$q = trim(strtolower($_GET["q"]));
if ( strlen($q) < 2 ) {
	exit();
}

// split query into multiple words
$words = explode(" ", $q);
$words = array_map("trim", $words);

$results = [];

// iterate all html files
$files = glob(realpath(__DIR__."/../cache/")."/*.html");
foreach ( $files as $file ) {
	// get text of file
	$parts = explode("/", $file);
	$filename = $parts[sizeof($parts)-1];
	$html = file_get_contents($file);
	$parsed = parseHtml($html);
	// make sure all words are present
	foreach ( $words as $needle ) {
		if ( ! stristr($parsed['text'], $needle) ) {
			continue 2;
		}
	}
	// store matches
	$results[] = [
		"file" => $filename,
		"title" => $parsed['title'],
		"icon" => $parsed['icon'],
		"text" => $parsed['text'],
	];
}

// build pretty html search results
$html = '';
foreach ( $results as $result ) {
	$html .= '<div class="searchresultsingle" onclick="document.location.href=\''.$result['file'].'\';">';
	$html .= '<span class="icon">'.$result['icon']."</span>".$result['title'];
	$html .= '<div class="searchresultinfo">'.findOccurences($result['text'], $words).'</div>';
	$html .= '</div>';
}

// nothing found
if ( sizeof($results) == 0 ) {
	$html = '<p><i>No Results</i></p>';
}

echo $html;

