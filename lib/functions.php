<?php
	Class Functions {
		static function dataToTime($str) {
			$needle_ym = array("年","月");
			$needle_d  = array("日");
			$str = str_replace($needle_ym,"-",$str);
			$str = str_replace($needle_d,"",$str);
			$str .= ":00";
			return $str;
		}

		static function shapeHTML($str) {
			$search = array(
				'@<script[^>]*?>.*?<\/script>@si',			// Strip out javascript
				'@<style[^>]*?>[^<]+<\/style>@siU',			// Strip out style
				'@<meta[^>]*?>@si',							// Strip meta tag
				'@<link[^>]*?>@si',							// Strip link tag
				'@style=(\"|\')([^\"\'])+?(\"|\')@si',	// Style Propaty
				'@<input[^>]+>@si',							// Input Tag
				'@<footer.*footer>@si',						// Footer
				'@<\/?iframe[^>]>@si',						// Iframe
			);
			$str = preg_replace($search,"",$str);
			return preg_replace("/(\s{2,}|\n|\r\n)/m"," ",$str);
		}
	}