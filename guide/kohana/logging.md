# Logging

From [Wikipedia](https://en.wikipedia.org/wiki/Logfile):

> In computing, a logfile is a file that records either events that occur in
an operating system or other software runs, or messages between different users
of a communication software. Logging is the act of keeping a log. In the
simplest case, messages are written to a single logfile.

> Many operating systems, software frameworks, and programs include a logging
system. A widely used logging standard is syslog, defined in Internet
Engineering Task Force (IETF) RFC 5424. 

Kohana provides a logging mechanism that support multiple writers & log filters.
In a standard Kohana application, the logger `Kohana_Log` is bootrapped with a
File writer and logs exceptions automatically. If you have not altered the way
Kohana bootstraps the logger, you can find the logs written in `APPPATH.'logs'`
folder.

[!!] As of Kohana v3.4, Kohana logger is PSR-3 compliant.

## Bootstrapping

### The standard Kohana Log

In a standard Kohana application, the `Kohana::$log` static global variable is
initialized in `Kohana::init()`. Later, in `bootstrap.php`, a `Log_File` writer
is attached to it:

~~~
// Attach the file writer to logging. Multiple writers are supported.
Kohana::$log->attach(new Log_File(APPPATH.'logs'));
~~~

You can attach more than one writer to `Kohana::$log`, by locating the above
code in `bootstrap.php` and attaching writers in similar fashion.

[!!] As of Kohana v3.4, you can bootstrap the Log_File writer with custom modes
for the files and the folders that it creates. The current default modes are more
restrictive that it used to be in v3.3. Developers should assess their needs
and are encouraged to bootstrap the writer with modes as strict as possible.

### A PSR-3 compliant logger

Any PSR-3 compatible logger can be used instead of the default Kohana logger.
In this example, we will be using [Monolog](https://github.com/Seldaek/monolog).

[!!] To use a PSR-3 compatible logger other than Kohana’s default, you need
to initialize it prefereably in `bootstrap.php` before `Kohana::init()` call.

~~~
// create a log channel and add a handler
Kohana::$log = new Monolog\Logger('name');
Kohana::$log->pushHandler(new Monolog\Handler\StreamHandler(APPPATH.'logs'));
~~~

Once `Kohana::$log` is initialized and is a valid PSR-3 logger, Kohana will not
attempt to replace it with a standard logger.

## Usage

### Logging API

#### The generic `log` method

Kohana logger exposes a generic `log` method, it accepts three arguments. Each
argument is described in detail below:

~~~
Log::log($level, $message, array $context = [])
~~~

1. The first argument is one of the eight RFC 5424 log levels as defined in
`\Psr\Log\LogLevel` class.
2. The second argument is the message you want to log. It should be either a
string or an object with a `__toString()` method. This message might contain
placeholders wrapped in braces `{}` that will be replaced with values from the
context array (third argument).
3. The third argument is the context array which is a lookup hashtable mapping
placeholders to values. Those values will be interpolated with the placeholders
when logging, except when it's passed `['exception'=>$exception]`. The latter,
being an exception object will be used to log the stack trace of the exception,
instead of interpolating.

Example:

~~~
use \Psr\Log\LogLevel;

Kohana::$log->log(LogLevel::INFO, '{runtime} passed by', ['runtime' => 'PHP']);
~~~

In a standard Kohana application, the above example should produce a log file
`APPPATH.logs\{yyyy}\{mm}\{dd}.php` with an entry containing the following:

~~~
2015-11-19 10:05:48 --- INFO: PHP passed by in /path/to/file.php:10
~~~

Note that the above log entry might vary, as the date, the time, the file path
and the line number given here are just examples. These parameters are written
according to the environment when logging.

#### The eight level-specific methods

An additional eight level-specific methods are exposed by the logger to write
logs according to the eight levels (debug, info, notice, warning, error,
critical, alert, emergency). So basically, instead of specifying the level at
the first parameter of the generic `log` method, we can use one of those
level-specific methods instead. The following rewrite of the example above
yields to the same result:

~~~
Kohana::$log->info('{runtime} passed by', ['runtime' => 'PHP']);
~~~

Notice the use of the method `info` instead of `LogLevel::INFO` parameter of the
`log` method.

### Deferred writing

Kohana log supports deferred writing. It buffers log entries in an array and
then flushes them to the writers once `Kohana_Log_Buffer::flush()` is called.

During the handling of an exception, as well as during a standard shutdown
at the end of a successful HTTP request, Kohana logger `flush()`s its logs to
the attached writers automatically, so that logs are written.


## Multiple Writers

You can initiate writers and attach as many as you need to the logger via its
`attach` method.

### File Writer

Kohana comes with a simple file writer to write the logs to the filesystem.
In a standard installation, the file writer will write logs into a file located
in a directory tree following this path format: `APPPATH/log/yyyy/mm/dd.php`

### Syslog Writer

From [Wikipedia](https://en.wikipedia.org/wiki/Logfile):

> The syslog standard enables a dedicated, standardized subsystem to generate,
filter, record, and analyze log messages. This relieves software developers of
having to design and code their own ad hoc logging systems.

Kohana comes with `Log_Syslog` writer that extends the `Log_Writer`, and wraps
around PHP `syslog()` calls to write to the system log. PSR string levels are
transformed to OS specific `LOG_` levels.

To write your logs to the system log, attach an instance of `Log_Syslog` to
Kohana::$log.

~~~
// Attach the syslog writer to logging.
Kohana::$log->attach(new Log_Syslog('MyKohanaApplication'));
~~~


### Custom Writer

You can have your own log writer defined if you extend from the abstract
`Log_Writer` class and implement its own `write()` method.

## Filtering log entries

There might be times you do not want your writer to write all the flushed log
entries it receives from the logger. In order to selectively filter out log
entries, you must attach an implementation of `Kohana_Log_Filter` to your
writer.

### Attaching a filter

Similar to the way you attach writers to the logger, you can initialize and
attach a filter to the writer. The writer, then will write only the log entries
that are white-listed by the filter.

To attach a filter, use:

~~~
$writer->attach_filter($filter);
~~~

To detach a filter:

~~~
$writer->detach_filter($filter);
~~~

You can attach multiple filters to a Log_Writer. The writer will filter out the
logs by intersecting (AND logic) filter results.

### Log level filter

Kohana comes with a `Log_Filter_PSRLevel` that can filter log entries according
to the log PSR-3 log level.

~~~
use \Psr\Log\LogLevel;

$writer = Log_File(APPPATH.’log’);
$writer->attach_level(new Log_Filter_PSRLevel([LogLevel::INFO, LogLevel::DEBUG]));
Kohana::$log->attach($writer);
~~~

The example above shows you how to make the writer to write only logs that
have `LogLevel::INFO` and `LogLevel::DEBUG` as log levels.

### Custom Filter

If you need to filter out logs according to a custom logic, you can create a
filter by implementing the `Kohana_Log_Filter` interface, and attach the object
to your writer.

Happy logging!

