<?php
	//charm
	mb_internal_encoding("utf-8");
	mb_http_input("auto");
	mb_http_output("utf-8");

	//DirectorySetting
	define("ROOT_DIR",dirname(__FILE__));
	define("LIB_DIR",ROOT_DIR."/lib/");
	define("LOG_DIR",ROOT_DIR."/log/");
	define("CACHE_DIR",ROOT_DIR."/cache/");
	define("DOWNLOAD_DIR",ROOT_DIR."/file/");

	//ConsoleSetting
	define("CHARSET","SJIS");

	//CacheSetting
	define("CACHE_LIMIT",60*60*24*30);
	define("GC_CACHE_LIMIT",60*60*24*30);
	define("GC_LOG_DIR",LOG_DIR);
	define("IS_COMPRESS",TRUE);

	//PIXIVURI
	define("PIXIV_ROOT","http://www.pixiv.net");

	//ScrapeSetting
	//undefined : NOLIMIT
	define("SCRAPE_FROM","2012-01-01 00:00:00");

	//SCRIPT INCLUDE
	include "HTTP/Client.php";
	include LIB_DIR."scrape.php";
	include LIB_DIR."cache.php";
	include LIB_DIR."simple_html_dom.php";
	include LIB_DIR."console.php";
	include LIB_DIR."process.php";
	include LIB_DIR."functions.php";


	//LOGIN ID:PASS
	define("LOGIN_ID","");
	define("LOGIN_PASS","");


	//slug key from json file
	define("JSON_URI",ROOT_DIR."/test.json");

	$key_from_api = json_decode(file_get_contents(JSON_URI));

	foreach($key_from_api as $key) {
		$process = new Process($key->slug,$key->query,DOWNLOAD_DIR,LOG_DIR);
		$process->main();
		unset($process);
	}
	Cache::gavageCache();