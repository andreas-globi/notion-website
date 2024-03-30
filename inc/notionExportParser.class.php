<?php

// complex class to extract files from Notion html export zip file
// save images and other assets and re-write urls for href and src attributes
// also tries fixing some shortcomings in the html produces by Notion
// not perfect by any means, but results in a usable set of files

class NotionExportParser {

	private $zip;
	private $filename;
	private $out_path;
	private $out_images;
	private $out_assets;
	private $index = "index";
	private $ext = "html";
	private $images = [];
	private $map = [];


	public function __construct(string $filename, string $out_path, $out_images, $out_assets, $index = "index", $ext = "html") {
		$this->filename = $filename;
		$this->out_path = $out_path;
		$this->out_images = $out_images;
		$this->out_assets = $out_assets;
		$this->index = $index;
		$this->ext = $ext;
		while ( substr($this->out_path, -1) == "/" ) {
			$this->out_path = substr($this->out_path, 0, -1);
		}
		if ( ! file_exists($filename) ) throw new Exception("zip file does not exist: ".$filename);
		if ( ! file_exists($out_path) || ! is_dir($out_path) || ! is_writable($out_path) ) {
			throw new Exception("output path not writable: " . $out_path);
		}
		if ( ! file_exists($out_images) || ! is_dir($out_images) || ! is_writable($out_images) ) {
			throw new Exception("images path not writable: " . $out_images);
		}
		if ( ! file_exists($out_assets) || ! is_dir($out_assets) || ! is_writable($out_assets) ) {
			throw new Exception("assets path not writable: " . $out_assets);
		}
	}

	public function process() {
		$this->zip = new ZipArchive;
		if ( $this->zip->open($this->filename) !== true ) {
			throw new Exception("could not open zip file: ".$this->filename);
		}
		$initialPage = $this->getFirstHtmlFile();

		$pages = [$initialPage];
		$this->map = [];
		$linkMap = [];
		$this->map[$this->urlToId($initialPage)] = $this->index;
		$empties = [];

		while ( sizeof($pages) > 0 ) {
			$page = array_pop($pages);
			$id = $this->urlToId($page);
			$html = $this->getPageFromZip($page);
			echo "processing: ".$page." - ".strlen($html)." bytes" . PHP_EOL;
			$links = $this->getLinks($html);
			$linkMap = array_merge($linkMap, $links);
			foreach ( $links as $link ) {
				$pages[] = $link["url"];
			}
			$this->processImages($html);
			$html = $this->unlinkEmpty($html, $empties);
			$html = $this->fixFrames($html);
			$html = $this->fixLinkIconImages($html, $linkMap);
			$html = $this->fixMiscHtml($html);
			$html = $this->add_css($html);
			$html = $this->add_js($html);
			$out = $this->out_path . "/" . $this->map[$id].".".$this->ext;
			file_put_contents($out, $html);
		}

		$this->zip->close();
	}

	private function getPageFromZip($wanted) {
		for ( $i=0; $i<$this->zip->numFiles; $i++ ) {
			$filename = $this->zip->getNameIndex($i);
			if ( substr($filename, -5) != ".html" ) continue;
			if ( substr($filename, -strlen($wanted)) != $wanted ) continue;
			$html = $this->zip->getFromIndex($i);
			return $html;
		}
		return "";
	}

	private function getLinks(&$html) {
		$links = [];
		preg_match_all('/<a href="(.*?)"/', $html, $matches);
		foreach ( $matches[1] as $url ) {
			$rel = $this->filenameFromUrl($url);
			if ( ! $rel ) continue;
			$id = $this->urltoId($url);
			$name = $this->urlToName($url);
			if ( ! $id || ! $name ) {
				$this->saveAsset($rel, $rel);
				$html = $this->replaceLinks($url, "assets/".$rel, $html);
				continue;
			}
			if ( isset($this->map[$id]) ) { // already been mapped before
				$html = $this->replaceLinks($url, $this->map[$id].".".$this->ext, $html);
				continue;
			}
			$links[] = [
				"url" => $rel,
				"id" => $id,
				"name" => $name.".".$this->ext,
			];
			$this->map[$id] = $name;
			$html = $this->replaceLinks($url, $name.".".$this->ext, $html);
		}
		return $links;
	}

