<?php
namespace Mapper;

use Reflectionist\Factory\Factory;

/**
 * Class Mapper
 *
 * This class could be used to benefit from the power of docblock annotation
 *
 * Example usage:
 *
 * $result = Mapper::getPropertyMeta('FQN/Class/Name', 'propertyName');
 * //Returns metadata about that property
 * $result = Mapper::getMethodMeta('FQN/Class/Name', 'methodName');
 * //Returns metadata about that method
 *
 * @author  Lauri Orgla <TheOrX@hotmail.com>
 * @package Mapper
 */
class Mapper {

	/**
	 * @var Callable
	 */
	private static $saveMethod = null;
	/**
	 * @var Callable
	 */
	private static $readMethod = null;
	/**
	 * @var int
	 */
	private static $cacheTimeout = 60;


	/**
	 * getMap returns the whole map for metadata from class name
	 *
	 * @author Lauri Orgla <TheOrX@hotmail.com>
	 *
	 * @param $className
	 *
	 * @throws \Exception
	 * @return array
	 */
	public static function getMap($className) {

		$cachedResult = null;
		self::readCache($className, $cachedResult);
		if (!$cachedResult) {
			self::checkExternalDependencies();
			$analyzer = Factory::getAnalyzer();
			$result   = [];

			try {
				$analyzerResult = $analyzer->addClass($className)->analyze()->getResult()[$className];
			} catch (\Exception $e) {
				throw new \Exception('Mapper Exception. Invalid class given');
			}

			self::processMethods($className, $analyzerResult, $result);
			self::processProperties($className, $analyzerResult, $result);
			self::saveCache($className, $result);

			return $result;
		}

		return $cachedResult;
	}

	/**
	 *
	 * getMethodMeta returns method metadata from given class and method name
	 *
	 * @author Lauri Orgla <TheOrX@hotmail.com>
	 *
	 * @param $className
	 * @param $methodName
	 *
	 * @return bool
	 */
	public static function getMethodMeta($className, $methodName) {

		return self::getMetaByTypeAndName($className, 'method', $methodName);
	}

	/**
	 * getPropertyMeta returns property metadata from given class and property name
	 *
	 * @author Lauri Orgla <TheOrX@hotmail.com>
	 *
	 * @param $className
	 * @param $propertyName
	 *
	 * @return bool
	 */
	public static function getPropertyMeta($className, $propertyName) {

		return self::getMetaByTypeAndName($className, 'property', $propertyName);
	}

	/**
	 * Internal method to avoid code duplication.
	 *
	 * @author Lauri Orgla <TheOrX@hotmail.com>
	 *
	 * @param $className
	 * @param $type
	 * @param $name
	 *
	 * @return bool
	 */
	private static function getMetaByTypeAndName($className, $type, $name) {

		$map = self::getMap($className);

		$keyName = $className . '::' . (($type == 'property') ? '$' : null) . $name;
		if (isset($map[$keyName])) {
			return $map[$keyName];
		}

		return false;
	}

	/**
	 * This method is for processing properties and returning array of processed properties
	 * This is internal method, no public access.
	 *
	 * @author Lauri Orgla <TheOrX@hotmail.com>
	 *
	 * @param $className
	 * @param $analyzerResult
	 * @param $result
	 */
	private static function processProperties($className, $analyzerResult, &$result) {

		if (isset($analyzerResult['properties'])) {
			foreach ($analyzerResult['properties'] as $property) {
				$result[$className . '::$' . $property['name']] = [
					'tags' => isset($property['phpdoc']['tags']) ? $property['phpdoc']['tags'] : null
				];
			}
		}
	}

	/**
	 * This method is for processing methods and returning array of processed methods
	 * This is internal method, no public access.
	 *
	 * @author Lauri Orgla <TheOrX@hotmail.com>
	 *
	 * @param $className
	 * @param $analyzerResult
	 * @param $result
	 */
	private static function processMethods($className, &$analyzerResult, &$result) {

		if (isset($analyzerResult['methods'])) {
			foreach ($analyzerResult['methods'] as $method) {
				$result[$className . '::' . $method['name']] = [
					'parameters' => isset($method['parameters']) ? $method['parameters'] : null,
					'tags'       => isset($method['phpdoc']['tags']) ? $method['phpdoc']['tags'] : null
				];
			}
		}
	}

	/**
	 * readCache
	 *
	 * This functions is used for reading from cache.
	 * Actual implementation for reading can be defined by user as a callback
	 * and set via setCacheReadFunction()
	 *
	 * @author Lauri Orgla <TheOrX@hotmail.com>
	 *
	 * @param $className
	 * @param $cachedResult
	 */
	private static function readCache($className, &$cachedResult) {

		if (is_callable(self::getReadMethod())) {
			$callback     = self::getReadMethod();
			$cachedResult = $callback($className, self::$cacheTimeout);
		}
	}

	/**
	 * saveCache
	 * Calls internal callable function to handle saving. Function for saving
	 * could be defined by the user via setCacheSaveFunction();
	 *
	 * @author Lauri Orgla <TheOrX@hotmail.com>
	 *
	 * @param $className
	 * @param $result
	 */
	private static function saveCache($className, $result) {

		if (is_callable(self::getSaveMethod())) {
			$callback = self::getSaveMethod();
			$callback($className, $result, self::$cacheTimeout);
		}
	}

	/**
	 * @author Lauri Orgla <TheOrX@hotmail.com>
	 *
	 * This internal method checks if external libs are loaded
	 * If libs are not loaded, then throws exception
	 *
	 * @throws \Exception
	 */
	private static function checkExternalDependencies() {

		if (!class_exists('Reflectionist\Factory\Factory')) {
			throw new \Exception(
				'Dependency missing: Reflectionist\Factory\Factory class. Please load Reflectionist library.'
			);
		}
	}

	/**
	 * setCacheSaveFunctions sets a callable to handle cache saving
	 *
	 * @author Lauri Orgla <TheOrX@hotmail.com>
	 *
	 * Example: Mapper::setCacheSaveFunction(function($className, $data, $ttl){
	 *        //Call your caching interface from here
	 *        //Or save the results to database/disk
	 *
	 *        return true;
	 * });
	 *
	 * @param callable $saveMethod
	 */
	public static function setCacheSaveFunction($saveMethod) {

		self::$saveMethod = $saveMethod;
	}

	/**
	 * setCacheReadFunction sets a callable to handle cache reading and validating TTL
	 *
	 * @author Lauri Orgla <TheOrX@hotmail.com>
	 *
	 * Example: Mapper::setCacheReadFunction(function($className, $ttl){
	 *        //Call your caching interface from here
	 *        //Or read the results from database/disk
	 *        //And validate TTL
	 *        //If the cache is invalid / outdated then return false.
	 *
	 *        return false;
	 * });
	 *
	 * @param callable $readMethod
	 */
	public static function setCacheReadFunction($readMethod) {

		self::$readMethod = $readMethod;
	}

	/**
	 * @param $seconds
	 */
	public static function setCacheTimeout($seconds) {

		self::$cacheTimeout = $seconds;
	}

	/**
	 * @return int
	 */
	public static function getCacheTimeout() {

		return self::$cacheTimeout;
	}

	/**
	 * @return Callable
	 */
	public static function getReadMethod() {

		return self::$readMethod;
	}

	/**
	 * @return Callable
	 */
	public static function getSaveMethod() {

		return self::$saveMethod;
	}
}
