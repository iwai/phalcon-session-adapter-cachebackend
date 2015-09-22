# phalcon-session-adapter-cachebackend

CacheBackend adapter for session.  

## Install

```json
{
    "require": {
        "iwai/phalcon-session-adapter-cachebackend": "*"
    }
}
```

## Usage

### Multiple backend

```php
use Iwai\Phalcon\Session\Adapter\CacheBackend;
use Phalcon\Cache\Frontend\Data as FrontendData;

$app->getDI()->setShared('session', function () {
    $session = new CacheBackend();

    $backend = new \Phalcon\Cache\Multiple();

    $backend->push(new \Phalcon\Cache\Backend\Apc(
        new FrontendData([ 'lifetime' => 3600 ]),
        [ 'prefix' => 'cache' ]
    ));
    $backend->push(new \Phalcon\Cache\Backend\Memcached(
        new FrontendData([ 'lifetime' => 86400 ]),
        [ 'prefix' => 'cache', 'host' => 'localhost', 'port' => 11211 ]
    ));

    $session->setBackend($backend);
    $session->start();

    return $session;
});
```

### Single backend

```php
use Iwai\Phalcon\Session\Adapter\CacheBackend;
use \Phalcon\Cache\Frontend\Data as FrontendData;

$app->getDI()->setShared('session', function () use ($config) {
    $session = new CacheBackend();

    $backend = new \Phalcon\Cache\Backend\Memcached(
        new FrontendData([ 'lifetime' => 86400 ]), [
        'prefix' => 'cache',
        'host'   => 'localhost',
        'port'   => 11211
    ]);
        
    $session->setBackend($backend);
    $session->start();

    return $session;
});
```

### In controller

```php
$value = $this->session->get('key');
```


## See Also

[Phalcon Cache Document](http://docs.phalconphp.com/en/latest/reference/cache.html)
