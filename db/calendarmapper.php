<?php
/**
 * ownCloud - Calendar App
 *
 * @author Georg Ehrke
 * @copyright 2014 Georg Ehrke <oc.list@georgehrke.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\Calendar\Db;

use \OCP\AppFramework\IAppContainer;

class CalendarMapper extends Mapper {

	/**
	 * timezoneMapper object
	 * @var \OCA\Calendar\Db\TimezoneMapper
	 */
	private $timezoneMapper;


	/**
	 * @param iAppContainer $app: Instance of the API abstraction layer
	 * @param string $tablename
	 */
	public function __construct(IAppContainer $app, $tablename='clndr_calcache'){
		parent::__construct($app, $tablename);
		$this->timezoneMapper = $app->query('TimezoneMapper');
	}


	/**
	 * find calendar by backend, uri and userId
	 * @param string $backend
	 * @param string $uri
	 * @param string $userId
	 * @throws DoesNotExistException: if the item does not exist
	 * @throws MultipleObjectsReturnedException: if more than one item found
	 * @return calendar object
	 */
	public function find($backend, $uri, $userId){
		$sql  = 'SELECT * FROM `' . $this->getTableName() . '` ';
		$sql .= 'WHERE `backend` = ? AND `uri` = ? AND `user_id` = ?';

		$row = $this->findOneQuery($sql, array(
			$backend, $uri, $userId
		));

		return new Calendar($row);
	}


	/**
	 * find calendar's ctag
	 * @param string $backend
	 * @param string $uri
	 * @param string $userId
	 * @throws DoesNotExistException: if the item does not exist
	 * @throws MultipleObjectsReturnedException: if more than one item found
	 * @return calendar object
	 */
	public function findCTag($backend, $uri, $userId) {
		$sql  = 'SELECT `ctag` FROM `' . $this->getTableName() . '` ';
		$sql .= 'WHERE `backend` = ? AND `uri` = ? AND `user_id` = ?';

		$row = $this->findOneQuery($sql, array(
			$backend, $uri, $userId
		));

		return intval($row['ctag']);
	}


	/**
	 * find all calendars of a user
	 * @param string $userId
	 * @param integer $limit
	 * @param integer $offset
	 * @return CalendarCollection
	 */
	public function findAll($userId, $limit, $offset){
		$sql  = 'SELECT * FROM `'. $this->getTableName() . '` ';
		$sql .= 'WHERE `user_id` = ? ORDER BY `order`';

		return $this->findEntities($sql, array(
			$userId
		), $limit, $offset);
	}


	/**
	 * find all calendars of a user on a backend
	 * @param string $backend
	 * @param string $userId
	 * @param integer $limit
	 * @param integer $offset
	 * @return CalendarCollection
	 */
	public function findAllOnBackend($backend, $userId, $limit, $offset) {
		$sql  = 'SELECT * FROM `'. $this->getTableName() . '` ';
		$sql .= 'WHERE `backend` = ? AND `user_id` = ? ORDER BY `order`';

		return $this->findEntities($sql, array(
			$backend, $userId
		), $limit, $offset);
	}


	/**
	 * @param string $backend
	 * @param string $userId
	 * @return array
	 */
	public function findAllIdentifiersOnBackend($backend, $userId) {
		$sql  = 'SELECT `uri` FROM `'. $this->getTableName() . '` ';
		$sql .= 'WHERE `backend` = ? AND `user_id` = ?';

		$identifiers = array();

		$uris =  $this->findEntities($sql, array(
			$backend, $userId
		));

		foreach($uris as $uri) {
			$identifiers[] = $uri['uri'];
		}

		return $identifiers;
	}


	/**
	 * number of calendars by user
	 * @param string $userId
	 * @throws DoesNotExistException: if the item does not exist
	 * @return integer
	 */
	public function count($userId){
		$sql  = 'SELECT COUNT(*) AS `count` FROM ';
		$sql .= '`' . $this->getTableName() . '` WHERE `user_id` = ?';

		$row = $this->findOneQuery($sql, array(
			$userId
		));

		return intval($row['count']);
	}


	/**
	 * number of calendars by user on a backend
	 * @param string $backend
	 * @param string $userId
	 * @throws DoesNotExistException: if the item does not exist
	 * @return integer
	 */
	public function countOnBackend($backend, $userId) {
		$sql  = 'SELECT COUNT(*) AS `count` FROM `' . $this->getTableName() . '` ';
		$sql .= 'WHERE `backend` = ? AND `user_id` = ?';

		$row = $this->findOneQuery($sql, array(
			$backend,
			$userId
		));

		return intval($row['count']);
	}


	/**
	 * does a calendar exist
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @throws DoesNotExistException: if the item does not exist
	 * @return boolean
	 */
	public function doesExist($backend, $calendarURI, $userId) {
		$sql  = 'SELECT COUNT(*) AS `count` FROM `' . $this->tableName . '`';
		$sql .= ' WHERE `backend` = ? AND `uri` = ? AND `user_id` = ?';

		$row = $this->findOneQuery($sql, array(
			$backend,
			$calendarURI,
			$userId
		));

		$count = intval($row['count']);
		return ($count !== 0);
	}


	/**
	 * checks if a calendar allows a certain action
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @param integer $cruds
	 * @throws DoesNotExistException: if the item does not exist
	 * @return boolean
	 */
	public function doesAllow($backend, $calendarURI, $userId, $cruds) {
		$sql  = 'SELECT COUNT(*) AS `count` FROM `' . $this->tableName . '`';
		$sql .= ' WHERE `cruds` & ? AND `backend` = ? AND `uri` = ? AND `user_id` = ?';

		$row = $this->findOneQuery($sql, array(
			$cruds,
			$backend,
			$calendarURI,
			$userId
		));

		$count = intval($row['count']);
		return ($count !== 0);
	}


	/**
	 * checks if a calendar supports a certian component
	 * @param string $backend
	 * @param string $calendarURI
	 * @param string $userId
	 * @param integer $component
	 * @throws DoesNotExistException: if the item does not exist
	 * @return boolean
	 */
	public function doesSupport($backend, $calendarURI, $userId, $component) {
		$sql  = 'SELECT COUNT(*) AS `count` FROM `' . $this->tableName . '`';
		$sql .= ' WHERE `components` & ? AND `backend` = ? AND `uri` = ? AND `user_id` = ?';

		$row = $this->findOneQuery($sql, array(
			$component,
			$backend,
			$calendarURI,
			$userId
		));

		$count = intval($row['count']);
		return ($count !== 0);
	}
}