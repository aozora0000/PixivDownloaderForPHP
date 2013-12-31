<?php
	Class Console {
		static function log($text,$separator = "-",$depth = 1) {
			$prefix = str_repeat($separator,$depth);
			$charset = (defined("CHARSET")) ? CHARSET : "SJIS";
			$log = mb_convert_encoding($prefix.$text."\n",$charset,"auto");
			print $log;
			@ob_flush();
			@flush();
			return NULL;
		}
		static function put_dump($file,$obj) {
			ob_start();
			var_dump($obj);
			$out = ob_get_contents();
			ob_end_clean();
			file_put_contents($file,$out);
		}
	}