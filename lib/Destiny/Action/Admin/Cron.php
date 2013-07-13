<?php
namespace Destiny\Action\Admin;

use Destiny\AppException;
use Destiny\Application;
use Destiny\Scheduler;
use Destiny\Utils\Http;
use Destiny\Config;
use Destiny\MimeType;
use Psr\Log\LoggerInterface;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Cron {

	/**
	 * @Route ("/admin/cron")
	 * @Secure ({"ADMIN"})
	 *
	 * @param array $params
	 * @throws AppException
	 */
	public function execute(array $params) {
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new AppException ( 'Action id required.' );
		}
		set_time_limit ( 180 );
		$log = Application::instance ()->getLogger ();
		
		$response = array ();
		$scheduler = new Scheduler ( Config::$a ['scheduler'] );
		$scheduler->setLogger ( $log );
		$scheduler->loadSchedule ();
		$scheduler->executeTaskByName ( $params ['id'] );
		$response ['message'] = sprintf ( 'Execute %s', $params ['id'] );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}