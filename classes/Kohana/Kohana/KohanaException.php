<?php

namespace Kohana\Core\Kohana;

use ErrorException;
use Exception;
use InvalidArgumentException;
use Kohana\Core\Core;
use Kohana\Core\Debug;
use Kohana\Core\HTTP\HttpException;
use Kohana\Core\I18n;
use Kohana\Core\Log\LogBuffer;
use Kohana\Core\Response;
use Kohana\Core\View;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Kohana exception class. Translates exceptions using the [I18n] class.
 *
 * @package    Kohana
 * @category   Exceptions
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class KohanaException extends Exception {

	/**
	 * @var  array  PHP error code => human readable name
	 */
	public static $php_errors = array(
		E_ERROR              => 'Fatal Error',
		E_USER_ERROR         => 'User Error',
		E_PARSE              => 'Parse Error',
		E_WARNING            => 'Warning',
		E_USER_WARNING       => 'User Warning',
		E_STRICT             => 'Strict',
		E_NOTICE             => 'Notice',
		E_RECOVERABLE_ERROR  => 'Recoverable Error',
		E_DEPRECATED         => 'Deprecated',
	);

	/**
	 * @var  string  error rendering view
	 */
	public static $error_view = 'kohana/error';

	/**
	 * @var  string  error view content type
	 */
	public static $error_view_content_type = 'text/html';

	/**
	 * Creates a new translated exception.
	 *
	 *     throw new KohanaException('Something went terrible wrong, :user',
	 *         array(':user' => $user));
	 *
	 * @param   string          $message    error message
	 * @param   array           $variables  translation variables
	 * @param   integer|string  $code       the exception code
	 * @param   Exception       $previous   Previous exception
	 * @return  void
	 */
	public function __construct($message = "", array $variables = NULL, $code = 0, Exception $previous = NULL)
	{
		// Set the message
		$message = I18n::translate($message, $variables);

		// Pass the message and integer code to the parent
		parent::__construct($message, (int) $code, $previous);

		// Save the unmodified code
		// @link http://bugs.php.net/39615
		$this->code = $code;
	}

	/**
	 * Magic object-to-string method.
	 *
	 *     echo $exception;
	 *
	 * @uses    KohanaException::text
	 * @return  string
	 */
	public function __toString()
	{
		return self::text($this);
	}

	/**
	 * Inline exception handler, displays the error message, source of the
	 * exception, and the stack trace of the error.
	 *
	 * @uses    KohanaException::response
	 * @param   Exception  $e
	 * @return  void
	 */
	public static function handler($e)
	{
		$response = self::_handler($e);

		// Send the response to the browser
		echo $response->send_headers()->body();

		exit(1);
	}

	/**
	 * Exception handler, logs the exception and generates a Response object
	 * for display.
	 *
	 * @uses    KohanaException::response
	 * @param   Exception  $e
	 * @return  Response
	 */
	public static function _handler($e)
	{
		try
		{
			// Log the exception
			self::log($e);

			// Generate the response
			$response = self::response($e);

			return $response;
		}
		catch (Exception $e)
		{
			/**
			 * Things are going *really* badly for us, We now have no choice
			 * but to bail. Hard.
			 */
			// Clean the output buffer if one exists
			ob_get_level() AND ob_clean();

			// Set the Status code to 500, and Content-Type to text/plain.
			header('Content-Type: text/plain; charset='.Core::$charset, TRUE, 500);

			echo self::text($e);

			exit(1);
		}
	}

	/**
	 * Logs an exception.
	 *
	 * @uses    self::text
	 * @param   Exception  $e
	 * @param   string        $level
	 * @return  void
	 */
	public static function log($e, $level = LogLevel::EMERGENCY)
	{
		if (is_object(Core::$log))
		{
			// Create a text version of the exception
			$error = self::text($e);

			// Add this exception to the log
			Core::$log->log($level, $error, array('exception' => $e));

			if (Core::$log instanceof LogBuffer)
			{
				Core::$log->flush();
			}
		}
	}

	/**
	 * Get a single line of text representing the exception:
	 *
	 * Error [ Code ]: Message ~ File [ Line ]
	 *
	 * @param   Exception  $e
	 * @return  string
	 */
	public static function text($e)
	{
		if ( ! $e instanceof Exception AND ! $e instanceof Throwable)
		{
			throw new InvalidArgumentException('Argument 1 passed to \Kohana\Core\Kohana\KohanaException::response() must be an instance of Exception or Throwable');
		}

		return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
			get_class($e), $e->getCode(), strip_tags($e->getMessage()), Debug::path($e->getFile()), $e->getLine());
	}

	/**
	 * Get a Response object representing the exception
	 *
	 * @uses    self::text
	 * @param   Exception  $e
	 * @return  Response
	 */
	public static function response($e)
	{
		if ( ! $e instanceof Exception AND ! $e instanceof Throwable)
		{
			throw new InvalidArgumentException('Argument 1 passed to \Kohana\Core\Kohana\KohanaException::response() must be an instance of Exception or Throwable');
		}

		try
		{
			// Get the exception information
			$class   = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();
			$file    = $e->getFile();
			$line    = $e->getLine();
			$trace   = $e->getTrace();

			/**
			 * HTTP_Exceptions are constructed in the HTTP_Exception::factory()
			 * method. We need to remove that entry from the trace and overwrite
			 * the variables from above.
			 */
			if ($e instanceof HttpException AND $trace[0]['function'] == 'factory')
			{
				extract(array_shift($trace));
			}


			if ($e instanceof ErrorException)
			{
				/**
				 * If XDebug is installed, and this is a fatal error,
				 * use XDebug to generate the stack trace
				 */
				if (function_exists('xdebug_get_function_stack') AND $code == E_ERROR)
				{
					$trace = array_slice(array_reverse(xdebug_get_function_stack()), 4);

					foreach ($trace as & $frame)
					{
						/**
						 * XDebug pre 2.1.1 doesn't currently set the call type key
						 * http://bugs.xdebug.org/view.php?id=695
						 */
						if ( ! isset($frame['type']))
						{
							$frame['type'] = '??';
						}

						// Xdebug returns the words 'dynamic' and 'static' instead of using '->' and '::' symbols
						if ('dynamic' === $frame['type'])
						{
							$frame['type'] = '->';
						}
						elseif ('static' === $frame['type'])
						{
							$frame['type'] = '::';
						}

						// XDebug also has a different name for the parameters array
						if (isset($frame['params']) AND ! isset($frame['args']))
						{
							$frame['args'] = $frame['params'];
						}
					}
				}

				if (isset(self::$php_errors[$code]))
				{
					// Use the human-readable error name
					$code = self::$php_errors[$code];
				}
			}

			/**
			 * The stack trace becomes unmanageable inside PHPUnit.
			 *
			 * The error view ends up several GB in size, taking
			 * serveral minutes to render.
			 */
			if (
				defined('PHPUnit_MAIN_METHOD')
				OR
				defined('PHPUNIT_COMPOSER_INSTALL')
				OR
				defined('__PHPUNIT_PHAR__')
			)
			{
				$trace = array_slice($trace, 0, 2);
			}

			// Instantiate the error view.
			$view = View::factory(self::$error_view, get_defined_vars());

			// Prepare the response object.
			$response = Response::factory();

			// Set the response status
			$response->status(($e instanceof HttpException) ? $e->getCode() : 500);

			// Set the response headers
			$response->headers('Content-Type', self::$error_view_content_type.'; charset='.Core::$charset);

			// Set the response body
			$response->body($view->render());
		}
		catch (Exception $e)
		{
			/**
			 * Things are going badly for us, Lets try to keep things under control by
			 * generating a simpler response object.
			 */
			$response = Response::factory();
			$response->status(500);
			$response->headers('Content-Type', 'text/plain');
			$response->body(self::text($e));
		}

		return $response;
	}

}
