<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Upload helper class for working with uploaded files and [Validate].
 *
 *     $array = Validate::factory($_FILES);
 *
 * [!!] Remember to define your form with "enctype=multipart/form-data" or file
 * uploading will not work!
 *
 * The following configuration properties can be set:
 *
 * - [Upload::$remove_spaces]
 * - [Upload::$default_directory]
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Upload {

	/**
	 * @var  boolean  remove spaces in uploaded files
	 */
	public static $remove_spaces = TRUE;

	/**
	 * @var  string  default upload directory
	 */
	public static $default_directory = 'upload';

	/**
	 * Save an uploaded file to a new location. If no filename is provided,
	 * the original filename will be used, with a unique prefix added.
	 *
	 * This method should be used after validating the $_FILES array:
	 *
	 *     if ($array->check())
	 *     {
	 *         // Upload is valid, save it
	 *         Upload::save($_FILES['file']);
	 *     }
	 *
	 * @param   array    uploaded file data
	 * @param   string   new filename
	 * @param   string   new directory
	 * @param   integer  chmod mask
	 * @return  string   on success, full path to new file
	 * @return  FALSE    on failure
	 */
	public static function save(array $file, $filename = NULL, $directory = NULL, $chmod = 0644)
	{
		if ( ! isset($file['tmp_name']) OR ! is_uploaded_file($file['tmp_name']))
		{
			// Ignore corrupted uploads
			return FALSE;
		}

		if ($filename === NULL)
		{
			// Use the default filename, with a timestamp pre-pended
			$filename = uniqid().$file['name'];
		}

		if (Upload::$remove_spaces === TRUE)
		{
			// Remove spaces from the filename
			$filename = preg_replace('/\s+/', '_', $filename);
		}

		if ($directory === NULL)
		{
			// Use the pre-configured upload directory
			$directory = Upload::$default_directory;
		}

		if ( ! is_dir($directory) OR ! is_writable(realpath($directory)))
		{
			throw new Kohana_Exception('Directory :dir must be writable',
				array(':dir' => Kohana::debug_path($directory)));
		}

		// Make the filename into a complete path
		$filename = realpath($directory).DIRECTORY_SEPARATOR.$filename;

		if (move_uploaded_file($file['tmp_name'], $filename))
		{
			if ($chmod !== FALSE)
			{
				// Set permissions on filename
				chmod($filename, $chmod);
			}

			// Return new file path
			return $filename;
		}

		return FALSE;
	}

	/**
	 * Tests if upload data is valid, even if no file was uploaded. If you
	 * _do_ require a file to be uploaded, add the [Upload::not_empty] rule
	 * before this rule.
	 *
	 *     $array->rule('file', 'Upload::valid')
	 *
	 * @param   array  $_FILES item
	 * @return  bool
	 */
	public static function valid($file)
	{
		return (isset($file['error'])
			AND isset($file['name'])
			AND isset($file['type'])
			AND isset($file['tmp_name'])
			AND isset($file['size']));
	}

	/**
	 * Tests if a successful upload has been made.
	 *
	 *     $array->rule('file', 'Upload::not_empty');
	 *
	 * @param   array    $_FILES item
	 * @return  bool
	 */
	public static function not_empty(array $file)
	{
		return (isset($file['error'])
			AND isset($file['tmp_name'])
			AND $file['error'] === UPLOAD_ERR_OK
			AND is_uploaded_file($file['tmp_name'])
		);
	}

	/**
	 * Test if an uploaded file is an allowed file type, by extension.
	 *
	 *     $array->rule('file', 'Upload::type', array(array('jpg', 'png', 'gif')));
	 *
	 * @param   array    $_FILES item
	 * @param   array    allowed file extensions
	 * @return  bool
	 */
	public static function type(array $file, array $allowed)
	{
		if ($file['error'] !== UPLOAD_ERR_OK)
			return TRUE;

		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

		return in_array($ext, $allowed);
	}

	/**
	 * Validation rule to test if an uploaded file is allowed by file size.
	 * File sizes are defined as: SB, where S is the size (1, 15, 300, etc) and
	 * B is the byte modifier: (B)ytes, (K)ilobytes, (M)egabytes, (G)igabytes.
	 *
	 *     $array->rule('file', 'Upload::size', array('1M'))
	 *
	 * @param   array    $_FILES item
	 * @param   string   maximum file size
	 * @return  bool
	 */
	public static function size(array $file, $size)
	{
		if ($file['error'] === UPLOAD_ERR_INI_SIZE)
		{
			// Upload is larger than PHP allowed size
			return FALSE;
		}

		if ($file['error'] !== UPLOAD_ERR_OK)
		{
			// The upload failed, no size to check
			return TRUE;
		}

		// Only one size is allowed
		$size = strtoupper(trim($size));

		if ( ! preg_match('/^[0-9]++[BKMG]$/', $size))
		{
			throw new Kohana_Exception('Size does not contain a digit and a byte value: :size', array(
				':size' => $size,
			));
		}

		// Make the size into a power of 1024
		switch (substr($size, -1))
		{
			case 'G': $size = intval($size) * pow(1024, 3); break;
			case 'M': $size = intval($size) * pow(1024, 2); break;
			case 'K': $size = intval($size) * pow(1024, 1); break;
			default:  $size = intval($size);                break;
		}

		// Test that the file is under or equal to the max size
		return ($file['size'] <= $size);
	}

} // End upload
