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

use MediaWiki\Config\Config;
use MediaWiki\Extension\RobloxAPI\data\source\UserIdDataSource;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\data\source\UserIdDataSource
 * @group RobloxAPI
 */
class UserIdDataSourceTest extends RobloxAPIDataSourceUnitTestCase {

	private UserIdDataSource $subject;

	protected function setUp(): void {
		$this->subject = new UserIdDataSource( $this->createMock( Config::class ) );
	}

	public function testProcessData() {
		$data = (object)[
			'data' => [
				(object)[
					'userId' => 12345,
				],
			],
		];
		self::assertEquals( $data->data[0], $this->subject->processData( $data, [ 'username' ] ) );

		// test invalid data
		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-data' );
		$this->subject->processData( (object)[ 'data' => null ], [ 'username' ] );
	}

	public function testFetch() {
		$result = /** @lang JSON */
			<<<EOD
		{
			"data": [
				{
					"requestedUsername": "abaddriverlol",
					"hasVerifiedBadge": false,
					"id": 4182456156,
					"name": "abaddriverlol",
					"displayName": "abaddriverlol"
				}
			]
		}
		EOD;

		$dataSource = new UserIdDataSource( $this->createMock( Config::class ) );
		$dataSource->setHttpRequestFactory( $this->createMockHttpRequestFactory( $result ) );

		$data = $dataSource->fetch( 'abaddriverlol' );

		self::assertEquals( 4182456156, $data->id );
		self::assertEquals( 'abaddriverlol', $data->name );
	}

	public function testFetchEmptyResult() {
		$result = /** @lang JSON */
			<<<EOD
		{
			"data": []
		}
		EOD;

		$dataSource = new UserIdDataSource( $this->createMock( Config::class ) );
		$dataSource->setHttpRequestFactory( $this->createMockHttpRequestFactory( $result ) );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-data' );
		$dataSource->fetch( 'thisuserdoesntexist' );
	}

	public function testFailedRequest() {
		$dataSource = new UserIdDataSource( $this->createMock( Config::class ) );
		$dataSource->setHttpRequestFactory( $this->createMockHttpRequestFactory( null, 429 ) );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-request-failed' );
		$dataSource->fetch( 'thisrequestwillfail' );
	}

	public function testProcessRequestOptions() {
		$dataSource = new UserIdDataSource( $this->createMock( Config::class ) );
		$options = [];
		$args = [ 'example_user' ];
		$dataSource->processRequestOptions( $options, $args );

		self::assertEquals( 'POST', $options['method'] );
		self::assertEquals( '{"usernames":["example_user"]}', $options['postData'] );
	}

}
