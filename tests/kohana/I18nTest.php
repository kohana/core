<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana i18n class
 *
 * @group kohana
 * @group kohana.i18n
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @author     Logan Aube <logan@bnotions.ca>
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_I18nTest extends Unittest_TestCase
{

	/**
	 * When an i18n object is initially created there should be
	 * no readers attached
	 *
	 * @test
	 * @covers I18n
	 */
	public function test_initially_there_are_no_sources()
	{
		$i18n = new I18n;

		$this->assertAttributeSame(array(), '_sources', $i18n);
	}

	/**
	 * Test that calling attach() on a kohana i18n object
	 * adds the specified reader to the i18n object
	 *
	 * @test
	 * @covers I18n::attach
	 */
	public function test_attach_adds_reader_and_returns_this()
	{
		$i18n = new I18n;
		$reader = $this->getMock('Kohana_I18n_Reader');
		
		$this->assertSame($i18n, $i18n->attach($reader));
		
		$this->assertAttributeContains($reader, '_sources', $i18n);
	}
	
	/**
	 * By default (or by passing TRUE as the second parameter) the i18n object
	 * should prepend the reader to the front of the readers queue
	 *
	 * @test
	 * @covers I18n::attach
	 */
	public function test_attach_adds_reader_to_front_of_queue()
	{
		$i18n = new I18n;

		$reader1 = $this->getMock('Kohana_I18n_Reader');
		$reader2 = $this->getMock('Kohana_I18n_Reader');

		$i18n->attach($reader1);
		$i18n->attach($reader2);

		// Rather than do two assertContains we'll do an assertSame to assert
		// the order of the readers
		$this->assertAttributeSame(array($reader2, $reader1), '_sources', $i18n);

		// Now we test using the second parameter
		$i18n = new I18n;

		$i18n->attach($reader1);
		$i18n->attach($reader2, TRUE);

		$this->assertAttributeSame(array($reader2, $reader1), '_sources', $i18n);
	}
	
	/**
	 * Test that attaching a new reader (and passing FALSE as second param) causes
	 * i18n to append the reader rather than prepend
	 *
	 * @test
	 * @covers I18n::attach
	 */
	public function test_attach_can_add_reader_to_end_of_queue()
	{
		$i18n = new I18n;
		
		$reader1 = $this->getMock('Kohana_I18n_Reader');
		$reader2 = $this->getMock('Kohana_I18n_Reader');

		$i18n->attach($reader1);
		$i18n->attach($reader2, FALSE);

		$this->assertAttributeSame(array($reader1, $reader2), '_sources', $i18n);
	}
	
	/**
	 * Calling detach() on an i18n object should remove it from the queue of readers
	 *
	 * @test
	 * @covers I18n::detach
	 */
	public function test_detach_removes_reader_and_returns_this()
	{
		$i18n = new I18n;

		// Due to the way phpunit mock generator works if you try and mock a class
		// that has already been used then it just re-uses the first's name

		// To get around this we have to specify a totally random name for the second mock object
		$reader1 = $this->getMock('Kohana_I18n_Reader');
		$reader2 = $this->getMock('Kohana_I18n_Reader', array(), array(), 'MY_AWESOME_READER');

		$i18n->attach($reader1);
		$i18n->attach($reader2);

		$this->assertSame($i18n, $i18n->detach($reader1));

		$this->assertAttributeNotContains($reader1, '_sources', $i18n);
		$this->assertAttributeContains($reader2, '_sources', $i18n);

		$this->assertSame($i18n, $i18n->detach($reader2));

		$this->assertAttributeNotContains($reader2, '_sources', $i18n);
	}

	/**
	 * detach() should return $this even if the specified reader does not exist
	 *
	 * @test
	 * @covers I18n::detach
	 */
	public function test_detach_returns_this_even_when_reader_dnx()
	{
		$i18n = new I18n;
		$reader = $this->getMock('Kohana_I18n_Reader');

		$this->assertSame($i18n, $i18n->detach($reader));
	}

	/**
	 * Provides test data for test_lang()
	 * 
	 * @return array
	 */
	public function provider_lang()
	{
		return array(
			// $value, $result
			array(NULL, 'en-us'),
			array('es-es', 'es-es'),
			array(NULL, 'es-es'),
		);
	}

	/**
	 * Tests i18n::lang()
	 *
	 * @test
	 * @dataProvider provider_lang
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	public function test_lang($input, $expected)
	{
		$i18n = new I18n;
		
		$this->assertSame($expected, $i18n->lang($input));
	}

	/**
	 * Provides test data for test_get()
	 * 
	 * @return array
	 */
	public function provider_get()
	{
		return array(
			// $value, $result
			array('en-us', 'Hello, world!', 'Hello, world!'),
			array('es-es', 'Hello, world!', '¡Hola, mundo!'),
			array('fr-fr', 'Hello, world!', 'Bonjour, monde!'),
		);
	}

	/**
	 * I18n sources are stored in a stack, make sure that translation table at the bottom
	 * of the stack is overriden by table at the top
	 *
	 * @test
	 * @covers Config::load
	 */
	public function test_table_is_loaded_from_top_to_bottom_of_stack()
	{
		$reader1 = $this->getMock('Kohana_I18n_Reader', array('load'), array(), 'Unittest_I18n_Reader_1');
		$reader2 = $this->getMock('Kohana_I18n_Reader', array('load'), array(), 'Unittest_I18n_Reader_2');

		$reader1
			->expects($this->once())
			->method('load')
			->with($group_name)
			->will($this->returnValue(array('hello world' => 'heeleoo worrrld')));

		$reader2
			->expects($this->once())
			->method('load')
			->with($group_name)
			->will($this->returnValue(array('hello world' => 'baaaaaaa')));

		$I18n = new I18n;
		
		$i18n->lang = 'fo-br';

		// Attach $reader1 at the "top" and reader2 at the "bottom"
		$$i18n->attach($reader1)->attach($reader2, FALSE);

		$this->assertSame('heeleoo worrrld', $i18n->get('hello world'));
	}

	/**
	 * Tests i18n::get()
	 *
	 * @test
	 * @dataProvider provider_get
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	public function test_get($lang, $input, $expected)
	{
		$i18n = new I18n;
		
		$i18n->lang($lang);
		$this->assertSame($expected, $i18n->get($input));

		// Test immediate translation, issue #3085
		$i18n->lang('en-us');
		$this->assertSame($expected, $i18n->get($input, $lang));
	}

}
