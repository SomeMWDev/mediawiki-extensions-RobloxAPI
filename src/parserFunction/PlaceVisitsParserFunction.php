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
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil;

/**
 * Gets the amount of visits for a game in a universe.
 */
class PlaceVisitsParserFunction extends RobloxApiParserFunction {

	public function __construct( DataSourceProvider $dataSourceProvider ) {
		parent::__construct( $dataSourceProvider );
	}

	/**
	 * @inheritDoc
	 */
	public function exec( $parser, ...$args ): string {
		[ $universeId, $gameId ] = RobloxAPIUtil::safeDestructure( $args, 2 );

		$source = $this->dataSourceProvider->getDataSourceOrThrow( 'gameData' );

		$gameData = $source->fetch( $universeId, $gameId );

		if ( !$gameData ) {
			throw new RobloxAPIException( 'robloxapi-error-datasource-returned-no-data' );
		}

		return $gameData->visits;
	}

}
