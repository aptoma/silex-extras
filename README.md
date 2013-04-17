Silex Extras
============

Package consisting of reusable stuff for Silex powered applications.

The vision for this package is to keep DRY. As we get a lot of applications
built using the same tools, we find that we tend to do the same stuff a lot of
places. This package seeks to provide default implementations.

This might sound like the first steps towards a new AFW, but this really just
default configuration for integration 3rd party modules consistently.

## What's In It?

- Log processor for adding extra context to logs
- ServiceProvider for rich logging in Logstash format
- Base Application class for doing common stuff we always do in Silex applications
- Error handler that outputs stuff as json if `Accept: application/json`
- TestToolkit to bootstrap functional testing of Silex applications

### Aptoma\Log

This folder provides two classes:

- `ExtraContextProcessor` for always adding a predefined set of extra fields to log entries
- `RequestProcessor` for adding client ip, unique request token and username to all entries

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
