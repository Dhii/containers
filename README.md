# Dhii - Containers
[![Continuous Integration](https://github.com/Dhii/containers/actions/workflows/ci.yml/badge.svg)](https://github.com/Dhii/containers/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/dhii/containers/v)](//packagist.org/packages/dhii/containers)
[![Latest Unstable Version](https://poser.pugx.org/dhii/containers/v/unstable)](//packagist.org/packages/dhii/containers)

## Details
A selection of [PSR-11][] containers for utility, simplicity, and ease.

### Generic
- [`CachingContainer`][] - A decorator that [separates the concern][SoC] of caching, allowing for caching of any container's values.
- [`CompositeContainer`][] - A container that is composed of multiple other containers. On key access, iterates over its internal list of containers, and accesses that key of the first container which has that key. Useful for merging configuration from multiple sources, without actually doing any merging.
- [`ProxyContainer`][] - A container that forwards access to 1 other container, which can be assigned after construction. Useful for solving container-related recursive dependency problems, for example for lookup delegation.
- [`AliasingContainer`][] - A decorator that provides access to another container via aliased keys. Useful when an immutable map needs to have its keys changed.
- [`MappingContainer`][] - A decorator that uses a callback to manipulate values retrieved from another container on the fly.
- [`PrefixingContainer`][] - A decorator that allows access to another container's values via prefixed keys. Can also fall back to non-prefixed keys. Useful when e.g. enforcing a prefixing naming convention on keys. The opposite of `DeprefixingContainer`.
- [`DeprefixingContainer`][] - A decorator that allows access to another container's values that have prefixed keys - without the prefix. Useful for e.g. simplification of keys that follow a prefixiing naming convention. The opposite of `PrefixingContainer`.
- [`MaskingContainer`][] - A decorator that selectively hides or exposes keys of another container. Useful when working with maps that have a defined structure.
- [`PathContainer`][] - A decorator that allows access to a hierarchy of nested container via path-like keys. Useful when accessing configuration merged from multiple sources.
- [`SegmentingContainer`][] - A decorator that allows access to a container with delimiter-separated path-like keys as if it were a hierarchy of containers. Useful isolating a segment of configuration when using a path-like naming convention for keys (such as namespacing). The opposite of `PathContainer`.
- [`HierarchyContainer`][] - A container that allows access to a arbitrary hierarchy of arrays as if it was a hierarchy of containers. Creates containers in place, and caches them for future re-use.
- [`Dictionary`][] - Allows access to an array via a container interface, without sacrificing iterability.
- [`DataStructureBasedFactory`][] allows this to be recursive for an array hierarchy of an arbitrary depth. Useful for transforming an array into a container, especially with other decorators.
- [`SimpleCacheContainer`][] - A decorator that presents a PSR-16 cache as a mutable, clearable container with fixed TTL.
- [`FlashContainer`][] - A decorator that presents a value from an inner storage container as another container, copying that value into memory, then clearing it from storage.
- [`NoOpContainer`][] - A no-op writable mutable clearable map that does nothing, and cannot have any values.

### DI
- [`ServiceProvider`][] - A super-simple implementation that allows quick creation of  [service providers][Service Provider] from known maps of factories and extensions.
- [`CompositeCachingServiceProvider`][] - A service provider that aggregates factories and extensions of other service providers. The results of this aggregation will be cached, meaing that it is only performed at most once per instance - when retrieving said factories or extensions.
- [`DelegatingContainer`][] - A container that will invoke the factories and extensions of its configured service provider before returning values. If a parent container is specified, it will be passed to the service definitions instead of this container. This allows [dependency lookup delegation][DDL], which is especially useful when composing a container out of other containers.

## Examples

### Application Container
Most modern applications use some kind of DI container setup. The below example demonstrates a use-case where all configuration is composed of different sources, accessible via a single source of truth, services are cached per request.

```php
    // Retrieve factories and extensions from respective files, and create a service provider with them
    $factories = require('factories.php');
    $extensions = require('extensions.php');
    $appProvider = new ServiceProvider($factories, $extensions);
    
    // Perhaps retrieve service providers from other modules and aggregate them
    $provider = new CompositeCachingServiceProvider([$appProvider, $moduleProviderA, $moduleProviderB]);
    
    $proxyContainer = new ProxyContainer(); // A temporary parent container for lookup delegation
    $container = new DelegatingContainer($provider, $proxyContainer); // Container with application configuration
    $appContainer = new CompositeContainer([ // The application's container
        $dbContainer, // <-- Perhaps another container with configuration from DB
        $container, // <-- The main container with merged configuration from modules
    ]);
    $appContainer = new CachingContainer($appContainer); // Add caching, so that each service definition is only invoked once
    $proxyContainer->setInnerContainer($appContainer); // Switch lookup to the application's main container, making it available in service definitions
    
    // Retrieve cached configuration aggregated from various modules and other sources, sucha as the database or a remote API
    $appContainer->get('my-service');
```

### Fun Things With Maps
Maps are very commonly used in applications to represent some key-value relationships. We decided that PSR-11 containers are a great way to represent maps in an interop way. Here are some of the things you can do.

```php
// App configuration, perhaps from a file
$config = [
  'dev' => [
    'db' => [
      'host' => 'localhost',
      'username' => 'root',
      'password' => '',
      'database' => 'my_app',
    ],
  'staging' => [
    'db' => [
      'host' => '123.abc.com',
      'username' => 'application123',
      'password' => '$*!@$T123SAfa',
      'database' => 'my_app',
    ],
  ],
];

// Can create container hierarchies of arbitrary depths from array hierarchies
$factory = new DataStructureBasedFactory(new DictionaryFactory());
// The new configuration interface
$config = $factory->createContainerFromArray($config);

// Output the DB host names for each environment
foreach ($config as $env => $envConfig) {
  echo $env . ': ' . $envConfig->get('db')->get('host') . PHP_EOL; // Print 'dev: localhost' then 'staging: 123.abc.com'
}

// Access configuration using a path
$config = new PathContainer($config, '/');
echo $config->get('staging/db/username'); // Print 'application123'

// Access dev env DB config with a 'local_' prefix
$localDbConfig = new PrefixingContainer($config->get('dev/db'), 'local_');
echo $localDbConfig->get('local_username'); // Print 'root'

// Effectively adds production DB configuration
$productionConfig = new Dictionary([
  'production' => [
    'db' => [
      'host' => 'db.myserver.com',
      'username' => 'D97rh1d0A&13',
      'password' => 'DN(Q(u3dgh3q87g3',
      'database' => 'my_app',
    ],
  ],
]);
$config = new CompositeContainer([$config, $productionConfig]);
echo $config->get('production/db/password'); // Print 'DN(Q(u3dgh3q87g3'
echo $config->get('dev/db/password'); // Print '': all of the old configuration is available on this new container

// Make production host also available as 'live_db_host' - maybe something requires it to be at that key, and not in a path
$config = new AliasingContainer($config, ['live_db_host' => 'production/db/host']);
echo $config->get('live_db_host'); // Print 'db.myserver.com'
echo $config->get('production/db/host'); // That value is still accessible by the original full path

// Isolate production DB configuration, but without the 'password' key - perhaps to be passed to the UI, or another untrusted party
$productionConfig = new MaskingContainer($config->get('production/db'), true, ['password' => false]);
echo $productionConfig->get('password'); // NotFoundException: This key does not exist for this container
```


[Service Provider]: https://github.com/container-interop/service-provider/
[Dhii]: https://github.com/Dhii/dhii
[PSR-11]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md
[SoC]: https://dev.to/xedinunknown/separation-of-concerns-3e7d

[`ServiceProvider`]: src/ServiceProvider.php
[`CompositeCachingServiceProvider`]: src/CompositeCachingServiceProvider.php
[`DelegatingContainer`]: src/DelegatingContainer.php
[`CachingContainer`]: src/CachingContainer.php
[`CompositeContainer`]: src/CompositeContainer.php
[`ProxyContainer`]: src/ProxyContainer.php
[`AliasingContainer`]: src/AliasingContainer.php
[`MappingContainer`]: src/MappingContainer.php
[`PrefixingContainer`]: src/PrefixingContainer.php
[`DeprefixingContainer`]: src/DeprefixingContainer.php
[`MaskingContainer`]: src/MaskingContainer.php
[`PathContainer`]: src/PathContainer.php
[`SegmentingContainer`]: src/SegmentingContainer.php
[`HierarchyContainer`]: src/HierarchyContainer.php
[`Dictionary`]: src/Dictionary.php
[`DataStructureBasedFactory`]: src/DataStructureBasedFactory.php
[`SimpleCacheContainer`]: src/SimpleCacheContainer.php
[`FlashContainer`]: src/FlashContainer.php
[`NoOpContainer`]: src/NoOpContainer.php

[DDL]: https://thecodingmachine.io/psr-11-an-in-depth-view-at-the-delegate-lookup-feature
