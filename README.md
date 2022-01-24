# PukiWiki用プラグイン<br>サイトマップ生成 sitemap.inc.php

サイトマップXMLを生成する[PukiWiki](https://pukiwiki.osdn.jp/)用プラグイン。

|対象PukiWikiバージョン|対象PHPバージョン|
|:---:|:---:|
|PukiWiki 1.5.3 ~ 1.5.4RC (UTF-8)|PHP 7.4 ~ 8.1|

## インストール

sitemap.inc.php を PukiWiki の plugin ディレクトリに配置してください。

## 使い方

```
（ウィキのURL）?plugin=sitemap
```

## 設定

ソース内の下記の定数で動作を制御することができます。

|定数名|値|既定値|意味|
|:---|:---:|:---:|:---|
|PLUGIN_SITEMAP_PAGE_ALLOW| 文字列| |対象ページ名を表す正規表現|
|PLUGIN_SITEMAP_PAGE_DISALLOW| 文字列| '``^(Pukiwiki\/.*)$``'|除外ページ名を表す正規表現|
|PLUGIN_SITEMAP_FILE| 文字列| |出力ファイル名（例：'sitemap.xml'）|

## 処理の詳細

列挙するのは次のすべての条件で絞り込まれたページです。  
特殊なページを省くなど、サイトマップを制御したいかたは条件を調整してください。正規表現の知識が必要です。

1. RecentChanges ページを除外する
2. pukiwiki.ini.php 内 $non_list で指定されたページを除外する
3. PLUGIN_SITEMAP_PAGE_ALLOW 定数が空でなければ、それが表すページのみを対象とする
4. PLUGIN_SITEMAP_PAGE_DISALLOW 定数が空でなければ、それが表すページを除外する（デフォルトでは「Pukiwiki/」から始まるページを除外）
5. 閲覧不可のページを除外する

サイトマップは専用のキャッシュファイル cache/sitemap.dat に保存し、次回から処理を省略します。  
cache/recent.dat のタイムスタンプを参照し、ページの編集・増減を検知したら数え直します。  
もしプラグイン内の定数やコードを書き換えて条件を変更したら、キャッシュファイル cache/sitemap.dat を削除するか適当なページを編集して強制的にキャッシュを更新してください。
