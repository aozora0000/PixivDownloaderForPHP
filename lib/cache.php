<?php
	class Cache {

		//default_configure
		static function CACHE_LIMIT() {
			return (defined("CACHE_LIMIT"))		? CACHE_LIMIT	: 60*60*30;
		}
		static function GC_CACHE_LIMIT() {
			return (defined("GC_CACHE_LIMIT"))	? GC_CACHE_LIMIT: self::CACHE_LIMIT();
		}
		static function GC_LOG_DIR() {
			return (defined("GC_LOG_DIR"))		? GC_LOG_DIR	: dirname(__FILE__)."log/";
		}
		static function CACHE_EXT() {
			return (defined("CACHE_EXT"))		? CACHE_EXT 	: ".dat";
		}
		static function CACHE_DIR() {
			return (defined("CACHE_DIR"))		? CACHE_DIR 	: dirname(__FILE__)."cache/";
		}
		static function CACHE_HASH() {
			return (defined("CACHE_HASH"))		? CACHE_HASH 	: "crc32";
		}
		static function IS_COMPRESS() {
			return (defined("IS_COMPRESS"))		? IS_COMPRESS	: FALSE;
		}
		static function file($seed) {
			$hash = hash(self::CACHE_HASH(),$seed);
			return self::CACHE_DIR().$hash.self::CACHE_EXT();
		}


		static function getCache($seed) {
			$file = self::file($seed);
			if(self::isCache($file)) {
				if(self::IS_COMPRESS()) {
					return gzinflate(file_get_contents($file));
				} else {
					return file_get_contents($file);
				}
			} else {
				return false;
			}
		}

		static function setCache($seed,$str) {
			$file = self::file($seed);
			if(self::IS_COMPRESS()) {
				$compress = gzdeflate($str,1);
			} else {
				$compress = $str;
			}
			file_put_contents($file,$compress);
			return $str;
		}

		static function isCache($file) {
			if(is_file($file)) {
				$progress = time() - filemtime($file);
				$limit = self::CACHE_LIMIT();
				if($limit > $progress) {
					return true;
				} else {
					@unlink($file);
					return false;
				}
			} else {
				return false;
			}
		}

		static function gavageCache() {
			$dir = self::CACHE_DIR();
			$mode = \RecursiveIteratorIterator::LEAVES_ONLY;
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($dir,
					\FilesystemIterator::CURRENT_AS_FILEINFO |
					\FilesystemIterator::KEY_AS_PATHNAME |
					\FilesystemIterator::SKIP_DOTS
				),
				$mode
			);
			foreach ($iterator as $key => $node) {
				$file = $node->getRealPath();
				$limit = self::CACHE_LIMIT();
				$progress = time() - $node->getCTime();
				if($limit < $progress) {
					@unlink($file);
					$log[] = array(
						"file"=>$file,
						"lastmodified"=>$node->getCTime()
					);
				}
			}
			$log_file = self::GC_LOG_DIR().date("Ymd-His")."-gavage.log";
			Console::put_dump($log_file,$log);
		}
	}