	private function processImages(&$html) {
		preg_match_all('/src="(.*?)"/', $html, $matches);
		foreach ( $matches[1] as $url ) {
			if ( preg_match("/[a-z]+:\/\//", $url) ) continue;
			$relname = urldecode($url);
			$parts = explode("/", $relname);
			$basename = $parts[sizeof($parts)-1];
			$parts = explode(".", $basename);
			$ext = $parts[sizeof($parts)-1];
			unset($parts[sizeof($parts)-1]);
			$basename = implode(".", $parts);
			$add = "";
			while ( in_array($basename.$add.".".$ext, $this->images) ) {
				$add = "-" . (intval(substr($add, 1))+1);
			}
			$basename = $basename.$add.".".$ext;
			$this->images[] = $basename;
			$this->saveImage(urldecode($url), $basename);
			$html = str_replace('src="'.$url.'"', 'src="images/'.$basename.'"', $html);
		}
	}

	private function saveImage($relname, $basename) {
		$this->saveAsset($relname, $basename, "images");
	}

	private function saveAsset($relname, $basename, $folder="assets") {
		$out = $this->out_assets;
		if ( $folder == "images" ) $out = $this->out_images;
		for ( $i=0; $i<$this->zip->numFiles; $i++ ) {
			$filename = $this->zip->getNameIndex($i);
			if ( substr($filename, -strlen($relname)) != $relname ) continue;
			$contents = $this->zip->getFromIndex($i);
			file_put_contents($out."/".$basename, $contents);
		}
	}

	private function replaceLinks($from, $to, $html) {
		preg_match_all('/<a href="(.*?)"/', $html, $matches);
		foreach ( $matches[1] as $url ) {
			if ( $url == $from ) {
				$html = str_replace('href="'.$from.'"', 'href="'.$to.'"', $html);
			}
		}
		return $html;
	}

	private function getFirstHtmlFile() {
		for ( $i=0; $i<$this->zip->numFiles; $i++ ) {
			$filename = $this->zip->getNameIndex($i);
			if ( substr($filename, -5) != ".html" ) continue;
			if ( stristr($filename, "/") ) continue;
			$initialPage = $filename;
		}
		if ( ! $initialPage ) throw new Exception("could not find initial page");
		return $initialPage;
	}

	private function filenameFromUrl($url) {
		if ( preg_match("/[a-z]+:\/\//", $url) ) return false;
		$url = html_entity_decode(urldecode($url));
		$parts = explode("/", $url);
		$rel = $parts[sizeof($parts)-1];
		return $rel;
	}

	private function urlToId($url) {
		$url = urldecode($url);
		$url = html_entity_decode(urldecode($url));
		$rel = $this->filenameFromUrl($url);
		$parts = explode(".", $rel);
		$first = $parts[0];
		$parts = explode(" ", $first);
		$last = $parts[sizeof($parts)-1];
		if ( strlen($last) != 32 ) return false;
		return $last;
	}

	private function urlToName($url) {
		$url = urldecode($url);
		$url = html_entity_decode(urldecode($url));
		if ( substr($url, 0, 7) == "http://" ) return false;
		if ( substr($url, 0, 8) == "https://" ) return false;
		if ( ! stristr($url, ".") ) return false;
		$rel = $this->filenameFromUrl($url);
		$id = $this->urlToId($rel);
		if ( ! $id ) return false;
		// Foo-Bar-761d65c7d5c247eb98126ab3d4192898.html
		$parts = explode($id, $rel);
		$name = $parts[0];
		$name = strtolower(trim($name));
		$name = preg_replace("/\s+/", "-", $name);
		$name = preg_replace("/[^a-z0-9_-]/", "", $name);
		if ( empty($name) ) $name = "page";
		$allnames = array_values($this->map);
		$add = "";
		while ( in_array($name.$add, $allnames) ) {
			$add = "-" . (intval(substr($add, 1))+1);
		}
		return $name.$add;
	}

