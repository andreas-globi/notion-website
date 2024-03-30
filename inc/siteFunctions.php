<?php

// parse a Notion HTML export page and get text version, title, icon, etc
function parseHtml($html) {
	// split into header and footer
	$parts = explode("</head>", $html);
	$head = $parts[0].'</head>';
	$body = $parts[1];

	// get the plain text version
	preg_match("/<article[^>]*>(.*?)<\/article>/ism", $body, $matches);
	$text = $matches[1]??$body;
	$text = str_ireplace('</p>', ' </p> ', $text);
	$text = str_ireplace('</a>', ' </a> ', $text);
	$text = str_ireplace('</div>', ' </div> ', $text);
	$text = str_ireplace('</th>', ' </th> ', $text);
	$text = str_ireplace('</td>', ' </td> ', $text);
	$text = str_ireplace('</figure>', ' </figure> ', $text);
	$text = preg_replace("/<img[^>]*>/ism", " <img> ", $text);
	$text = strip_tags($text);
	$text = preg_replace("/[^A-Za-z0-9\.\,\s\-]/ism", " ", $text);
	$text = trim(preg_replace("/\s{2,}/ism", " ", $text));

	// calculate a description
	$description = preg_replace("/[^A-Za-z0-9\.\,\s\-]/ism", "", substr($text, 0, 200));
	while ( strlen($description) > 0 && substr($description, -1) != " " ) {
		$description = substr($description, 0, strlen($description)-1);
	}
	$description = trim($description) . " ...";

	// get the title and image
	preg_match("/<title>(.*?)<\/title>/ism", $head, $matches);
	$title = trim($matches[1]??"Unknown Document");

	// get the first image
	preg_match("/<img[^.]*src=\"(.*?)\"/ism", $html, $matches);
	$image = isset($matches[1])?($matches[1]):("");

	// get the header icon
	$icon = "🗏";
	preg_match("/<header>.*?<span class=\"icon\">(.*?)<\/span>.*?<\/header>/ism", $body, $matches);
	if ( is_array($matches) && isset($matches[1]) ) {
		$icon = $matches[1];
	}

	// return the parsed data
	return [
		'title' => $title,
		'text' => $text,
		'description' => $description,
		'image' => $image,
		'icon' => $icon,
	];
}

// find all occurences of words in text and return a formatted string for search results
function findOccurences($text, $words) {
	$before = 75;
	$after = 75;
	// find all occurences of the words in the text
	$occurences = [];
	foreach ( $words as $needle ) {
		if ( empty(trim($needle)) ) continue;
		$pos = 0;
		while ( ($pos = stripos($text, $needle, $pos)) !== false ) {
			$occurences[] = [
				'from' => max(0, $pos-$before),
				'to' => min(strlen($text), $pos+strlen($needle)+$after),
			];
			$pos += strlen($needle);
		}
	}
	// sort the occurences
	usort($occurences, function($a, $b) {
		return $a['from'] - $b['from'];
	});
	// combine overlapping occurences
	$combined = [];
	foreach ( $occurences as $occurence ) {
		if ( empty($combined) ) {
			$combined[] = $occurence;
		} else {
			$last = $combined[count($combined)-1];
			if ( $occurence['from'] <= $last['to'] ) {
				$last['to'] = $occurence['to'];
				$combined[count($combined)-1] = $last;
			} else {
				$combined[] = $occurence;
			}
		}
	}
	// get the occurences as sub-strings and highlight matches
	$partials = [];
	foreach ( $combined as $occurence ) {
		$substring = substr($text, $occurence['from'], $occurence['to']-$occurence['from']);
		foreach ( $words as $needle ) {
			$substring = preg_replace("/(".$needle.")/i", "<b>$1</b>", $substring);
		}
		$partials[] = $substring;
	}
	// return the html
	return "... " . implode(" ... ", $partials) . " ...";
}
