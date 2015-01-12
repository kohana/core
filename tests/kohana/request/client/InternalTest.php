<?php
namespace {
	use test\RequestClientInternalTest\ControllerCapturingResponseStub;
	use test\RequestClientInternalTest\FixedParametersRequestStub;

/**
 * Unit tests for internal request client
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.request
 * @group kohana.core.request.client
 * @group kohana.core.request.client.internal
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Request_Client_InternalTest extends Unittest_TestCase
{
	public function provider_response_failure_status()
	{
		return array(
			array('', 'Welcome', 'missing_action', 'Welcome/missing_action', 404),
			array('kohana3', 'missing_controller', 'index', 'kohana3/missing_controller/index', 404),
			array('', 'Template', 'missing_action', 'kohana3/Template/missing_action', 500),
		);
	}

	/**
	 * Tests for correct exception messages
	 *
	 * @test
	 * @dataProvider provider_response_failure_status
	 *
	 * @return null
	 */
	public function test_response_failure_status($directory, $controller, $action, $uri, $expected)
	{
		$request = new FixedParametersRequestStub(array(
			'directory'  => $directory,
			'controller' => $controller,
			'action'     => $action,
			'uri'        => $uri,
		));

		$internal_client = new Request_Client_Internal;
		$response = $internal_client->execute($request);
		$this->assertSame($expected, $response->status());
	}

	/**
	 * @return array
	 */
	public function provider_controller_class_mapping()
	{
		return array(
			array(
				array('controller' => 'RequestClientInternalTestControllerDummy', 'directory' => ''),
				'Controller_RequestClientInternalTestControllerDummy',
			),
			array(
				array('controller' => 'ControllerDummy', 'directory' => 'RequestClientInternalTest'),
				'Controller_RequestClientInternalTest_ControllerDummy',
			),
			array(
				array('controller' => '\RequestClientInternalTestControllerDummy', 'directory' => ''),
				'\RequestClientInternalTestControllerDummy',
			),
			array(
				array('controller' => '\test\RequestClientInternalTest\ControllerDummy', 'directory' => ''),
				'\test\RequestClientInternalTest\ControllerDummy',
			),
		);
	}

	/**
	 * @param array $request_params
	 * @param string $expect_class
	 *
	 * @dataProvider provider_controller_class_mapping
	 */
	public function test_maps_request_params_to_controller_class($request_params, $expect_class)
	{
		$client = new Request_Client_Internal;
		$request = new FixedParametersRequestStub($request_params);
		$response = new ControllerCapturingResponseStub;
		$client->execute_request($request, $response);

		$this->assertInstanceOf($expect_class, $response->getController());
	}
}

	class RequestClientInternalTestControllerDummy extends Controller {

		public function execute()
		{
			if ($this->response instanceof ControllerCapturingResponseStub)
			{
				$this->response->setController($this);
			}
			return $this->response;
		}

	}

	class Controller_RequestClientInternalTest_ControllerDummy extends \RequestClientInternalTestControllerDummy {}
	class Controller_RequestClientInternalTestControllerDummy extends \RequestClientInternalTestControllerDummy {}

} // End of global namespace

namespace test\RequestClientInternalTest {

	class FixedParametersRequestStub extends \Request {

		public function __construct($params)
		{
			$params            = array_merge(
				array(
					'directory'  => '',
					'controller' => '',
					'action'     => 'index',
					'uri'        => '/',
				),
				$params
			);
			$this->_directory  = $params['directory'];
			$this->_controller = $params['controller'];
			$this->_action     = $params['action'];
			$this->_uri        = $params['uri'];
		}

	}

	class ControllerCapturingResponseStub extends \Response {

		protected $controller;

		public function __construct() {}

		public function setController(\Controller $controller)
		{
			$this->controller = $controller;
		}

		public function getController()
		{
			return $this->controller;
		}
	}

	class ControllerDummy extends \RequestClientInternalTestControllerDummy {}
}
