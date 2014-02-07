Silex Extras
============

Package consisting of reusable stuff for Silex powered applications.

It is collection of various services that will ease bootsrapping new Silex
applications, keep best practices in sync across projects, and ensure we do
stuff in a similar manner whenever we do something.

## What's In It?

- Log processor for adding extra context to logs
- ServiceProvider for rich logging in Logstash format
- Base Application class for doing common stuff we always do in Silex applications
- Error handler that outputs stuff as json if `Accept: application/json`
- TestToolkit to bootstrap functional testing of Silex applications
- API key user authentication
- Storage interface, with local file and Level3 implementations
- Ftp upload abstraction, basically a wrapper around native FTP functionality
- Level3 upload service, for uploading stuff to Level3
- ConsoleLoggerServiceProvider, for integrating the logger with the console
- MemcachedServiceProvider
- GuzzleServiceProvider for extra Guzzle features
- Guzzle HttpCallInterceptorPlugin for testing with Guzzle services

### Aptoma\Ftp

Wraps native PHP FTP functions in an object oriented manner. It's designed for
working with Level3 CDN, and thus has a concept of multiple paths for upload.

Usage:

````PHP
$ftp = new Ftp($hostname, $username, $password, $logger);
$file = new File($pathToFile);

$destination = 'path/on/server/with/filename.txt';
// All directories needs to be created before upload
$ftp->preparePaths(array($destination));
$ftp->put($destination, $file->getRealPath());
````

The class has a few more features for validating upload integrity and moving
the file to publish location after upload. Read the source :)

### Aptoma\Service\Level3Service

Provides an abstraction for uploading files to Level3. You need to provide a
an `Ftp` instance and various paths, and can then simply do:
`$service->upload($fileContents, $targetFileName, $relativeLocalTempDir);`.

After upload, the file will be renamed and put in a folder matching it's checksum,
in order to avoid duplicate uploads, and to deal with Level3's (sensible) limitation
of max number of files in a directory. The full public url is returned. Strictly
speaking, this isn't actually Level3 specific, but more a two-step strategy for
validated uploads.

There's also a bundled Level3ServiceProvider for simpler integration with Silex.

### Aptoma\Log

This folder provides two classes:

- `ExtraContextProcessor` for always adding a predefined set of extra fields to log entries
- `RequestProcessor` for adding client ip, unique request token and username to all entries
- `MonologGuzzleLogAdapater` for integrating Monolog with Guzzle, to log request times and errors

Usage is simple:

````PHP
use Monolog\Logger;
use Aptoma\Log\RequestProcessor;

$app = new \Silex\Application(...);

$app['logger']->pushProcessor(new RequestProcessor($app));
$app['logger']->pushProcessor(new ExtraContextProcessor(array('service' => 'my-service', 'environment' => 'staging')));
````

### Aptoma\Silex\Provider\ExtendedLoggerServiceProvider

This is a service provider that automatically adds the above mentioned `RequestProcessor`,
as well as a LogstashFormatter if you have specified `monolog.logstashfile`.

The LogstashFormatter can also add some extra context to each record:

````PHP
$app['meta.service'] = 'drvideo-metadata-admin-gui'; // The name of the service, consult with AMP
$app['meta.customer'] = 'Aptoma'; // The name of customer for this record
$app['meta.environment'] = 'production'; // The environment of the current installation
````

These extra fields will help us classify records in our consolidated logging
infrastructure (Loggly, Logstash and friends), and lead to great success.

### Aptoma\Silex\Provider\ConsoleLoggerServiceProvider

