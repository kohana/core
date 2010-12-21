<!DOCTYPE html>
<html lang="<?php echo Kohana::$charset; ?>">
	<head>
		<title><?php echo HTML::chars($http_code.' '.$http_status); ?></title>
	</head>
	<body>
		<h1><?php echo HTML::chars($http_code.' '.$http_status); ?></h1>
		<p><?php echo HTML::chars($message); ?></p>
		<?php if (Kohana::$expose): ?>
			<hr />
			<p><em><?php echo ('Kohana Framework '.Kohana::VERSION.' ('.Kohana::CODENAME.')'); ?></em></p>
		<?php endif; ?>
	<body>
</html>