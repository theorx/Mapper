<?php
namespace Tests\Mapper;

use \Mapper\Mapper;

/**
 * Class MapperTest
 * @author             Lauri Orgla <TheOrX@hotmail.com>
 * @package            Tests\Mapper
 * @coversDefaultClass Mapper\Mapper
 */
class MapperTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Reset state before every test
	 */
	public function setUp() {

		parent::setUp();
		Mapper::setCacheReadFunction(null);
		Mapper::setCacheSaveFunction(null);
	}

	/**
	 * @covers ::getMap
	 * @covers ::processProperties
	 * @covers ::processMethods
	 */
	public function testGetMapReturnsMap() {

		$map = Mapper::getMap('\Tests\Stubs\StubExampleClass');

		$this->assertArrayHasKey('\Tests\Stubs\StubExampleClass::test', $map);
		$this->assertArrayHasKey('\Tests\Stubs\StubExampleClass::$primaryProperty', $map);
	}

	/**
	 * @covers ::getMap
	 * @expectedException \Exception
	 */
	public function testGetMapThrowsExceptionInvalidClass() {

		$map = Mapper::getMap('\Invalid\Namespace\InvalidClass');
	}

	/**
	 * @covers ::getMap
	 * @covers ::setCacheSaveFunction
	 * @expectedException \Exception
	 */
	public function testGetMapUsesCacheSave() {

		Mapper::setCacheSaveFunction(function () {

			throw new \Exception('SaveFunctionException');
		});

		//Check if thrown exception is the one we set in Saving function
		try {
			$map = Mapper::getMap('\Tests\Stubs\StubExampleClass');
		} catch (\Exception $exception) {
			$this->assertEquals('SaveFunctionException', $exception->getMessage());
			throw $exception;
		}
	}

	/**
	 * @covers ::getMap
	 * @covers ::setCacheReadFunction
	 * @expectedException \Exception
	 */
	public function testGetMapUsesCacheRead() {

		Mapper::setCacheReadFunction(function () {

			throw new \Exception('ReadFunctionException');
		});

		//Check if thrown exception is the one we set in Read function
		try {
			$map = Mapper::getMap('\Tests\Stubs\StubExampleClass');
		} catch (\Exception $exception) {
			$this->assertEquals('ReadFunctionException', $exception->getMessage());
			throw $exception;
		}
	}

	/**
	 * @covers ::getMap
	 * @covers ::setCacheReadFunction
	 */
	public function testGetMapReturnCachedResponse() {

		Mapper::setCacheReadFunction(function () {

			return ['my' => 'result'];
		});

		$this->assertEquals(['my' => 'result'], Mapper::getMap('\Tests\Stubs\StubExampleClass'));
	}

	/**
	 * @covers ::getCacheTimeout
	 * @covers ::setCacheTimeout
	 */
	public function testGetSetCacheTimeoutReturnsTimeout() {

		Mapper::setCacheTimeout(1337);
		$this->assertEquals(1337, Mapper::getCacheTimeout());
	}

	/**
	 * @covers ::setCacheReadFunction
	 * @covers ::getReadMethod
	 */
	public function testGetSetCacheReadFunction() {

		$function = function () {

			return true;
		};

		Mapper::setCacheReadFunction($function);
		$this->assertEquals($function, Mapper::getReadMethod());
	}

	/**
	 * @covers ::setCacheSaveFunction
	 * @covers ::getSaveMethod
	 */
	public function testGetSetCacheSaveFunction() {

		$function = function () {

			return false;
		};

		Mapper::setCacheSaveFunction($function);
		$this->assertEquals($function, Mapper::getSaveMethod());
	}

	/**
	 * @covers ::getMethodMeta
	 * @covers ::getMetaByTypeAndName
	 */
	public function testGetMethodMetaReturnsMetadata() {

		$result = Mapper::getMethodMeta('\Tests\Stubs\StubExampleClass', 'test');
		$this->assertArrayHasKey('parameters', $result);
		$this->assertArrayHasKey('tags', $result);
	}

	/**
	 * @covers ::getPropertyMeta
	 * @covers ::getMetaByTypeAndName
	 */
	public function testGetPropertyMetaReturnsMetadata() {

		$result = Mapper::getPropertyMeta('\Tests\Stubs\StubExampleClass', 'primaryProperty');
		$this->assertArrayHasKey('tags', $result);
	}


	/**
	 * @covers ::getPropertyMeta
	 * @covers ::getMetaByTypeAndName
	 */
	public function testGetPropertyMetaReturnsFalse() {

		$result = Mapper::getPropertyMeta('\Tests\Stubs\StubExampleClass', 'invalidProperty');
		$this->assertFalse($result);
	}
}
