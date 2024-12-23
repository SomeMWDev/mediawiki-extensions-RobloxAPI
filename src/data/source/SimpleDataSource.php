<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace MediaWiki\Extension\RobloxAPI\data\source;

use MediaWiki\Config\Config;

/**
 * A simple data source that does not process the data.
 */
class SimpleDataSource extends DataSource {

	/**
	 * @var callable The function to create the endpoint.
	 */
	protected $createEndpoint;

	/**
	 * @var callable|null The function to process the data.
	 */
	protected $processData;

	/**
	 * @inheritDoc
	 */
	public function __construct(
		string $id, Config $config, array $expectedArgs, callable $createEndpoint, ?callable $processData = null
	) {
		parent::__construct( $id, self::createSimpleCache(), $config, $expectedArgs );
		$this->createEndpoint = $createEndpoint;
		$this->processData = $processData;
	}

	/**
	 * @inheritDoc
	 */
	public function getEndpoint( $args ): string {
		return call_user_func( $this->createEndpoint, $args );
	}

	/**
	 * @inheritDoc
	 */
	public function processData( $data, $args ) {
		if ( $this->processData ) {
			return call_user_func( $this->processData, $data, $args );
		}

		return $data;
	}

}
