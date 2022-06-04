<?php
namespace OCA\Whereami\Controller;

use OCA\Whereami\Db\Bdd;
use OCA\Whereami\MyClass\MyEvent;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Controller;
use OCP\Calendar\IManager;
use Sabre\VObject;
use OCA\Polls\Model\CalendarEvent;
use DateTimeImmutable;
use DateTime;

class PageController extends Controller {
	private $userId;
	private $GetNomComplet;
    private $calendarManager;
	private $myDb;

	public function __construct($AppName,
								$UserId,
								IRequest $request, 
								IManager $calendarManager,
								Bdd $myDb
								){
		parent::__construct($AppName, $request);

		$this->userId = $UserId;
		$this->myDb = $myDb;
		$this->calendarManager = $calendarManager;
		$this->calendars = $this->calendarManager->getCalendars();

		$this->GetNomComplet = function($val)
		{
			$myObj = json_decode($val);
			$parameters = $myObj->{'displayname'}->{'value'};
			if(empty($parameters))
				$parameters="Non affecté";
			return $parameters;
		};
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		$res = $this->getEvents(new DateTime('2022-06-01'), new DateTime('2022-06-15'));
		return new TemplateResponse('whereami', 'index', array('uids' => $this->myDb->listUID(),
																'Events' => $res));
	}


	/**
	 * Getall events in DateTime interval
	 */
	public function getEvents(DateTime $from, DateTime $to):array {
		$searchResults = $this->calendarManager->search("[loc]", ['SUMMARY'], ['timerange' => ['start' => $from, 'end' => $to]]);
		$events = [];
		foreach($searchResults as $c){
			$e = new MyEvent($c, $this->myDb);
			if(!array_key_exists($e->nextcloud_users,$events)){
				$events[$e->nextcloud_users] = [];
			}
			array_push($events[$e->nextcloud_users], $e);
		}
		return $events;
	}
}
