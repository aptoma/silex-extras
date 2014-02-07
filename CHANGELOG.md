CHANGELOG
=========

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