This service provider makes it easy to show log messages from services in the console,
without having to inject an instance of `OutputInterface` into the services. This
requires version ~2.4 of Symfony Components. More info about the change is at the
[Symfony Blog](http://symfony.com/blog/new-in-symfony-2-4-show-logs-in-console).

In your console application, you can now do something like this:

````PHP
use Symfony\Component\Console\Application;

$app = require 'app.php';
$console = new Application('My Console Application', '1.0');
// You should only register this service provider when running commands
$app->register(new \Aptoma\Silex\Provider\ConsoleLoggerServiceProvider());

$console->addCommands(
    array(
    //...
    )
);

$console->run($app['console.input'], $app['console.output']);
````

You will still use the normal `OutputInterface` instance for command feedback
in your commands, but you will now also get output from anything your services
are logging.

The console logger overrides the default `monolog.handler` in order to allow setting
a custom log file. If defined, it will use `monolog.console_logfile`, and if not, it
will fall back to `monolog.logfile`.

### Aptoma\Silex\Application

This is a base application you can extend. It will add a json formatter for errors,
register `ServiceControllerServiceProvider` and `UrlGeneratorServiceProvider`,
automatically log execution time for scripts (up until the response has been sent),
and also exposes `registerTwig` and `registerLogger`, which you can use to set up
those with one line of code.

This class should include functionality that we _always_ use, meaning it's not
a collection of "nice to haves".

### Aptoma\JsonErroHandler

This class simply formats exceptions as `JsonResponse`s, provided the client
has sent an `Accept: application/json` header. It will be loaded automatically
by the base `Application` class mentioned above, or it can be registered manually:

````PHP
$jsonErrorHandler = new Aptoma\JsonErrorHandler($app);
$app->error(array($jsonErrorHandler, 'handle'));
````

### Aptoma\TestToolkit

This includes a BaseWebTestCase you can use to bootstrap your test, and an
associated `TestClient` with shortcuts for `postJson($url, $data)` and
`putJson($url, $data)`.

To use it, you need to have your tests extend it, and probably also add the
path to your bootstrap file:

````PHP
class MyObjectTest extends TestToolkit\BaseWebTestCase
{
    public function setUp()
    {
        $this->pathToAppBootstrap = __DIR__.'/../../app/app.php';
        parent::setUp();
    }
}
````

### Aptoma\Security

Component for API key user authentication.

All it requires is a UserProvider and an encoder to encode the API key.
It'll typically be used in your app like this:

````PHP
$app->register(
    new Aptoma\Silex\Provider\ApiKeyServiceProvider(),
    array(
        'api_key.user_provider' => new App\Specific\UserProvider(),
        'api_key.encoder' => new App\Specific\Encoder()
    )
);
````

It can then be attached to any firewall of your choice:

````PHP
$app->register(
    new Silex\Provider\SecurityServiceProvider(),
    array(
        'security.firewalls' => array(
            // ...
            'secured' => array(
                'pattern' => '^.*$',
                'api_key' => true
                // more settings...
            )
        )
    )
);
````

### MemcachedServiceProvider

Registers Memcached as a service, and takes care of prefixes and persistent connections.
It returns an instance of \Memcached.
It also provides a generic cache compatible with Doctrine\Common\Cache.

````PHP

$app['memcached.identifier'] = 'my_app';
$app['memcached.prefix'] = 'ma_';
$app['memcached.servers'] = array(
        array('host' => '127.0.0.1', 'port' => 11211),
    );

$app->register(new Aptoma\Silex\Provider\MemcachedServicerProvider());

$app['memcached']->set('mykey', 'myvalue');

````

### GuzzleServiceProvider

Extends the base GuzzleServiceProvider to allwo registering global plugins, and also
adds plugins for generic logging of each request, logging of total requests and a cache
plugin for HTTP based caching.

````PHP
$app->register(new GuzzleServiceProvider(), array('guzzle.services' => array()));
$app->finish(array($app['guzzle.request_logger_plugin'], 'writeLog'));

$app['guzzle.plugins'] = $app->share(
    function () use ($app) {
        return array(
            $app['guzzle.log_plugin'],
            $app['guzzle.request_logger_plugin'],
            $app['guzzle.cache_plugin'],
        );
    }
);
````

### Guzzle HttpCallInterceptorPlugin

Guzzle plugin for use in unit testing to ensure there are no calls made to any
external services. In your test setup, do something like this:

````PHP

$this->app['guzzle.plugins'] = $this->app->share(
    $this->app->extend(
        'guzzle.plugins',
        function (array $plugins, $app) {
            $plugins[] = new HttpCallInterceptorPlugin($app['logger']);

            return $plugins;
        }
    )
);

// Intercept errors and fail tests, if you don't do this, you'll most often get
// a rather cryptic error message
$this->app->error(
    function (HttpCallToBackendException $e) use ($testCase) {
        $testCase->fail($e->getMessage());
    },
    10
);

$this->app->error(
    function (BatchTransferException $e) use ($testCase) {
        $testCase->fail($e->getMessage());
    },
    10
);
````