	private function unlinkEmpty($html, $empties) {
		foreach ( $empties as $link ) {
			$name = preg_quote($link['name']);
			$rex = '~<a[^>]*href="'.$name.'"[^>]*>([^<]*)</a>~ism';
			$html = preg_replace($rex, "$1", $html);
		}
		return $html;
	}

	private function fixFrames($html) {
		preg_match_all('/<div class="source"><a href="(.*?)">(.*?)<\/a><\/div>/ism', $html, $matches);
		foreach ( $matches[1] as $k => $url ) {
			$parts = explode(".", $url);
			$ext = strtolower(trim($parts[sizeof($parts)-1]));
			if ( strlen($ext) <= 4 && ! in_array($ext, ["html","php","asp"]) ) continue; // this is not iframable - possibly a file attachment
			$search = $matches[0][$k];
			// default iframe
			$replace = '<iframe src="'.$url.'" style="width:100%;height:100%;border:0"></iframe>';
			// check for youtube
			$regExp = "/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/";
			preg_match($regExp, $url, $match);
			if ( is_array($match) && isset($match[2]) && strlen($match[2]) >= 10 ) {
				$replace = '<div style="position: relative; width: 100%; padding-bottom: 56.25%;">';
				$replace .= '<iframe width="560" height="315" src="https://www.youtube.com/embed/'	. $match[2] . '" frameborder="0" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen></iframe>';
				$replace .= '</div>';
			}
			$html = str_replace($search, $replace, $html);
		}
		return $html;
	}


	private function fixLinkIconImages($html, $linkMap) {
		// fix image sources
		preg_match_all('/<a href="(.*?)".*?>.*?<img class="icon" src="(.*?)"/', $html, $matches);
		foreach ( $matches[2] as $k => $src ) {
			if ( substr($src, 0, 4) == "http" ) continue;
			$filename = str_replace("%20", " ", $src);
			$inLink = $matches[1][$k];
			$thisLink = false;
			foreach ( $linkMap as $link ) {
				if ( $link['name'] != $inLink ) continue;
				$thisLink = $link;
				break;
			}
			if ( $thisLink === false ) continue;
			$parts = explode("/", $src);
			if ( sizeof($parts) < 2 ) continue;
			$file = $parts[sizeof($parts)-1];
			unset($parts[sizeof($parts)-1]);
			$path = implode("/", $parts);
			$replace = $thisLink['dir']."/".$file;
			$html = str_replace('src="'.$src.'"', 'src="'.$replace.'"', $html);
		}
		return $html;
	}

	private function fixMiscHtml($html) {
		$html = preg_replace("/<details[^>]*>/", '<details>', $html);
		// find all tables with images and add gallery class
		preg_match_all("/<table[^>]+collection-content[^>]+>.*?<\/table>/ism", $html, $tables);
		if ( is_array($tables) ) {
			foreach ( $tables as $arr ) {
				if ( is_array($arr) && isset($arr[0]) ) {
					$tabhtm = $arr[0];
					// tables with images should be galleries
					if ( stristr($tabhtm, '<img ') ) {
						$tab2 = str_replace('<table class="collection-content"', '<table class="collection-content gallery"', $tabhtm);
						$html = str_replace($tabhtm, $tab2, $html);
					}
				}
			}
		}
		return $html;
	}

