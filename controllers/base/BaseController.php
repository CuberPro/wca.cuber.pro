<?php

namespace app\controllers\base;

use Yii;
use yii\web\Cookie;

class BaseController extends \yii\web\Controller {

	public function init() {
		$this->setLanguage();
	}

	public function actionLang() {
		$providingLanguages = require(Yii::getAlias('@app/config/lang.php'));
		$r = Yii::$app->request;
		$lang=$r->post('lang', array_keys($providingLanguages)[0]);
		if (!isset($providingLanguages[$lang])) {
			$lang = array_keys($providingLanguages)[0];
		}
		$this->setLang($lang);
		$this->redirect(isset($r->referrer) ? $r->referrer : Yii::$app->homeUrl);
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
		if (isset($queryLang) && isset($providingLanguages[$queryLang])) {
			$this->setLang($queryLang);
			return;
		}
		$cookieLang = $r->cookies->getValue('lang', '');
		if (isset($cookieLang) && isset($providingLanguages[$cookieLang])) {
			$this->setLang($cookieLang, false);
			return;
		}
		foreach (array_filter([$queryLang, $cookieLang]) as $lang) {
			foreach ($providingLanguages as $availableLang => $localName) {
				if (strpos($availableLang, substr($lang, 0, 2)) === 0) {
					$this->setLang($availableLang);
					return;
				}
			}
		}

		$acceptLanguages = $r->acceptableLanguages;
		foreach ($acceptLanguages as $lang) {
			if (isset($providingLanguages[$lang])) {
				$this->setLang($lang);
				return;
			}
		}
		foreach ($acceptLanguages as $lang) {
			foreach ($providingLanguages as $availableLang => $localName) {
				if (strpos($availableLang, substr($lang, 0, 2)) === 0) {
					$this->setLang($availableLang);
					return;
				}
			}
		}
	}
}