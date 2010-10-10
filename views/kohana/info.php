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
	
</body>
</html>