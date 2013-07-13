<?php
namespace Destiny\Action;

use Destiny\MimeType;
use Destiny\Utils\Http;
use Destiny\Application;
use Destiny\Config;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Youtube {

	/**
	 * @Route ("/youtube")
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$app = Application::instance ();
		$playlist = $app->getCacheDriver ()->fetch ( 'youtubeplaylist' );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $playlist ) );
	}

}