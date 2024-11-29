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

namespace MediaWiki\Extension\RobloxAPI\parserFunction;

use MediaWiki\Extension\RobloxAPI\data\source\DataSourceProvider;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;

/**
 * Gets the amount of active players for a game in a universe.
 */
class ActivePlayersParserFunction extends RobloxApiParserFunction {

	public function __construct( DataSourceProvider $dataSourceProvider ) {
		parent::__construct( $dataSourceProvider );
	}

	/**
	 * Executes the parser function.
	 * @param \Parser $parser
	 * @param string $universeId
	 * @param string $gameId
	 * @return string
	 * @throws RobloxAPIException
	 */
	public function exec( $parser, $universeId = '', $gameId = '' ) {
		$source = $this->dataSourceProvider->getDataSource( 'gameData' );

		if ( !$source ) {
			throw new RobloxAPIException( 'robloxapi-error-datasource-not-found', 'gameData' );
		}

		$gameData = $source->fetch( $universeId, $gameId );

		if ( !$gameData ) {
			throw new RobloxAPIException( 'robloxapi-error-datasource-returned-no-data' );
		}

		return $gameData->playing;
	}

}
