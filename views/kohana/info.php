<?php defined('SYSPATH') or exit('Install tests must be loaded from within index.php!'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Kohana Info</title>

	<style type="text/css">
	body { width: 42em; margin: 0 auto; font-family: sans-serif; background: #fff; font-size: 1em; padding-bottom:2em;}
	h1 { letter-spacing: -0.04em; }
	h1 + p { margin: 0 0 2em; color: #333; font-size: 90%; font-style: italic; }
	code { font-family: monaco, monospace; }
	table { border-collapse: collapse; width: 100%; border:2px solid #ccc; }
		table th,
		table td { padding: 0.4em; text-align: left; vertical-align: top; }
		table th { width: 12em; }
		table tr:nth-child(odd) { background: #eee; }
		table td.pass { color: #191; }
		table td.fail { color: #911; }
	#results { padding: 0.8em; color: #fff; font-size: 1.5em; }
	#results.pass { background: #191; }
	#results.fail { background: #911; }
	</style>

</head>
<body>
	
	<h1>Kohana Environment</h1>
	
	<table cellspacing="0">
		<tr>
			<th>Kohana Version</th>
			<td><?php echo Kohana::VERSION.' - <em>'.Kohana::CODENAME.'</em>' ?></td>
		</tr>
		<tr>
			<th>DOCROOT</th>
			<td><?php echo DOCROOT ?></td>
		</tr>
		<tr>
			<th>APPPATH</th>
			<td><?php echo APPPATH ?></td>
		</tr>
		<tr>
			<th>SYSPATH</th>
			<td><?php echo SYSPATH ?></td>
		</tr>
		<tr>
			<th>MODPATH</th>
			<td><?php echo MODPATH ?></td>
		</tr>
		<tr>
			<th>Kohana::$environment</th>
			<td><?php echo Kohana::$environment ?></td>
		</tr>
		<tr>
			<th>Kohana::init() settings</th>
			<td><code>"base_url" = <?php echo Kohana::dump(Kohana::$base_url) ?><br />
			"index_file" = <?php echo Kohana::dump(Kohana::$index_file) ?><br />
			"charset" = <?php echo Kohana::dump(Kohana::$charset) ?><br />
			"cache_dir" = <?php echo Kohana::dump(Kohana::$cache_dir) ?><br />
			"errors" = <?php echo Kohana::dump(Kohana::$errors) ?><br />
			"profile" = <?php echo Kohana::dump(Kohana::$profiling) ?><br />
			"caching" = <?php echo Kohana::dump(Kohana::$caching) ?></code></td>
		</tr>
	</table>
	
	<h2>Loaded Modules</h2>
	
	<?php if (count(Kohana::modules()) > 0): ?>
		<table cellspacing="0">
			<?php foreach (Kohana::modules() as $module => $path): ?>
			<tr>
				<th><?php echo $module ?></th>
				<td><?php echo $path ?>
					<?php if (is_file($path.'init.php')) echo ' <small><em>(has init.php file)<em></small>'; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
	<?php else: ?>
	<p>No modules loaded</p>
	<?php endif; ?>

	<h2>Routes</h2>
	
	<?php if (count(Route::all()) > 0): ?>
		
		<?php foreach (Route::all() as $route): ?>
		<h3><?php echo Route::name($route) ?></h3>
			<?php
			$array = (array) $route;
			foreach ($array as $key => $value)
			{
				$new_key = substr($key, strrpos($key, "\x00") + 1);
				$array[$new_key] = $value;
				unset($array[$key]);
			}
			?>
			<table>
				<tr>
					<th>Route uri</th>
					<td><code><?php echo html::chars($array['_uri']) ?></code></td>
				</tr>
				<tr>
					<th>Params with regex</th>
					<td><?php if (count($array['_regex']) == 0) echo "none"; foreach( $array['_regex'] as $param => $regex) echo "<code>\"$param\" = \"$regex\"</code><br/>" ?></td>
				</tr>
				<tr>
					<th>Defaults</th>
					<td><?php if (count($array['_defaults']) == 0) echo "none"; foreach( $array['_defaults'] as $param => $default) echo "<code>\"$param\" = \"$default\"</code><br/>" ?></td>

				</tr>
				<tr>
					<th>Compiled Regex</th>
					<td><code><?php echo html::chars($array['_route_regex']) ?></code></td>
				</tr>
			</table>
		<?php endforeach; ?>
		
	<?php else: ?>
	<p>No routes</p>
	<?php endif; ?>
	
	<h2>install.php tests</h2>
	
	<table cellspacing="0">
		<tr>
			<th>PHP Version</th>
			<?php if (version_compare(PHP_VERSION, '5.2.3', '>=')): ?>
				<td class="pass"><?php echo PHP_VERSION ?></td>
			<?php else: $failed = TRUE ?>
				<td class="fail">Kohana requires PHP 5.2.3 or newer, this version is <?php echo PHP_VERSION ?>.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>System Directory</th>
			<?php if (is_dir(SYSPATH) AND is_file(SYSPATH.'classes/kohana'.EXT)): ?>
				<td class="pass"><?php echo SYSPATH ?></td>
			<?php else: $failed = TRUE ?>
				<td class="fail">The configured <code>system</code> directory does not exist or does not contain required files.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Application Directory</th>
			<?php if (is_dir(APPPATH) AND is_file(APPPATH.'bootstrap'.EXT)): ?>
				<td class="pass"><?php echo APPPATH ?></td>
			<?php else: $failed = TRUE ?>
				<td class="fail">The configured <code>application</code> directory does not exist or does not contain required files.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Cache Directory</th>
			<?php if (is_dir(APPPATH) AND is_dir(APPPATH.'cache') AND is_writable(APPPATH.'cache')): ?>
				<td class="pass"><?php echo APPPATH.'cache/' ?></td>
			<?php else: $failed = TRUE ?>
				<td class="fail">The <code><?php echo APPPATH.'cache/' ?></code> directory is not writable.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Logs Directory</th>
			<?php if (is_dir(APPPATH) AND is_dir(APPPATH.'logs') AND is_writable(APPPATH.'logs')): ?>
				<td class="pass"><?php echo APPPATH.'logs/' ?></td>
			<?php else: $failed = TRUE ?>
				<td class="fail">The <code><?php echo APPPATH.'logs/' ?></code> directory is not writable.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>PCRE UTF-8</th>
			<?php if ( ! @preg_match('/^.$/u', 'ñ')): $failed = TRUE ?>
				<td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support.</td>
			<?php elseif ( ! @preg_match('/^\pL$/u', 'ñ')): $failed = TRUE ?>
				<td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with Unicode property support.</td>
			<?php else: ?>
				<td class="pass">Pass</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>SPL Enabled</th>
			<?php if (function_exists('spl_autoload_register')): ?>
				<td class="pass">Pass</td>
			<?php else: $failed = TRUE ?>
				<td class="fail">PHP <a href="http://www.php.net/spl">SPL</a> is either not loaded or not compiled in.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Reflection Enabled</th>
			<?php if (class_exists('ReflectionClass')): ?>
				<td class="pass">Pass</td>
			<?php else: $failed = TRUE ?>
				<td class="fail">PHP <a href="http://www.php.net/reflection">reflection</a> is either not loaded or not compiled in.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Filters Enabled</th>
			<?php if (function_exists('filter_list')): ?>
				<td class="pass">Pass</td>
			<?php else: $failed = TRUE ?>
				<td class="fail">The <a href="http://www.php.net/filter">filter</a> extension is either not loaded or not compiled in.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>Iconv Extension Loaded</th>
			<?php if (extension_loaded('iconv')): ?>
				<td class="pass">Pass</td>
			<?php else: $failed = TRUE ?>
				<td class="fail">The <a href="http://php.net/iconv">iconv</a> extension is not loaded.</td>
			<?php endif ?>
		</tr>
		<?php if (extension_loaded('mbstring')): ?>
		<tr>
			<th>Mbstring Not Overloaded</th>
			<?php if (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING): $failed = TRUE ?>
				<td class="fail">The <a href="http://php.net/mbstring">mbstring</a> extension is overloading PHP's native string functions.</td>
			<?php else: ?>
				<td class="pass">Pass</td>
			<?php endif ?>
		</tr>
		<?php endif ?>
		<tr>
			<th>Character Type (CTYPE) Extension</th>
			<?php if ( ! function_exists('ctype_digit')): $failed = TRUE ?>
				<td class="fail">The <a href="http://php.net/ctype">ctype</a> extension is not enabled.</td>
			<?php else: ?>
				<td class="pass">Pass</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>URI Determination</th>
			<?php if (isset($_SERVER['REQUEST_URI']) OR isset($_SERVER['PHP_SELF']) OR isset($_SERVER['PATH_INFO'])): ?>
				<td class="pass">Pass</td>
			<?php else: $failed = TRUE ?>
				<td class="fail">Neither <code>$_SERVER['REQUEST_URI']</code>, <code>$_SERVER['PHP_SELF']</code>, or <code>$_SERVER['PATH_INFO']</code> is available.</td>
			<?php endif ?>
		</tr>
	</table>
	
	<h3>Optional Tests</h3>

	<p>
		The following extensions are not required to run the Kohana core, but if enabled can provide access to additional classes.
	</p>

	<table cellspacing="0">
		<tr>
			<th>cURL Enabled</th>
			<?php if (extension_loaded('curl')): ?>
				<td class="pass">Pass</td>
			<?php else: ?>
				<td class="fail">Kohana requires <a href="http://php.net/curl">cURL</a> for the Remote class.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>mcrypt Enabled</th>
			<?php if (extension_loaded('mcrypt')): ?>
				<td class="pass">Pass</td>
			<?php else: ?>
				<td class="fail">Kohana requires <a href="http://php.net/mcrypt">mcrypt</a> for the Encrypt class.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>GD Enabled</th>
			<?php if (function_exists('gd_info')): ?>
				<td class="pass">Pass</td>
			<?php else: ?>
				<td class="fail">Kohana requires <a href="http://php.net/gd">GD</a> v2 for the Image class.</td>
			<?php endif ?>
		</tr>
		<tr>
			<th>PDO Enabled</th>
			<?php if (class_exists('PDO')): ?>
				<td class="pass">Pass</td>
			<?php else: ?>
				<td class="fail">Kohana can use <a href="http://php.net/pdo">PDO</a> to support additional databases.</td>
			<?php endif ?>
		</tr>
	</table>
	
</body>
</html>