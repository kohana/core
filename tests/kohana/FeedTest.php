<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Test for feed helper
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.feed
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_FeedTest extends Unittest_TestCase
{

	/**
	 * Sets up the environment
	 */
	// @codingStandardsIgnoreStart
	public function setUp()
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();
		Kohana::$config->load('url')->set('trusted_hosts', array('localhost'));
	}

	/**
	 * Provides test data for test_parse()
	 *
	 * @return array
	 */
	public function provider_parse()
	{
		return array(
			// $source, $expected
			array(realpath(__DIR__.'/../test_data/feeds/activity.atom'), array('Proposals (Political/Workflow) #4839 (New)', 'Proposals (Political/Workflow) #4782')),
			array(realpath(__DIR__.'/../test_data/feeds/example.rss20'), array('Example entry')),
		);
	}

	/**
	 * Tests that Feed::parse gets the correct number of elements
	 *
	 * @test
	 * @dataProvider provider_parse
	 * @covers feed::parse
	 * @param string  $source   URL to test
	 * @param integer $expected Count of items
	 */
	public function test_parse($source, $expected_titles)
	{
		$titles = array();
		foreach (Feed::parse($source) as $item)
		{
			$titles[] = $item['title'];
		}

		$this->assertSame($expected_titles, $titles);
	}

	/**
	 * Provides test data for test_create()
	 *
	 * @return array
	 */
	public function provider_create()
	{
		$info = array('pubDate' => 123, 'image' => array('link' => 'http://kohanaframework.org/image.png', 'url' => 'http://kohanaframework.org/', 'title' => 'title'));

		return array(
			// $source, $expected
			array($info, array('foo' => array('foo' => 'bar', 'pubDate' => 123, 'link' => 'foo')), array('_SERVER' => array('HTTP_HOST' => 'localhost')+$_SERVER),
				array(
					'tag' => 'channel',
					'descendant' => array(
						'tag' => 'item',
						'child' => array(
							'tag' => 'foo',
							'content' => 'bar'
						)
					)
				),
				array(
					$this->matcher_composer($info, 'image', 'link'),
					$this->matcher_composer($info, 'image', 'url'),
					$this->matcher_composer($info, 'image', 'title')
				)
			),
		);
	}

	/**
	 * Helper for handy matcher composing
	 *
	 * @param array $data
	 * @param string $tag
	 * @param string $child
	 * @return array
	 */
	private function matcher_composer($data, $tag, $child)
	{
		return array(
			'tag' => 'channel',
			'descendant' => array(
				'tag' => $tag,
				'child' => array(
					'tag' => $child,
					'content' => $data[$tag][$child]
				)
			)
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider provider_create
	 *
	 * @covers feed::create
	 *
	 * @param string  $info     info to pass
	 * @param integer $items    items to add
	 * @param integer $matcher  output
	 */
	public function test_create($info, $items, $enviroment, $matcher_item, $matchers_image)
	{
		$this->setEnvironment($enviroment);

		$this->assertTag($matcher_item, Feed::create($info, $items), '', FALSE);

		foreach ($matchers_image as $matcher_image)
		{
			$this->assertTag($matcher_image, Feed::create($info, $items), '', FALSE);
		}
	}
}
