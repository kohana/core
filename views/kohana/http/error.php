<!DOCTYPE html>
<html lang="<?php echo Kohana::$charset; ?>">
<head>
	<title><?php echo ($title = ($http_code.' '.$http_status)); ?></title>
</head>
<body>
<h1><?php echo $title; ?></h1>
<?php if (isset($message)) : ?>
<p><?php echo $message; ?></p>
<?php endif; ?>
<?php if (Kohana::$expose) : ?>
<hr />
<p><em><?php echo ('Kohana Framework '.Kohana::VERSION.' ('.Kohana::CODENAME.')'); ?></em></p>
<?php endif; ?>
<body>
<html>