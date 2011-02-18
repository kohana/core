<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Description of RouteTest
 *
 * @group kohana
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_RouteTest extends Kohana_Unittest_TestCase
{
	/**
	 * Remove all caches
	 */
	public function setUp()
	{
		parent::setUp();

		$this->cleanCacheDir();
	}

	/**
	 * Removes cache files created during tests
	 */
	public function tearDown()
	{
		parent::tearDown();

		$this->cleanCacheDir();
	}

	/**
	 * If Route::get() is asked for a route that does not exist then
	 * it should throw a Kohana_Exception
	 *
	 * Note use of @expectedException
	 *
	 * @test
	 * @covers Route::get
	 * @expectedException Kohana_Exception
	 */
	public function test_get_throws_exception_if_route_dnx()
	{
		Route::get('HAHAHAHAHAHAHAHAHA');
	}

	/**
	 * Route::all() should return all routes defined via Route::set()
	 * and not through new Route()
	 *
	 * @test
	 * @covers Route::all
	 */
	public function test_all_returns_all_defined_routes()
	{
		$defined_routes = self::readAttribute('Route', '_routes');

		$this->assertSame($defined_routes, Route::all());
	}

	/**
	 * Route::name() should fetch the name of a passed route
	 * If route is not found then it should return FALSE
	 *
	 * @TODO: This test needs to segregate the Route::$_routes singleton
	 * @test
	 * @covers Route::name
	 */
	public function test_name_returns_routes_name_or_false_if_dnx()
	{
		$route = Route::set('flamingo_people', 'flamingo/dance');
		
		$this->assertSame('flamingo_people', Route::name($route));

		$route = new Route('dance/dance');

		$this->assertFalse(Route::name($route));
	}

	/**
	 * If Route::cache() was able to restore routes from the cache then
	 * it should return TRUE and load the cached routes
	 *
	 * @test
	 * @covers Route::cache
	 */
	public function test_cache_stores_route_objects()
	{
		$routes = Route::all();

		// First we create the cache
		Route::cache(TRUE);

		// Now lets modify the "current" routes
		Route::set('nonsensical_route', 'flabbadaga/ding_dong');

		// Then try and load said cache
		$this->assertTrue(Route::cache());

		// And if all went ok the nonsensical route should be gone...
		$this->assertEquals($routes, Route::all());
	}

	/**
	 * Route::cache() should return FALSE if cached routes could not be found
	 *
	 * The cache is cleared before and after each test in setUp tearDown 
	 * by cleanCacheDir()
	 * 
	 * @test
	 * @covers Route::cache
	 */
	public function test_cache_returns_false_if_cache_dnx()
	{
		$this->assertSame(FALSE, Route::cache(), 'Route cache was not empty');
	}

	/**
	 * If the constructor is passed a NULL uri then it should assume it's
	 * being loaded from the cache & therefore shouldn't override the cached attributes
	 *
	 * @test
	 * @covers Route::__construct
	 */
	public function test_constructor_returns_if_uri_is_null()
	{
		// We use a mock object to make sure that the route wasn't recompiled
		$route = $this->getMock('Route', array('_compile'), array(), '', FALSE);

		$route
			->expects($this->never())
			->method('_compile');

		$route->__construct(NULL,NULL);

		$this->assertAttributeSame('', '_uri', $route);
		$this->assertAttributeSame(array(), '_regex', $route);
		$this->assertAttributeSame(array('action' => 'index'), '_defaults', $route);
		$this->assertAttributeSame(NULL, '_route_regex', $route);
	}

	/**
	 * The constructor should only use custom regex if passed a non-empty array
	 *
	 * Technically we can't "test" this as the default regex is an empty array, this
	 * is purely for improving test coverage
	 *
	 * @test
	 * @covers Route::__construct
	 */
	public function test_constructor_only_changes_custom_regex_if_passed()
	{
		$route = new Route('<controller>/<action>', array());

		$this->assertAttributeSame(array(), '_regex', $route);

		$route = new Route('<controller>/<action>', NULL);

		$this->assertAttributeSame(array(), '_regex', $route);
	}

	/**
	 * When we pass custom regex to the route's constructor it should it
	 * in leu of the default
	 *
	 * @test
	 * @covers Route::__construct
	 * @covers Route::_compile
	 */
	public function test_route_uses_custom_regex_passed_to_constructor()
	{
		$regex = array('id' => '[0-9]{1,2}');

		$route = new Route('<controller>(/<action>(/<id>))', $regex);

		$this->assertAttributeSame($regex, '_regex', $route);
		$this->assertAttributeContains(
			$regex['id'],
			'_route_regex',
			$route
		);
	}

	/**
	 * Route::matches() should return false if the route doesn't match against a uri
	 *
	 * @test
	 * @covers Route::matches
	 */
	public function test_matches_returns_false_on_failure()
	{
		$route = new Route('projects/(<project_id>/(<controller>(/<action>(/<id>))))');

		$this->assertSame(FALSE, $route->matches('apple/pie'));
	}

	/**
	 * Route::matches() should return an array of parameters when a match is made
	 * An parameters that are not matched should not be present in the array of matches
	 *
	 * @test
	 * @covers Route::matches
	 */
	public function test_matches_returns_array_of_parameters_on_successful_match()
	{
		$route = new Route('(<controller>(/<action>(/<id>)))');

		$matches = $route->matches('welcome/index');

		$this->assertInternalType('array', $matches);
		$this->assertArrayHasKey('controller', $matches);
		$this->assertArrayHasKey('action', $matches);
		$this->assertArrayNotHasKey('id', $matches);
		$this->assertSame(2, count($matches));
		$this->assertSame('welcome', $matches['controller']);
		$this->assertSame('index', $matches['action']);
	}

	/**
	 * Defaults specified with defaults() should be used if their values aren't
	 * present in the uri
	 *
	 * @test
	 * @covers Route::matches
	 */
	public function test_defaults_are_used_if_params_arent_specified()
	{
		$route = new Route('(<controller>(/<action>(/<id>)))');
		$route->defaults(array('controller' => 'welcome', 'action' => 'index'));

		$matches = $route->matches('');

		$this->assertInternalType('array', $matches);
		$this->assertArrayHasKey('controller', $matches);
		$this->assertArrayHasKey('action', $matches);
		$this->assertArrayNotHasKey('id', $matches);
		$this->assertSame(2, count($matches));
		$this->assertSame('welcome', $matches['controller']);
		$this->assertSame('index', $matches['action']);
		$this->assertSame('unit/test/1', $route->uri(array(
			'controller' => 'unit',
			'action' => 'test',
			'id' => '1'
		)));
		$this->assertSame('welcome/index', $route->uri());
	}

	/**
	 * This tests that routes with required parameters will not match uris without them present
	 *
	 * @test
	 * @covers Route::matches
	 */
	public function test_required_parameters_are_needed()
	{
		$route = new Route('admin(/<controller>(/<action>(/<id>)))');

		$this->assertFalse($route->matches(''));

		$matches = $route->matches('admin');

		$this->assertInternalType('array', $matches);

		$matches = $route->matches('admin/users/add');

		$this->assertSame(2, count($matches));
		$this->assertInternalType('array', $matches);
		$this->assertArrayHasKey('controller', $matches);
		$this->assertArrayHasKey('action', $matches);
	}

	/**
	 * This tests the reverse routing returns the uri specified in the route
	 * if it's a static route
	 *
	 * A static route is a route without any parameters
	 *
	 * @test
	 * @covers Route::uri
	 */
	public function test_reverse_routing_returns_routes_uri_if_route_is_static()
	{
		$route = new Route('info/about_us');

		$this->assertSame('info/about_us', $route->uri(array('some' => 'random', 'params' => 'to confuse')));
	}

	/**
	 * When Route::uri is working on a uri that requires certain parameters to be present
	 * (i.e. <controller> in '<controller(/<action)') then it should throw an exception
	 * if the param was not provided
	 *
	 * @test
	 * @covers Route::uri
	 */
	public function test_uri_throws_exception_if_required_params_are_missing()
	{
		$route = new Route('<controller>(/<action)');

		try 
		{
			$route->uri(array('action' => 'awesome-action'));

			$this->fail('Route::uri should throw exception if required param is not provided');
		}
		catch(Exception $e)
		{
			$this->assertInstanceOf('Kohana_Exception', $e);
			// Check that the error in question is about the controller param
			$this->assertContains('controller', $e->getMessage());
		}
	}

	/**
	 * The logic for replacing required segments is separate (but similar) to that for
	 * replacing optional segments.
	 *
	 * This test asserts that Route::uri will replace required segments with provided
	 * params
	 *
	 * @test
	 * @covers Route::uri
	 */
	public function test_uri_fills_required_uri_segments_from_params()
	{
		$route = new Route('<controller>/<action>(/<id>)');

		$this->assertSame(
			'users/edit',
			$route->uri(array(
				'controller' => 'users',
				'action'     => 'edit',
			))
		);

		$this->assertSame(
			'users/edit/god',
			$route->uri(array(
				'controller' => 'users',
				'action'     => 'edit',
				'id'         => 'god',
			))
		);
	}

	/**
	 * Provides test data for test_composing_url_from_route()
	 * @return array
	 */
	public function provider_composing_url_from_route()
	{
		return array(
			array('/welcome'),
			array('/news/view/42', array('controller' => 'news', 'action' => 'view', 'id' => 42)),
			array('http://kohanaframework.org/news', array('controller' => 'news'), true)
		);
	}

	/**
	 * Tests Route::url()
	 *
	 * Checks the url composing from specific route via Route::url() shortcut
	 *
	 * @test
	 * @dataProvider provider_composing_url_from_route
	 * @param string $expected
	 * @param array $params
	 * @param boolean $protocol
	 */
	public function test_composing_url_from_route($expected, $params = NULL, $protocol = NULL)
	{
		Route::set('foobar', '(<controller>(/<action>(/<id>)))')
			->defaults(array(
				'controller' => 'welcome',
			)
		);

		$this->setEnvironment(array(
			'_SERVER' => array('HTTP_HOST' => 'kohanaframework.org'),
			'Kohana::$base_url' => '/',
			'Request::$protocol' => 'http',
			'Kohana::$index_file' => '',
		));

		$this->assertSame($expected, Route::url('foobar', $params, $protocol));
	}

	/**
	 * Tests Route::_compile()
	 *
	 * Makes sure that compile will use custom regex if specified
	 *
	 * @test
	 * @covers Route::_compile
	 */
	public function test_compile_uses_custom_regex_if_specificed()
	{
		$route = new Route(
			'<controller>(/<action>(/<id>))', 
			array(
				'controller' => '[a-z]+',
				'id' => '\d+',
			)
		);

		$this->assertAttributeSame(
			'#^(?P<controller>[a-z]+)(?:/(?P<action>[^/.,;?\n]++)(?:/(?P<id>\d+))?)?$#uD', 
			'_route_regex', 
			$route
		);
	}
}
