<?php
$scheme = (
	(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
	|| (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
) ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

header('Content-Type: text/plain; charset=utf-8');
?>
User-agent: *
Allow: /
Disallow: /cache/
Disallow: /zips/
Disallow: /config/
Disallow: /inc/

Sitemap: <?= $scheme . '://' . $host ?>/sitemap.xml
