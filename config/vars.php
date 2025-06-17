<?php

return [

	// path to root where this is on the web server, eg "", "/blog", etc
	'path_from_root' => "",

	// logo image src for header
	'logo_image_src' => "https://www.notion.so/icons/notion_gray.svg",

	// brand name for header
	'brand_name' => "Not Notion",

	// should the search box be present or not? (true or false)
	'enable_search' => true,

	// footer html
	'footer_html' => <<<EOT
		(c) My Awesome Website - Powered by Notion
EOT,

	// head injection - eg google analytics
	'head_inject' => <<<EOT
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-C7P0BN3ZRR"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-C7P0BN3ZRR');
</script>	
EOT,

];

