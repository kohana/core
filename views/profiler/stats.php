<?php defined('SYSPATH') or die('No direct script access.') ?>

<style type="text/css">
<?php include Kohana::find_file('views', 'profiler/style', 'css') ?>
</style>

<?php
// This whole block should probably be moved to a method in Profiler, called "group_stats".
// It calculates the max values (not min, average and total for now) for each group,
// and in doing so does not take individual tokens into account but only the totals for each named subgroup.
foreach (Profiler::groups() as $group => $benchmarks)
{
	foreach ($benchmarks as $name => $tokens)
	{
		$stats[$group][$name] = Profiler::stats($tokens);
	}

	foreach ($stats as $group => $names)
	{
		foreach ($names as $name => $stat)
		{
			foreach ($stat as $key => $results)
			{
				if ($key !== 'total')
					continue;

				if ( ! isset($groups[$group]['max']['time']) OR $groups[$group]['max']['time'] < $results['time'])
				{
					$groups[$group]['max']['time'] = $results['time'];
				}

				if ( ! isset($groups[$group]['max']['memory']) OR $groups[$group]['max']['memory'] < $results['memory'])
				{
					$groups[$group]['max']['memory'] = $results['memory'];
				}
			}
		}
	}
}
?>

<div class="kohana">
	<?php foreach (Profiler::groups() as $group => $benchmarks): ?>
	<table class="profiler">
		<tr class="group">
			<th class="name" colspan="5"><?php echo __(ucfirst($group)) ?></th>
		</tr>
		<tr class="headers">
			<th class="name"><?php echo __('Benchmark') ?></th>
			<th class="min"><?php echo __('Min') ?></th>
			<th class="max"><?php echo __('Max') ?></th>
			<th class="average"><?php echo __('Average') ?></th>
			<th class="total"><?php echo __('Total') ?></th>
		</tr>
		<?php foreach ($benchmarks as $name => $tokens): ?>
		<tr class="mark time">
			<?php $stats = Profiler::stats($tokens) ?>
			<th class="name" rowspan="2" scope="rowgroup"><?php echo $name, ' (', count($tokens), ')' ?></th>
			<?php foreach (array('min', 'max', 'average', 'total') as $key): ?>
			<td class="<?php echo $key ?>">
				<div>
					<div class="value"><?php echo number_format($stats[$key]['time'], 6) ?> <abbr title="seconds">s</abbr></div>
					<?php if ($key === 'total') { ?>
						<div class="graph" style="left: <?php echo 100 - $stats[$key]['time'] / $groups[$group]['max']['time'] * 100 ?>%"></div>
					<?php } ?>
				</div>
			</td>
			<?php endforeach ?>
		</tr>
		<tr class="mark memory">
			<?php foreach (array('min', 'max', 'average', 'total') as $key): ?>
			<td class="<?php echo $key ?>">
				<div>
					<div class="value"><?php echo number_format($stats[$key]['memory'] / 1024, 4) ?> <abbr title="kilobyte">kB</abbr></div>
					<?php if ($key === 'total') { ?>
						<div class="graph" style="left: <?php echo 100 - $stats[$key]['memory'] / $groups[$group]['max']['memory'] * 100 ?>%"></div>
					<?php } ?>
				</div>
			</td>
			<?php endforeach ?>
		</tr>
		<?php endforeach ?>
	</table>
	<?php endforeach ?>

	<table class="profiler">
		<?php $stats = Profiler::application() ?>
		<tr class="final mark time">
			<th class="name" rowspan="2" scope="rowgroup"><?php echo __('Application Execution').' ('.$stats['count'].')' ?></th>
			<?php foreach (array('min', 'max', 'average', 'current') as $key): ?>
			<td class="<?php echo $key ?>"><?php echo number_format($stats[$key]['time'], 6) ?> <abbr title="seconds">s</abbr></td>
			<?php endforeach ?>
		</tr>
		<tr class="final mark memory">
			<?php foreach (array('min', 'max', 'average', 'current') as $key): ?>
			<td class="<?php echo $key ?>"><?php echo number_format($stats[$key]['memory'] / 1024, 4) ?> <abbr title="kilobyte">kB</abbr></td>
			<?php endforeach ?>
		</tr>
	</table>
</div>