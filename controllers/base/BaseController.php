<?php

namespace app\controllers\base;

use Yii;
use yii\web\Cookie;

class BaseController extends \yii\web\Controller {

	public function init() {
		$this->setLanguage();
	}

	private function setLang($lang, $setCookie = true) {
		Yii::$app->language = $lang;
		if ($setCookie) {
			Yii::$app->response->cookies->add(new Cookie([
				'name' => 'lang',
				'value' => $lang,
				'expire' => time() + 30 * 86400,
			]));
		}
	}

	private function setLanguage() {
		$providingLanguages = require(Yii::getAlias('@app/config/lang.php'));
		$r = Yii::$app->request;
		$queryLang = $r->get('lang');
		if (isset($queryLang) && in_array($queryLang, $providingLanguages)) {
			$this->setLang($queryLang);
			return;
		}
		$cookieLang = $r->cookies->get('lang');
		if (isset($cookieLang) && in_array($cookieLang, $providingLanguages)) {
			$this->setLang($cookieLang, false);
			return;
		}
		foreach (array_filter([$queryLang, $cookieLang]) as $lang) {
			foreach ($providingLanguages as $availableLang) {
				if (strpos($availableLang, substr($lang, 0, 2)) === 0) {
					$this->setLang($availableLang);
					return;
				}
			}
		}

		$acceptLanguages = $r->headers->get('Accept-Language');
		$acceptLanguages = explode(',', $acceptLanguages);
		$count = count($acceptLanguages);
		for ($i = 0; $i < $count; $i++) {
			preg_match('/^([a-z]{2}(?:-[A-Z]{2})?)(?:;q=(\d+(?:\.\d+)?))?$/', $acceptLanguages[$i], $matches);
			unset($acceptLanguages[$i]);
			if (!isset($matches[0])) {
				continue;
			}
			$acceptLanguages[$matches[1]] = isset($matches[2]) ? floatval($matches[2]) : 1.0;
		}
		arsort($acceptLanguages);
		$acceptLanguages = array_keys($acceptLanguages);
		foreach ($acceptLanguages as $lang) {
			if (in_array($lang, $providingLanguages)) {
				$this->setLang($lang);
				return;
			}
		}
		foreach ($acceptLanguages as $lang) {
			foreach ($providingLanguages as $availableLang) {
				if (strpos($availableLang, substr($lang, 0, 2)) === 0) {
					$this->setLang($availableLang);
					return;
				}
			}
		}
	}
}