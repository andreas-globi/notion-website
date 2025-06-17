<?php
require_once __DIR__ . "/inc/siteFunctions.php";
$config = require __DIR__ . "/config/vars.php";

// always force / as home
if ( $_SERVER['REQUEST_URI'] == "/index.php" || $_SERVER['REQUEST_URI'] == "/index.html" ) {
	header("Location: /");
	exit();
}

// figure out which page to show
$q = $_GET['q']??"";
$page = ( $q ? $q : "index" ) . ".html";
$file = realpath(__DIR__."/cache/".$page);

if ( ! file_exists($file) ) {
	if ( ! headers_sent() ) {
		header('HTTP/1.0 404 Not Found');
	}
	include __DIR__ . "/config/404.tpl";
	exit();
}

$html = file_get_contents($file);

// "fix" the html of the page
$headinject = <<<"EOT"
	<script src="inc/common.js"></script>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
EOT;

$html = str_replace('</body>', $headinject.'</body>', $html);

// inject the common header
$header = trim(file_get_contents( __DIR__ . "/config/header.tpl"));
$header = str_replace("{{pathfromroot}}", ($config['path_from_root']??""), $header);
$header = str_replace("{{logoimage}}", ($config['logo_image_src']??""), $header);
$header = str_replace("{{brandname}}", ($config['brand_name']??""), $header);
$header = str_replace("{{enablesearch}}", ($config['enable_search']??"")?"true":"false", $header);

// inject the common footer
$footer = trim(file_get_contents( __DIR__ . "/config/footer.tpl"));
$footer = str_replace("{{footerhtml}}", ($config['footer_html']??""), $footer);

// header injection
$headinject = $config['head_inject']??"";

// build and render
$html = str_replace('<body>', '<body>'.$header, $html);
$html = str_replace('</body>', $footer.'</body>', $html);
$html = str_replace('</head>', $headinject.'</head>', $html);

echo $html;
