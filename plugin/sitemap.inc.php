<?php
/*
PukiWiki - Yet another WikiWikiWeb clone.
sitemap.inc.php, v1.01 2020 M.Taniguchi
License: GPL v3 or (at your option) any later version

サイトマップを出力するPukiWiki用プラグイン。

【使い方】
（ウィキのURL）?plugin=sitemap

【処理の詳細】
列挙するのは次のすべての条件で絞り込まれたページです。

1) RecentChanges ページを除外する
2) pukiwiki.ini.php 内 $non_list で指定されたページを除外する
3) PLUGIN_SITEMAP_PAGE_ALLOW 定数が空でなければ、それが表すページのみを対象とする
4) PLUGIN_SITEMAP_PAGE_DISALLOW 定数が空でなければ、それが表すページを除外する（デフォルトでは「Pukiwiki/」から始まるページを除外）
5) 閲覧不可のページを除外する

【謝辞】
次のプラグインを参考にし、コードを一部流用させていただきました。
sitemap.inc.php : Google-Sitemaps plugin - Create Google-Sitemaps. Copyright (C) JuJu License: GPL v2 or (at your option) any later version
*/

/////////////////////////////////////////////////
// サイトマッププラグイン設定（sitemap.inc.php）
if (!defined('PLUGIN_SITEMAP_PAGE_ALLOW'))    define('PLUGIN_SITEMAP_PAGE_ALLOW',    '');                 // カウント対象ページ名を表す正規表現
if (!defined('PLUGIN_SITEMAP_PAGE_DISALLOW')) define('PLUGIN_SITEMAP_PAGE_DISALLOW', '^(Pukiwiki\/.*)$'); // カウント除外ページ名を表す正規表現
if (!defined('PLUGIN_SITEMAP_FILE'))          define('PLUGIN_SITEMAP_FILE',          '');                 // 出力ファイル名（例：'sitemap.xml'）


function plugin_sitemap_action() {
	global $whatsnew, $non_list, $defaultpage;

	$cachefile = (PLUGIN_SITEMAP_FILE)? PLUGIN_SITEMAP_FILE : CACHE_DIR . 'sitemap.dat'; // キャッシュファイルパス
	$recentfile = CACHE_DIR . PKWK_MAXSHOW_CACHE; // ページ更新キャッシュファイルパス
	$script = get_script_uri();
	$body = '';

	// キャッシュファイルがない、またはページ更新キャッシュファイルより古かったらサイトマップ生成
	if (!file_exists($cachefile) || (file_exists($recentfile) && (filemtime($cachefile) < filemtime($recentfile)))) {
		$urls = array();
		foreach (get_existpages() as $page) {
			if (($page != $whatsnew)	// RecentChangesページを除外
				&& !preg_match("/$non_list/", $page)	// $non_list に該当するページを除外
				&& ((PLUGIN_SITEMAP_PAGE_ALLOW     == '') ||  preg_match('/' . PLUGIN_SITEMAP_PAGE_ALLOW    . '/', $page))	// 定数指定があれば該当するページのみ対象
				&& ((PLUGIN_SITEMAP_PAGE_DISALLOW  == '') || !preg_match('/' . PLUGIN_SITEMAP_PAGE_DISALLOW . '/', $page))	// 定数指定があれば該当するページを除外
				&& check_readable($page, false, false)	// 閲覧可能なページのみ対象（ただし、これを実行するユーザーの権限において）
			) {
				$isHome = ($page === $defaultpage);
				$url = get_page_uri($page, PKWK_URI_ABSOLUTE);
				$time = date('Y-m-d\TH:i:sP', get_filetime($page));
				$urls[] = '<url><loc>' . $url . '</loc><lastmod>' . $time . "</lastmod></url>\n";
			}
		}

		sort($urls, SORT_NATURAL | SORT_FLAG_CASE);
		foreach ($urls as $url) $body .= $url;
		$body = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n" . $body . '</urlset>';

		// キャッシュファイル書き込み
		$fp = fopen($cachefile, 'w');
		flock($fp, LOCK_EX);
		rewind($fp);
		fwrite($fp, $body);
		flock($fp, LOCK_UN);
		fclose($fp);
	} else {
		// キャッシュファイル読み込み
		$fp = fopen($cachefile, 'r');
		$body = fread($fp, filesize($cachefile));
		fclose($fp);
	}

	// 出力
	header('Content-Type: application/xml; charset=UTF-8');
	echo $body;

	exit;
}
