<style type="text/css">
	table.route-test {
		margin-bottom:1.5em;
		background:#eee;
		border:2px solid #ccc;
	}
	
	table.route-test th {
		background:#ddd;
		padding:5px;
	}
	
	table.route-test td {
		padding:2px 10px;
	}
	
	table.route-test .error {
		background:#FFBBBB;
		color:red;
		font-weight:bold;
	}
	
	table.route-test .pass {
		background:#BBFFBB;
		color:green;
	}
</style>

<h1>Results of route test:</h1>

<?php foreach ($tests as $test) :?>

<table class="route-test">
	<tr><th colspan="3">Testing the url "<code><?php echo $test->url ?></code>"</th></tr>
	
	<?php if ($test->route === FALSE): ?>
	
		<tr><td colspan="3" class="error">Did not match any routes</td></tr>
	
	<?php else:?>
	
		<?php if ($test->expected_params): ?>
			
			<tr><th>param</th><th>result</th><th>expected</th>
		
			<?php
			foreach ($test->get_params() as $name => $param)
			{
				echo "<tr><td>{$name}</td><td".($param['error'] ? ' class="error"':' class="pass"').">{$param['result']}</td><td".($param['error'] ? ' class="error"':' class="pass"').">{$param['expected']}</td>";
			}
			?>
			
		<?php else: ?>
		
			<?php foreach ($test->params as $key => $value): ?>
				<tr><td><?php echo $key ?>:</td><td colspan="2"><?php echo $value ?></td></tr>
			<?php endforeach; ?>
			
		<?php endif; ?>
		
	<?php endif; ?>
	
</table>

<?php endforeach ?>


<h1>Copy/paste friendly version:</h1>

<pre style="border:1px dashed #666;padding:10px;" ><?php

foreach ($tests as $test)
{

	echo "Testing the url \"{$test->url}\"\n";
	
	if ($test->route === FALSE)
	{
		echo " ! Did not match any routes\n";
	}
	else
	{
		if ($test->expected_params)
		{
			foreach ($test->get_params() as $name => $param)
			{
				echo ($param['error'] ? ' ✓ ' : ' ! ' ).str_pad(str_pad($name.': ',15).$param['result'].' ',35).'(expecting '.$param['expected'].")\n";
			}
			
		}
		else
		{
			foreach ($test->params as $key => $value)
			{
				echo '   '.str_pad($key.':',15).$value."\n";
			}
		}
	}
	echo "\n";
}
?></pre>
