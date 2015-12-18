# Migrating from 3.3

## PSR-3 compliance of Kohana logger

Starting with version 3.4 the Kohana logger is PSR-3 compliant. This means that
it 

### The deprecation of the `add` method

Use the PSR-3 \Psr\Log\LoggerInterface methods instead of the `add` method.

The following code:

~~~
// Deprecated
Kohana::$log->add(Log::INFO, "This is an info message");
~~~

Should be migrated to this new syntax:

~~~
// Use the \Psr\Log\LoggerInterface log method
Kohana::$log->log(Log::INFO, "This is an info message");

// You can also use one of the Psr\Log\LogLevel levels
Kohana::$log->log(Psr\Log\LogLevel::INFO, "This is an info message");
~~~

### Immediate writing instead of buffering

You should use `Log_Writer::set_immediate_write` method instead of the static
`$write_on_add` property

The following code:

~~~
$writer::$write_on_add = TRUE;
~~~

Should be migrated to this new syntax:

~~~
$writer->set_immediate_write(TRUE);
~~~

### Writer flushes

The `Log::write` method has been renamed to `Log::flush`. Usually you do
not call this method, as Kohana calls it when there is an exception thrown
or at normal request shutdown.

The following code:

~~~
Kohana::$log->write();
~~~

Should be migrated to this new syntax:

~~~
Kohana::$log->flush();
~~~

### Log_File permissions

It is possible now to bootstrap the Log_File writer with custom modes for the
files and the folders that it creates. The current default modes are more
restrictive that it used to be in v3.3. Developers should assess their needs
and are encouraged to bootstrap the writer with modes as strict as possible.

For example:

~~~
// note that this is just an example
Kohana::$log->attach(new Log_File(APPPATH.'logs', 0770, 0660));
~~~

If you used to rely on the `setgid` bit (when logging in PHP safe mode) you need
to set it now explicitely, as the default omits that as well.

For example:

~~~
// note that this is just an example
Kohana::$log->attach(new Log_File(APPPATH.'logs', 02777, 0666));
~~~


### Filtering

Define filtering by attaching filters to the writer. In Kohana 3.3,
we used to specify the level filtering when attaching the writer to the log.

The following code:

~~~
$writer = new Log_File(APPPATH.'log');
Kohana::$log->attach($writer, Log::EMERGENCY, Log::ALERT);
~~~

Should be migrated to this new syntax:

~~~
use \Psr\Log\LogLevel;

$writer = new Log_File(APPPATH.'log');
$writer->attach_filter(new Log_Filter_PSRLevel([LogLevel::EMERGENCY, LogLevel::ALERT]));
Kohana::$log->attach($writer);
~~~

### Log_Writer static properties refactor

The `Log_Writer` abstract class had the following public static
properties that are no more available:

~~~
public static $timestamp;
public static $timezone;
public static $strace_level;
~~~

You should use the appropriate getter/setter on the `Log_Writer` object instead:

~~~
$writer->get_timestamp_format();
$writer->set_timestamp_format('Y-m-d');

$writer->get_timezone();
$writer->set_timezone('Asia/Beirut');

$writer->get_strace_level();
$writer->set_strace_level('Psr\Log\LogLevel::INFO');
~~~

In addition to a new API to specify the log writers default format:

~~~
$writer->get_format();
$writer->set_format('body in file:line');
~~~
