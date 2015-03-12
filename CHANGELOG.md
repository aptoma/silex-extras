CHANGELOG
=========

2.0.0
-----

* Added: `CacheServiceProvider` for exposing `Doctrine\Cache` compatible cache implemenations
* Added: `PredisClientServiceProvider` for exposing a `Predis\Client` service.
* Improved: Guzzle cache plugin now reads cache implementation from `$app['cache']`, so you can configure it to use something other than Memcached.
* BC: The `cache` service is now moved to `CacheServiceProvider`. It still returns Memcached by default, but you need to register the CacheServiceProvider to make it available.

1.6.0
-----

* Added: `JsonErrorHandler` now supports an extra argumenent to enable handling of errors regardless of accept header

1.5.1
-----

* Enhancement: Add passive mode support to `Ftp`

1.5.0
-----

* New: Add `RequestBeforeSendLoggerPlugin` for logging dispatch of Guzzle requests.
* Enhancement: Add event context entry `MonologGuzzleLogAdapter`.

1.4.0
-----

* BC: `SingleLineFormatter` for Monolog is removed. Use Monolog's `LineFormatter@~1.8.0` instead.
* Enhancement: Default `ExtendLoggerServiceProvider` monolog formatter now includes microseconds.

1.3.2
-----

* Bugfix: RemoteRequestToken is cached in the processor, so it's available for all later log entries

1.3.1
-----

* Enhancement: RequestProcessor now adds remoteRequestToken if available.

1.3.0
-----

* New: Add `RequestTokenPlugin` for adding and forwarding request token headers.
* BC: `RequestTokenPlugin` needs `RequestStack` introduced in Symfony 2.4

1.2.3
-----

* Bugfix: Make 1.2.2 compatible with stable silex branch.

1.2.2
-----

* New: Add `SingleLineFormatter` for ensuring log entries don't span multiple lines
* Enhancement: `ExtendedLoggerServiceProvider` now uses SingleLineFormatter, unless you override `monolog.formatter`

1.2.1
-----

Add license to prepare for Packagist release.

1.2.0
-----

* New: Add `MemcachedServiceProvider`
* New: Add `GuzzleServiceProvider` and related helper classes for logging and tests

1.1.3
-----

* Enhancement: Add `PsrLogMessageProcessor` when using `ExtendedLoggerServiceProvider`

1.1.2
-----

* Enhancement: JsonErrorHandler now handles all exception types, not just HttpExpcetions

1.1.1
-----

* New: Allow overriding logfile used by console logger

1.1.0
-----

* New: ConsoleLoggerServiceProvider - Allows piping Monolog logging to stdout when running console commands

1.0.0
-----

This release encompasses all other stuff since the first commit.

* New: Storage lib is introduced
* New: Base test classes
* New: Base Application
* New: JsonErrorHandler
* New: ApiKey component

0.0.1
-----
* Initial release.
