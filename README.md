#Pixiv Downloader for PHP

This program is scraping from [Pixiv](http://www.pixiv.net) on PHP.



##caution!!(まず読め)
* This program is Dos Attack on how to use.
* It is strictly prohibited the use of the data for research purposes other than this program.
* Please take your responsibility on use.
---
* このプログラムは使い方次第ではDos攻撃として利用する事が可能です。
* 研究目的以外の利用を固く禁じます。
* 何があっても自己責任でお願いします。

##required(必須環境)
- PHP5 <
- PEAR [HTTP_CLIENT](http://pear.php.net/package/HTTP_Client/redirected)

##how to use(使い方)
###windows
- click run.bat
- run.batをクリック
###linux/MacOS
- php run.php

##setting(設定)


### line 15

__define("CHARSET","SJIS");__

* Modify it to suit your environment.
* お使いの環境に合わせて変更してください。

### line 21

__define("IS_COMPRESS",TRUE);__

* Compile the cache?
* キャッシュをコンパイルするかどうか。

### line 28

__define("SCRAPE_FROM","2012-01-01 00:00:00");__

* When do you get.(Y-m-d H:i:s)
* いつから取得するか。Y-m-d H:i:s形式で

### line 41 & 42

__define("LOGIN_ID","");__
__define("LOGIN_PASS","");__

* Login ID & PASS
* Operation when you do not enter unknown
* ログインIDとPASS
* 未入力での動作は不明

### line 46

__define("JSON_URI",ROOT_DIR."/test.json");__

* Setting folder & query keywords from JSON or OBJECT FORMAT.
* フォルダー設定と検索キーワードをJSON形式かオブジェクト形式で入力出来ます。