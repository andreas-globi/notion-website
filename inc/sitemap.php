<?php
$config = require __DIR__ . "/../config/vars.php";

$scheme = (
	(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
	|| (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
) ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$prefix = rtrim($config['path_from_root'] ?? '', '/');
$base   = $scheme . '://' . $host . $prefix;

$files = glob(realpath(__DIR__ . "/../cache/") . "/*.html") ?: [];

usort($files, function($a, $b) {
	$an = basename($a, '.html');
	$bn = basename($b, '.html');
	if ($an === 'index') return -1;
	if ($bn === 'index') return 1;
	return strcmp($an, $bn);
});

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($files as $file) {
	$name = basename($file, '.html');
	if ($name === '404') continue;
	$path = ($name === 'index') ? '/' : '/' . $name . '.html';
	$loc  = htmlspecialchars($base . $path, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	$lastmod = date('c', filemtime($file));
	echo "  <url>\n    <loc>{$loc}</loc>\n    <lastmod>{$lastmod}</lastmod>\n  </url>\n";
}
echo '</urlset>' . "\n";
