<?php
/* =====================================================================
 * Porcess Notion HTML Export File
 * ===================================================================== */

// ensure we are running in CLI mode
if ( php_sapi_name() != "cli" ) {
	die("This script can only be run in CLI mode");
}

// ensure vars.php
if ( ! file_exists(__DIR__."/config/vars.php") ) {
	$content = file_get_contents(__DIR__."/config/vars.sample.php");
	$content = str_replace(
		"// NB: this is a sample file, copy this to vars.php and modify as needed",
		"// This is the actual vars file. Replace the values with your own.",
		$content
	);
	file_put_contents(__DIR__."/config/vars.php", $content);
}

// requirements
if ( ! class_exists("ZipArchive") ) throw new Exception("php-zip extension required");

// find most recent file in zips directory
$files = glob(__DIR__."/zips/*.zip");
if ( empty($files) ) {
	die("No zip files found in zips directory\n");
}
usort($files, function($a, $b) {
	return filemtime($a) < filemtime($b);
});
$filename = $files[0];

// output path
$out_html = realpath(__DIR__."/cache");
$out_assets = realpath(__DIR__."/assets");
$out_images = realpath(__DIR__."/images");
if ( ! file_exists($out_html) || ! is_dir($out_html) ) {
	die("Output path does not exist: ".$out_html."\n");
}
if ( ! file_exists($out_assets) || ! is_dir($out_assets) ) {
	die("Assets path does not exist: ".$out_assets."\n");
}
if ( ! file_exists($out_images) || ! is_dir($out_images) ) {
	die("Image path does not exist: ".$out_images."\n");
}

// start
echo "Opening zip file: ".$filename."\n";

// extract zip file
require_once __DIR__ . "/inc/notionExportParser.class.php";
$parser = new NotionExportParser($filename, $out_html, $out_images, $out_assets);
$parser->process();

// done
echo "Completed\n";
