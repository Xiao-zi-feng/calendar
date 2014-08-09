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
namespace OCA\Calendar\Utility;

use DateTime;
use OCP\AppFramework\IAppContainer;
use OCP\Calendar\ICollection;
use OCP\Calendar\IEntity;

class ObjectUtility extends Utility{

	/**
	 * generate a random uri
	 * @return string random uri
	 */
	public static function randomURI() {
		$random = rand().time().rand();
		$md5 = md5($random);
		$substr = substr($md5, rand(0,5),26);

		$uri = 'ownCloud-' . $substr . '.ics';
		return $uri;
	}


	/**
	 * get UTC date for database
	 * @param DateTime $datetime
	 * @return string
	 */
	public static function getUTCforMDB($datetime){
		if($datetime instanceof Datetime) {
			return date('Y-m-d H:i:s', $datetime->format('U'));
		} else {
			return null;
		}
	}


	/**
	 * @param \OCP\Calendar\IEntity|\OCP\Calendar\ICollection $input
	 * @param \OCP\AppFramework\IAppContainer $app
	 * @param string|array &$data
	 * @param bool $json
	 */
	public static function serializeDataWithTimezones($input, IAppContainer $app, &$data, $json=true) {
		if ($input instanceof IEntity || $input instanceof ICollection) {
			/* @var \OCA\Calendar\Sabre\VObject\Component\VCalendar $vcalendar */
			$vcalendar = $input->getVObject();
			$timezoneMapper = $app->query('TimezoneMapper');

			SabreUtility::addMissingVTimezones(
				$vcalendar,
				$timezoneMapper
			);

			$data = $json ? $vcalendar->jsonSerialize() : $vcalendar->serialize();
		} else {
			$data = $json ? array() : true;
		}
	}
}