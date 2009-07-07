<?php

// Unique error identifier
$error_id = uniqid('error');

?>
<style type="text/css">
#kohana_error { display: block; position: relative; z-index: 1000; background: #ddd; font-size: 1em; font-family:sans-serif; text-align: left; color: #111; overflow: auto; }
#kohana_error h1 { margin: 0; padding: 1em; font-size: 1em; font-weight: normal; background: #911; color: #fff; }
	#kohana_error h1 a { color: #fff; }
#kohana_error p { margin: 0; padding: 0.2em 0; }
#kohana_error a { color: #1b323b; }
#kohana_error div.content { padding: 1em; }
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
document.write('<style type="text/css"> .collapsed { display: none; } </style>');
function koggle(elem)
{
	elem = document.getElementById(elem);

	if (elem.style && elem.style['display'])
		// Only works with the "style" attr
		var disp = elem.style['display'];
	else if (elem.currentStyle)
		// For MSIE, naturally
		var disp = elem.currentStyle['display'];
	else if (window.getComputedStyle)
		// For most other browsers
		var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');

	// Toggle the state of the "display" style
	elem.style.display = disp == 'block' ? 'none' : 'block';
	return false;
}
</script>
<div id="kohana_error">
	<h1><span class="type"><?php echo $type ?> [ <?php echo $code ?> ]:</span> <span class="message"><?php echo $message ?></span></h1>
	<div id="<?php echo $error_id ?>" class="content">
		<p><span class="file"><?php echo Kohana::debug_path($file) ?> [ <?php echo $line ?> ]</span></p>
		<pre class="source"><code><?php echo $source ?></code></pre>
		<ol class="trace">
		<?php foreach (Kohana::trace($trace) as $i => $step): ?>
		<li>
			<p>
				<span class="file">
					<?php if ($step['file']): $source_id = $error_id.'source'.$i; ?>
						<a href="#<?php echo $source_id ?>" onclick="return koggle('<?php echo $source_id ?>')"><?php echo Kohana::debug_path($step['file']) ?> [ <?php echo $step['line'] ?> ]</a>
					<?php else: ?>
						{<?php echo __('PHP internal call') ?>}
					<?php endif ?>
				</span>
				&raquo;
				<?php echo $step['function'] ?>(<?php if ($step['args']): $args_id = $error_id.'args'.$i; ?><a href="#<?php echo $args_id ?>" onclick="return koggle('<?php echo $args_id ?>')"><?php echo __('arguments') ?></a><?php endif ?>)
			</p>
			<?php if (isset($args_id)): ?>
			<div id="<?php echo $args_id ?>" class="collapsed">
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
			<?php if (isset($source_id)): ?>
				<pre id="<?php echo $source_id ?>" class="source collapsed"><code><?php echo $step['source'] ?></code></pre>
			<?php endif ?>
		</li>
		<?php unset($args_id, $source_id); ?>
		<?php endforeach ?>
	</div>
</div>