	private function table2gallery($html) {
		// only if contains images
		if ( ! stristr($html, '<img ') ) return $html;
		$tab2 = str_replace('<table class="collection-content"', '<table class="collection-content gallery"', $html);
		preg_match_all("/<td[^>]+collection-content[^>]+>.*?<\/table>/ism", $html, $tables);
		if ( is_array($tables) ) {
			foreach ( $tables as $arr ) {
				if ( is_array($arr) && isset($arr[0]) ) {
					$tabhtm = $arr[0];
					// tables with images should be galleries
					if ( stristr($tabhtm, '<img ') ) {
						$tab2 = str_replace('<table class="collection-content"', '<table class="collection-content gallery"', $tabhtm);
						$html = str_replace($tabhtm, $tab2, $html);
					}
				}
			}
		}
	}
// check if file exists using real path - because file_exists inside a phar is fucking weird
	private function realFileExists($filename) {
		return file_exists(realpath($filename));
	}

// standard css
	private function add_css(&$html) {
		$css = '
<style>
a > img { pointer-events: none; }
article { margin-top: -37px; padding: 0 20px; min-height: 80vh; }
.page-cover-image { width: 100vw; min-width: 98vw; position: relative; margin-left: -49vw; left: 50%; z-index: -1; background: #eee; }
.icon { vertical-align: baseline; }
/*header .icon { font-size: 1.5em; margin-top:-0.25em; }*/
h1 { padding: 5px 0; }
h2 { padding: 4px 0; }
h3 { padding: 3px 0; }
h4 { padding: 2px 0; }
h5 { padding: 1px 0; }
figure { margin: 0.5em 0 !important; }
figure.image { text-align: left; }
figure.callout { border: 1px solid #ddd; background: #f8f8f8; }
code { font-size: 1em; color: #b00; }
.code > code { white-space: pre-wrap; font-size: 1em; color: #009; }
.bookmark { background: #f8f8f8; overflow: hidden; max-height: 6em; }
.bookmark-title { overflow: visible; min-height: 1.5em; }
.bookmark-href { overflow: hidden; }
.bookmark-text { min-height: 3em; max-height: 3em; }
.table_of_contents a { color: rgb(11,110,153); opacity: 0.9; }
blockquote { opacity: 0.7; }
@media (max-width: 767px) {
    div.column-list { display: block; }
    div.column { width: 100% !important; min-width: 100%; max-width: 100%; padding: 0; }
}
table.gallery { width: calc(100% + 20px); border: 0; margin: -10px; }
table.gallery thead { display: none; }
table.gallery tbody tr { display: inline-block; border: 1px solid #ddd; padding: 10px; width: calc(33% - 18px); min-width: 250px; min-height: 150px; float: left; margin: 10px; }
table.gallery tbody tr td { display: block; border: 0; }
table.gallery tbody tr td.cell-title { font-weight: bold; }
table.gallery tbody tr td img { width: 100% !important; max-width: 100% !important; max-height: unset !important; }
table.properties img { max-width: 100% !important; max-height: unset !important; width: unset !important; }
</style>
';
		return str_replace('</head>', $css . '</head>', $html);
	}

	private function add_js($html) {
		$js = '
<script>
(function() {
	// fix gallery images
	var gals = document.querySelectorAll("table.gallery");
	for ( var i=0; i<gals.length; i++) {
		var trs = gals[i].querySelectorAll("tr");
		for ( var j=0; j<trs.length; j++) {
			var tds = trs[j].querySelectorAll("td");
			var link = false;
			// find first non-image link
			for ( var k=0; k<tds.length; k++) {
				var td = tds[k];
				if ( link == false && td.querySelector("a") != null && td.querySelector("a img") == null ) {
					link = td.querySelector("a").href;
				}
			}
			// attach it to all images
			for ( var k=0; k<tds.length; k++) {
				var td = tds[k];
				if ( td.querySelector("span a img") != null ) {
					td.innerHTML = td.querySelector("span").innerHTML;
					if ( link ) {
						td.querySelector("a").href = link;
						td.querySelector("a").style.display = "inline-block";
					}
				}
			}
		}
	}
})();
</script>
';
		return str_replace('</body>', $js . '</body>', $html);
	}


}