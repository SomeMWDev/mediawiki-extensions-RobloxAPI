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
use MediaWiki\Extension\RobloxAPI\data\cache\DataSourceCache;
use MediaWiki\Extension\RobloxAPI\data\cache\EmptyCache;
use MediaWiki\Extension\RobloxAPI\data\cache\SimpleExpiringCache;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIException;
use MediaWiki\Extension\RobloxAPI\util\RobloxAPIUtil;
use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

/**
 * A data source represents an endpoint of the roblox api.
 */
abstract class DataSource {

	/**
	 * @var string The ID of this data source.
	 */
	public string $id;
	/**
	 * @var DataSourceCache The cache of this data source.
	 */
	protected DataSourceCache $cache;
	/**
	 * @var Config The extension configuration.
	 */
	protected Config $config;
	/**
	 * @var array|string The expected argument types.
	 */
	protected array $expectedArgs;
	/**
	 * @var ?HttpRequestFactory The HTTP request factory. Can be overridden for testing.
	 */
	private ?HttpRequestFactory $httpRequestFactory;

	/**
	 * Constructs a new data source.
	 * @param string $id The ID of this data source.
	 * @param DataSourceCache $cache The cache of this data source.
	 * @param Config $config The extension configuration.
	 * @param array $expectedArgs The expected argument types.
	 */
	public function __construct(
		string $id, DataSourceCache $cache, Config $config, array $expectedArgs
	) {
		$this->id = $id;
		$this->cache = $cache;
		$this->config = $config;
		$this->expectedArgs = $expectedArgs;
	}

	public function setCacheExpiry( int $seconds ): void {
		$this->cache->setExpiry( $seconds );
	}

	/**
	 * Fetches data
	 * @param mixed ...$args
	 * @return mixed
	 * @throws RobloxAPIException if there are any errors during the process
	 */
	public function fetch( ...$args ) {
		// assure that we have the correct number of arguments
		RobloxAPIUtil::safeDestructure( $args, count( $this->expectedArgs ) );
		// validate the args
		RobloxAPIUtil::assertValidArgs( $this->expectedArgs, $args );
		RobloxAPIUtil::assertArgsAllowed( $this->config, $this->expectedArgs, $args );

		$endpoint = $this->getEndpoint( $args );
		$data = $this->getDataFromEndpoint( $endpoint, $args );

		$processedData = $this->processData( $data, $args );

		if ( !$processedData ) {
			throw new RobloxAPIException( 'robloxapi-error-invalid-data' );
		}

		return $processedData;
	}

	/**
	 * Fetches data from the given endpoint.
	 * @param string $endpoint The endpoint to fetch data from.
	 * @param array $args The arguments to use.
	 * @return mixed The fetched data.
	 * @throws RobloxAPIException if there are any errors during the process
	 */
	public function getDataFromEndpoint( string $endpoint, array $args ) {
		$cached_result = $this->cache->getResultForEndpoint( $endpoint, $args );

		if ( $cached_result !== null ) {
			return $cached_result;
		}

		$options = [];

		$userAgent = $this->config->get( 'RobloxAPIRequestUserAgent' );
		if ( $userAgent && $userAgent !== '' ) {
			$options['userAgent'] = $userAgent;
		}

		$this->processRequestOptions( $options, $args );

		$this->httpRequestFactory =
			$this->httpRequestFactory ?? MediaWikiServices::getInstance()->getHttpRequestFactory();
		$request = $this->httpRequestFactory->create( $endpoint, $options );
		$request->setHeader( 'Accept', 'application/json' );

		$headers = $this->getAdditionalHeaders( $args );
		foreach ( $headers as $header => $value ) {
			$request->setHeader( $header, $value );
		}

		$status = $request->execute();

		if ( !$status->isOK() ) {
			$logger = LoggerFactory::getInstance( 'RobloxAPI' );
			$errors = $status->getErrorsByType( 'error' );
			$logger->warning( 'Failed to fetch data from Roblox API', [
				'endpoint' => $endpoint,
				'errors' => $errors,
				'status' => $status->getStatusValue(),
				'content' => $request->getContent(),
			] );
		}

		$json = $request->getContent();

		if ( !$status->isOK() || $json === null ) {
			// TODO try to fetch from cache
			throw new RobloxAPIException( 'robloxapi-error-request-failed' );
		}

		$data = FormatJson::decode( $json );

		if ( $data === null ) {
			throw new RobloxAPIException( 'robloxapi-error-decode-failure' );
		}

		$this->cache->registerCacheEntry( $endpoint, $data, $args );

		return $data;
	}

	/**
	 * Returns the endpoint of this data source for the given arguments.
	 * @param mixed $args The arguments to use.
	 * @return string The endpoint of this data source.
	 */
	abstract public function getEndpoint( $args ): string;

	/**
	 * Processes the data before returning it.
	 * @param mixed $data The data to process.
	 * @param mixed $args The arguments used to fetch the data.
	 * @return mixed The processed data.
	 * @throws RobloxAPIException if there are any errors during the process
	 */
	public function processData( $data, $args ) {
		return $data;
	}

	/**
	 * Processes the request options before making the request. This allows modifying the request options.
	 * @param array &$options The options to process.
	 * @param array $args The arguments used to fetch the data.
	 */
	public function processRequestOptions( array &$options, array $args ) {
	}

	/**
	 * Creates a simple expiring cache. If we're in a unit test environment, an empty cache is created.
	 * @return DataSourceCache The created cache.
	 */
	protected static function createSimpleCache(): DataSourceCache {
		global $wgRobloxAPIDisableCache;
		if ( defined( 'MW_PHPUNIT_TEST' ) || $wgRobloxAPIDisableCache ) {
			// we're either in a unit test environment or the cache is disabled
			return new EmptyCache();
		}

		return new SimpleExpiringCache();
	}

	/**
	 * Allows specifying additional headers for the request.
	 * @param array $args The arguments used to fetch the data.
	 * @return array The additional headers.
	 */
	protected function getAdditionalHeaders( array $args ): array {
		return [];
	}

	/**
	 * Returns whether this data source should provide a parser function.
	 * @return bool Whether this data source should provide a parser function.
	 */
	public function provideParserFunction(): bool {
		return true;
	}

	/**
	 * Sets the HTTP request factory.
	 * @param HttpRequestFactory $httpRequestFactory The HTTP request factory.
	 */
	public function setHttpRequestFactory( HttpRequestFactory $httpRequestFactory ): void {
		$this->httpRequestFactory = $httpRequestFactory;
	}

}
