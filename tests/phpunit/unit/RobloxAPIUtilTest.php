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

use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil;

/**
 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil
 * @group RobloxAPI
 */
class RobloxAPIUtilTest extends \MediaWikiUnitTestCase {

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::isValidId
	 */
	public function testIsValidId(): void {
		self::assertFalse( RobloxAPIUtil::isValidId( null ) );
		self::assertFalse( RobloxAPIUtil::isValidId( "" ) );
		self::assertFalse( RobloxAPIUtil::isValidId( "a" ) );
		self::assertFalse( RobloxAPIUtil::isValidId( "2412a4214" ) );
		self::assertFalse( RobloxAPIUtil::isValidId( "309713598a" ) );
		self::assertFalse( RobloxAPIUtil::isValidId( "4848492840912840912840921842019481" ) );
		self::assertFalse( RobloxAPIUtil::isValidId( "-1234" ) );

		self::assertTrue( RobloxAPIUtil::isValidId( "1" ) );
		self::assertTrue( RobloxAPIUtil::isValidId( "4182456156" ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::areValidIds
	 */
	public function testAreValidIds(): void {
		self::assertFalse( RobloxAPIUtil::areValidIds( [ null ] ) );
		self::assertFalse( RobloxAPIUtil::areValidIds( [ "a" ] ) );
		self::assertFalse( RobloxAPIUtil::areValidIds( [ "123", "b" ] ) );

		self::assertTrue( RobloxAPIUtil::areValidIds( [] ) );
		self::assertTrue( RobloxAPIUtil::areValidIds( [ "12345" ] ) );
		self::assertTrue( RobloxAPIUtil::areValidIds( [ "23598", "12345" ] ) );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::assertValidIds
	 */
	public function testAssertValidIds(): void {
		$this->expectException( RobloxAPIException::class );
		RobloxAPIUtil::assertValidIds( "abc" );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::safeDestructure
	 */
	public function testSafeDestructure(): void {
		self::assertEquals( [ 'test', 'some value' ], RobloxAPIUtil::safeDestructure( [ 'test', 'some value' ], 2 ) );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-args-count' );
		RobloxAPIUtil::safeDestructure( [ 'test', 'some value' ], 1 );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::assertArgsAllowed
	 */
	public function testAssertArgsAllowed(): void {
		$config = new \HashConfig( [
			'RobloxAPIAllowedArguments' => [
				'UserID' => [ '123454321' ],
				'GroupID' => [],
			],
		] );

		RobloxAPIUtil::assertArgsAllowed( $config, [ 'UserID' ], [ '123454321' ] );
		RobloxAPIUtil::assertArgsAllowed( $config, [ 'UserID', 'GroupID' ], [ '123454321', '14981124' ] );
		RobloxAPIUtil::assertArgsAllowed( $config, [ 'GroupID', 'GroupID' ], [ '512512312', '901480124' ] );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-arg-not-allowed' );
		RobloxAPIUtil::assertArgsAllowed( $config, [ 'UserID' ], [ '54321' ] );
		RobloxAPIUtil::assertArgsAllowed( $config, [ 'UserID', 'GroupID' ], [ '12345', '54321' ] );
	}

	/**
	 * @covers \MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil::assertValidArgs
	 */
	public function testAssertValidArgs(): void {
		RobloxAPIUtil::assertValidArgs( [ 'UserID' ], [ '123454321' ] );
		RobloxAPIUtil::assertValidArgs( [ 'ThumbnailSize' ], [ '140x140' ] );
		RobloxAPIUtil::assertValidArgs( [ 'Username' ], [ 'builderman_123' ] );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-thumbnail-size' );
		RobloxAPIUtil::assertValidArgs( [ 'ThumbnailSize' ], [ '12345' ] );

		$this->expectException( RobloxAPIException::class );
		$this->expectExceptionMessage( 'robloxapi-error-invalid-username' );
		RobloxAPIUtil::assertValidArgs( [ 'Username' ], [ '__invalidusername' ] );
	}

}
