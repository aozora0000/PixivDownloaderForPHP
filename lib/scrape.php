<?php
	class Scrape {
		//初期設定
		CONST USER_AGENT = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ja-JP; rv:1.9.1.4) Gecko/20091016 Firefox/3.5.4 ID:20091016092926';
		CONST PAR_PAGE = 20;
		CONST PIXIV_ROOT = "http://www.pixiv.net";

		//ログイン設定
		CONST LOGIN_URI = 'https://www.secure.pixiv.net/login.php';
		CONST LOGIN_ID_FIELD = "pixiv_id";
		CONST LOGIN_PASS_FIELD = "pass";

		//検索設定
		CONST MYPAGE_URI = 'http://www.pixiv.net/mypage.php';
		CONST SEARCH_URI = 'http://www.pixiv.net/search.php';
		CONST SEARCH_FIELD = 'word';

		//scrape setting
		CONST IMG_PROPATY = 'data-src';

		public function __construct() {
			if(is_file("cookie.txt")) {
				if(!$this->_set_client()) {
					print "LOGIN ERROR!";
					exit;
				}
			} else {
				if(!$this->_new_client()) {
					print "LOGIN ERROR!";
					exit;
				}
			}
		}
		public function __destruct() {
			unset($this->client);
		}

		static function LOGIN_ID_VALUE() {
			return (defined("LOGIN_ID")) ? LOGIN_ID : "";
		}

		static function LOGIN_PASS_VALUE() {
			return (defined("LOGIN_PASS")) ? LOGIN_PASS : "";
		}

		/*
		*	外部アクセス用クラス
		*/
		public function set_keyword($keyword) {
			$this->key = urlencode($keyword);
		}

		public function count() {
			$keyword = $this->key;
			$url = self::SEARCH_URI."?word={$keyword}&s_mode=s_tag";
			$html = str_get_html($this->get($url));
			$count_badge = $html->find(".count-badge",-1)->innertext;
			//file_put_contents("count.txt",$count_badge);
			$count = (int)str_replace("件","",$count_badge);
			return ceil($count / self::PAR_PAGE);
		}

		public function search($p = NULL) {
			$keyword = $this->key;
			$url = (is_null($p)) ? self::SEARCH_URI."?word={$keyword}&s_mode=s_tag" : self::SEARCH_URI."?word={$keyword}&s_mode=s_tag&p={$p}";
			$result = $this->get($url);
			return $result;
		}

		public function member_illust($url) {
			$result = $this->get($url,TRUE);
			return $result;
		}

		public function get_image($file_dir,$i) {
			$result = $this->search($i);
			$html = str_get_html($result);
			foreach($html->find(".work") as $elem) {
				$uri = self::PIXIV_ROOT.str_replace("&amp;","&",$elem->href);
				$result = $this->member_illust($uri);
				$illust_html = str_get_html($result);
				$posted = Functions::dataToTime($illust_html->find("ul.meta li",0)->innertext);
				if(defined("SCRAPE_FROM")) {
					if(strtotime(SCRAPE_FROM) <= strtotime($posted)) {
						$this->_get_image($illust_html,$file_dir);
					} else {
						return false;
					}
				} else {
					$this->_get_image($illust_html,$file_dir);
				}
			}
			unset($html);
			return true;
		}

		public static function cookie_remove($file_name = "cookie.txt") {
			if(is_file($file_name)) {
				@unlink($file_name);
			}
		}

		/*
		*	内部アクセス用クラス
		*/
		private function _new_client() {
			$this->client = &new HTTP_CLIENT();
			$this->client->enableHistory = false;
			$this->client->setDefaultHeader('User-Agent',self::USER_AGENT);
			$this->login();
			return $this->_set_client();
		}

		private function _set_client() {
			$this->client = NULL;
			$seriarize = file_get_contents("cookie.txt");
			$this->client = &new HTTP_CLIENT(null,null,unserialize($seriarize));
			$this->client->enableHistory = false;
			$this->client->setDefaultHeader('User-Agent',self::USER_AGENT);
			if($this->check(self::MYPAGE_URI) == "200") {
				return true;
			} else {
				return false;
			}
		}


		private function login() {
			$params = array(
				"mode"=>"login",
				"skip"=>1,
				self::LOGIN_ID_FIELD=>self::LOGIN_ID_VALUE(),
				self::LOGIN_PASS_FIELD=>self::LOGIN_PASS_VALUE(),
			);
			$this->client->post(self::LOGIN_URI,$params);
			$classCookieManager = $this->client->getCookieManager();
			$classCookieManager->serializeSessionCookies(true);
			$seriarize = serialize($classCookieManager);
			file_put_contents('cookie.txt',$seriarize);
			return true;
		}

		private function _get_image($illust_html,$file_dir) {
			foreach($illust_html->find("div.works_display a") as $a) {
				$illust_uri = self::PIXIV_ROOT."/".str_replace("&amp;","&",$a->href);
				$html = str_get_html($this->get($illust_uri));
				if(preg_match("/manga/",$illust_uri)) {
					$this->_get_image_from_manga($html,$file_dir);
				} else {
					$this->_get_image_from_single($html,$file_dir);
				}
				unset($html);
			}
			unset($illust_html);
		}

		private function _get_image_from_single($html,$file_dir) {
			$img_src = $html->find("img",0)->src;
			$file_name = pathinfo($img_src);
			$img_name = preg_replace("/(\/|\?\d+$)/","",$file_name["basename"]);
			if(!file_put_contents($file_dir.$img_name,$this->get($img_src))) {
				file_put_contents("error.txt",$img_src."\n".$img_name);
			}
			unset($html);
		}

		private function _get_image_from_manga($html,$file_dir) {
			$img_propaty = self::IMG_PROPATY;
			foreach($html->find(".item-container img") as $img) {
				$img_src = $img->$img_propaty;
				$file_name = pathinfo($img_src);
				$img_name = preg_replace("/(\/|\?\d+$)/","",$file_name["basename"]);
				if(!file_put_contents($file_dir.$img_name,$this->get($img_src))) {
					file_put_contents("error.txt",$img_src."\n".$img_name);
				}
			}
			unset($html);
		}

		public function get($url,$cache = false) {
			if($cache) {
				$res = Cache::getCache($url);
				return ($res) ? $res : Cache::setCache($url,$this->_get($url));
			} else {
				return $this->_get($url);
			}
		}
		private function _get($url) {
			$this->client->get($url);
			$responce = $this->client->currentResponse();
			$res = $responce["body"];
			if(preg_match("/image/i",$responce["headers"]["content-type"])) {
				return $res;
			} else {
				return Functions::shapeHTML($res);
			}
		}

		private function check($url) {
			$this->client->get($url);
			$responce = $this->client->currentResponse();
			return $responce["code"];
		}

		private function post($url,$params) {
			$this->client->post($url,$params);
			$responce = $this->client->currentResponce();
			$res = $responce["body"];
			return $res;
		}
	}