# Upgrading from 3.3.3.1

Minor version upgrades are usually done in a drop-in fashion. Unfortunately, however, upgrading from 3.3.3.1 to 3.3.4 needs a little configuration. This is because a [security disclosure from HP Fortify](https://github.com/kohana/kohana/issues/74), that unveiled a serious [host header attack](https://github.com/kohana/core/issues/613) vulnerability.

[!!] You *might* still be able to have a drop-in upgrade, in case you have set the `base_url` in the [Kohana::init] call to an absolute URL. We advise you however that you follow the step below to make your application secure, in case some day you decide to change your `base_url` to a relative URL.

## Trusted Hosts

You need to setup a list of trusted hosts. Trusted hosts are hosts that you expect your application to be accessible from.

Open `application/config/url.php` and add regex patterns of these hosts. An example is given hereunder:

~~~
return array(
	'trusted_hosts' => array(
		'example\.org',
		'.*\.example\.org',
	),
);
~~~

[!!] Do not forget to escape your dots (.) as these are regex patterns. These patterns should always fully match, as they are prepended with `^` and appended with `$`.

