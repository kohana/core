<?php defined('SYSPATH') or die('No direct script access.') ?>

<style type="text/css">
<?php include Kohana::find_file('views', 'profiler/style', 'css') ?>
</style>

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
			<?php $stats = Profiler::stats($tokens); ?>
			<th class="name" rowspan="2"><?php echo $name, ' (', count($tokens), ')' ?></th>
			<?php foreach (array('min', 'max', 'average', 'total') as $key): ?>
			<td class="<?php echo $key ?>"><?php echo number_format($stats[$key]['time'], 6), ' ', __('seconds') ?></td>
			<?php endforeach ?>
		</tr>
		<tr class="mark memory">
			<?php foreach (array('min', 'max', 'average', 'total') as $key): ?>
			<td class="<?php echo $key ?>"><?php echo number_format($stats[$key]['memory'] / 1024, 4), ' kb' ?></td>
			<?php endforeach ?>
		</tr>
		<?php endforeach ?>
	</table>
	<?php endforeach ?>
	<table class="profiler">
		<?php $stats = Profiler::application() ?>
		<tr class="final mark time">
			<th class="name" rowspan="2"><?php echo __('Application Execution').' ('.$stats['count'].')' ?></th>
			<?php foreach (array('min', 'max', 'average', 'current') as $key): ?>
			<td class="<?php echo $key ?>"><?php echo number_format($stats[$key]['time'], 6), ' ', __('seconds') ?></td>
			<?php endforeach ?>
		</tr>
		<tr class="final mark memory">
			<?php foreach (array('min', 'max', 'average', 'current') as $key): ?>
			<td class="<?php echo $key ?>"><?php echo number_format($stats[$key]['memory'] / 1024, 4), ' kb' ?></td>
			<?php endforeach ?>
		</tr>
	</table>
</div>
