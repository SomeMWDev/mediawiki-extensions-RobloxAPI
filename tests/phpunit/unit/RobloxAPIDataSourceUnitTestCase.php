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

namespace MediaWiki\Extension\RobloxAPI\Tests;

use GuzzleHttpRequest;
use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\Status\Status;
use MediaWikiUnitTestCase;
use StatusValue;

/**
 * Base class for Roblox API unit tests.
 */
abstract class RobloxAPIDataSourceUnitTestCase extends MediaWikiUnitTestCase {

	/**
	 * @param ?string $returnedContent Content to be returned by the request
	 * @param int $status HTTP status code to be returned by the request
	 * @return HttpRequestFactory Mocked HTTP request factory
	 */
	protected function createMockHttpRequestFactory( ?string $returnedContent, int $status = 200 ): HttpRequestFactory {
		$request = $this->createPartialMock( GuzzleHttpRequest::class, [ 'execute', 'getContent' ] );

		if ( $status > 0 && $status < 400 ) {
			$requestStatus = StatusValue::newGood( $status );
		} else {
			$requestStatus = StatusValue::newFatal( $status );
		}

		$request->expects( $this->once() )->method( 'execute' )->willReturn( Status::wrap( $requestStatus ) );

		if ( $returnedContent ) {
			$request->expects( $this->once() )->method( 'getContent' )->willReturn( $returnedContent );
		} else {
			$request->expects( $this->atMost( 2 ) )->method( 'getContent' )->willReturn( '' );
		}

		$httpRequestFactory = $this->createPartialMock( HttpRequestFactory::class, [ 'create' ] );
		$httpRequestFactory->expects( $this->once() )->method( 'create' )->willReturn( $request );

		return $httpRequestFactory;
	}

}
