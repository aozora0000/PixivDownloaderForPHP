<?php
	Class Process {
		CONST LOG_EXT = ".txt";
		CONST PROCESS_RESPONCE = 0.5;
		CONST RETRY_TIME = 3;

		public function __construct($slug,$query,$download_dir,$log_dir) {
			$slug = preg_replace("/_[0-9]+$/s","",$slug);
			$file_dir = $download_dir.$slug."/";
			if(!is_dir($file_dir)) {
				mkdir($file_dir);
			}
			$this->log_dir = $log_dir;
			$this->slug = $slug;
			$this->query = $query;
			$this->file_dir = $file_dir;
			$this->latency_log　= "";
		}

		public function __destruct() {
			$file = $this->log_dir.date("Ymd-His")."-".$this->slug.self::LOG_EXT;
			Console::put_dump($file,$this->latency_log);
		}

		public function main() {
			$error = 0;
			//コンテンツ数カウント
			Console::log("件数取得中。。。");
			$limit = $this->count($this->query);
			for($i = 1;$i <= $limit; ++$i) {
				$this->elapsed($this->slug,$i);
				Console::log("{$this->query}の検索 {$i}/{$limit}回目のループです。");
				$res = $this->scrape($this->query,$this->file_dir,$i);
				$t = $this->elapsed($slug,$i,TRUE);
				if($res) {
					if($t < self::PROCESS_RESPONCE) {
						if($error <= self::RETRY_TIME) {
							Console::log("コンテンツが見つかりません。\nセッションを終了して再度ページにアクセスします。");
							Scrape::cookie_remove();
							++$error;
							--$i;
						} else {
							Console::log("コンテンツが見つかりません。\nセッションを終了して次の検索ワードに入ります。");
							break 1;
						}
					}
				} else {
					Console::log(SCRAPE_FROM."より以前のコンテンツがありました。\nセッションを終了して次の検索ワードに入ります。");
					break 1;
				}
			}
			Scrape::cookie_remove();
		}

		private function count($query) {
			$scrape = new SCRAPE;
			$scrape->set_keyword($query);
			$limit = $scrape->count();
			unset($scrape);
			return $limit;
		}

		private function scrape($query,$file_dir,$page) {
			$scrape = new SCRAPE;
			$scrape->set_keyword($query);
			$res = $scrape->get_image($file_dir,$page);
			unset($scrape);
			return $res;
		}

		private function elapsed($char,$loop,$action = FALSE) {
			//FALSE -> COUNTUP
			//TRUE  -> PRINT
			switch($action) {
				case(FALSE) :
						$last_key = count($this->latency_log);
						$this->latency_log[$last_key] = new stdClass;
						$this->latency_log[$last_key]->char = $char;
						$this->latency_log[$last_key]->loop = $loop;
						$this->latency_log[$last_key]->start = microtime(true);
					break;
				case(TRUE) :
					$last_key = count($this->latency_log)-1;
					$this->latency_log[$last_key]->end = microtime(true);
					$t = $this->latency_log[$last_key]->end - $this->latency_log[$last_key]->start;
					$t = round($t,2);
					Console::log("{$t}秒掛かりました。","-",2);
					return $t;
					break;
			}
		}
	}