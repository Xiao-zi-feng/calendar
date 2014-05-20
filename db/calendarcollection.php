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

use OCP\Calendar\IBackendCollection;
use OCP\Calendar\ICalendarCollection;

class CalendarCollection extends Collection implements ICalendarCollection {

	/**
	 * @brief get a collection of all enabled calendars within collection
	 * @return CalendarCollection of all enabled calendars
	 */
	public function enabled() {
		return $this->search('enabled', true);
	}


	/**
	 * @brief get a collection of all disabled calendars within collection
	 * @return CalendarCollection of all disabled calendars
	 */
	public function disabled() {
		return $this->search('enabled', false);
	}


	/**
	 * @brief get a collection of all calendars owned by a certian user
	 * @param string userId of owner
	 * @return CalendarCollection of all calendars owned by user
	 */
	public function ownedBy($userId) {
		return $this->search('ownerId', $userId);
	}


	/**
	 * @brief get a collection of calendars that supports certian components
	 * @param int $component use \OCA\Calendar\Db\ObjectType to get wanted component code
	 * @return CalendarCollection of calendars that supports certian components
	 */
	public function components($component) {
		$newCollection = new CalendarCollection();

		$this->iterate(function($object) use (&$newCollection, $component) {
			if ($object->getComponents() & $component) {
				$newCollection->add(clone $object);
			}
		});

		return $newCollection;
	}


	/**
	 * @brief get a collection of calendars with a certain permission
	 * @param int $cruds use \OCA\Calendar\Db\Permissions to get wanted permission code
	 * @return CalendarCollection of calendars with a certian permission
	 */
	public function permissions($cruds) {
		$newCollection = new CalendarCollection();

		$this->iterate(function($object) use (&$newCollection, $cruds) {
			if ($object->getCruds() & $cruds) {
				$newCollection->add(clone $object);
			}
		});

		return $newCollection;
	}


	/**
	 * @brief filter calendars by BackendCollection
	 * @param BackendCollection $backends
	 * @return CalendarCollection of calendars on backends in BackendCollection
	 */
	public function filterByBackends(IBackendCollection $backends) {
		$newCollection = new CalendarCollection();
		$backendObjects = $backends->getObjects();

		$this->iterate(function($object) use (&$newCollection, $backendObjects) {
			foreach($backendObjects as $backendObject) {
				if ($object->getBackend() === $backendObject->getBackend()) {
					$newCollection->add(clone $object);
				}
			}
		});

		return $newCollection;
	}
}