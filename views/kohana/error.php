<?php

// Unique error identifier
$error_id = uniqid('error');

?>
<style type="text/css">
#kohana_error { display: block; position: relative; z-index: 1000; background: #cff292; font-size: 1em; font-family:sans-serif; text-align: left; color: #111; overflow: auto; }
#kohana_error a { color: #1b323b; }
#kohana_error p { margin: 0; padding: 0.2em 0; }
#kohana_error div.content { padding: 1em;  }
	#kohana_error div.content span.file { padding-right: 1em; }
#kohana_error pre.source { margin: 0 0 1em; padding: 0.4em; background: #fff; border: dotted 1px #b7c680; line-height: 1.2em; }
	#kohana_error pre.source span.line { display: block; }
	#kohana_error pre.source span.highlight { background: #f0eb96; }
		#kohana_error pre.source span.line span.number { color: #666; }
#kohana_error table.args { width: 100%; display: block; margin: 0 0 0.4em; padding: 0; border-collapse: collapse; background: #fff; }
	#kohana_error table.args td { border: solid 1px #ddd; text-align: left; vertical-align: top; padding: 0.4em; }
#kohana_error ol.trace { display: block; margin: 0 0 0 2em; padding: 0; list-style: decimal; }
	#kohana_error ol.trace li { margin: 0; padding: 0; }
</style>
<script type="text/javascript">
document.write('<style type="text/css">.collapsed { display: none; } </style>');
function koggle(elem)
{
	elem = document.getElementById(elem);
	elem.style.display = elem.style.display == 'block' ? '' : 'block';
	return false;
}
</script>
<div id="kohana_error">
	<div class="content">
		<p>
			<span class="type"><a href="#" onclick="return koggle('<?php echo $error_id ?>')"><?php echo $type ?> [ <?php echo $code ?> ]</a>:</span>
			<span class="message"><?php echo $message ?></span>
			&mdash;
			<span class="file"><?php echo Kohana::debug_path($file) ?> [ <?php echo $line ?> ]</span>
		</p>
	</div>
	<div id="<?php echo $error_id ?>" class="content collapsed">
		<pre class="source"><code><?php echo $source ?></code></pre>
		<ol class="trace">
		<?php foreach (Kohana::trace($trace) as $i => $step): ?>
		<li>
			<p>
				<span class="file">
					<?php if ($step['file']): ?>
						<a href="#" onclick="return koggle('<?php echo $error_id, 'source', $i ?>')"><?php echo Kohana::debug_path($step['file']) ?> [ <?php echo $step['line'] ?> ]</a>
					<?php else: ?>
						{<?php echo __('PHP internal call') ?>}
					<?php endif ?>
				</span>
				<?php echo $step['function'] ?>
					(<?php if ($step['args']): ?>
						<a href="#" onclick="return koggle('<?php echo $error_id, 'args', $i ?>')"><?php echo __('arguments') ?></a>
					<?php endif ?>)
			</p>
			<?php if ($step['args']): ?>
			<div id="<?php echo $error_id, 'args', $i ?>" class="collapsed">
				<table class="args" cellspacing="0">
				<?php foreach ($step['args'] as $name => $arg): ?>
					<tr>
						<td><code><?php echo $name ?></code></td>
						<td><?php echo Kohana::debug($arg) ?></td>
					</tr>
				<?php endforeach ?>
				</table>
			</div>
			<?php endif ?>
			<?php if ($step['file']): ?>
			<pre id="<?php echo $error_id, 'source', $i ?>" class="source collapsed"><code><?php echo $step['source'] ?></code></pre>
			<?php endif ?>
		</li>
		<?php endforeach ?>
	</div>
</div>
