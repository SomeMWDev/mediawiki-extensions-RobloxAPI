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

use FormatJson;
use MediaWiki\Config\Config;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;

/**
 * A data source for getting a user's ID from their username.
 */
class UserIdDataSource extends DataSource {

	public function __construct( Config $config ) {
		parent::__construct( 'userId', self::createSimpleCache(), $config, [
			'Username',
		] );
	}

	/**
	 * @inheritDoc
	 */
	public function getEndpoint( $args ): string {
		return "https://users.roblox.com/v1/usernames/users";
	}

	/**
	 * @inheritDoc
	 */
	public function processData( $data, $args ) {
		$entries = $data->data;
		if ( $entries === null || count( $entries ) === 0 ) {
			throw new RobloxAPIException( 'robloxapi-error-invalid-data' );
		}

		return $entries[0];
	}

	/**
	 * @inheritDoc
	 */
	public function processRequestOptions( array &$options, array $args ) {
		$options['method'] = 'POST';
		$options['postData'] = FormatJson::encode( [ 'usernames' => [ $args[0] ] ] );
	}

	/**
	 * @inheritDoc
	 */
	protected function getAdditionalHeaders( array $args ): array {
		return [
			'Content-Type' => 'application/json',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function provideParserFunction(): bool {
		return false;
	}

}
