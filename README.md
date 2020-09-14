# Dhii - Containers

[![Build Status](https://travis-ci.org/Dhii/containers.svg?branch=master)](https://travis-ci.org/Dhii/containers)
[![Code Climate](https://codeclimate.com/github/Dhii/containers/badges/gpa.svg)](https://codeclimate.com/github/Dhii/containers)
[![Test Coverage](https://codeclimate.com/github/Dhii/containers/badges/coverage.svg)](https://codeclimate.com/github/Dhii/containers/coverage)
[![Join the chat at https://gitter.im/Dhii/containers](https://badges.gitter.im/Dhii/containers.svg)](https://gitter.im/Dhii/containers?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![This package complies with Dhii standards](https://img.shields.io/badge/Dhii-Compliant-green.svg?style=flat-square)][Dhii]

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
- [`Dictionary`][] - Allows access to an array via a container interface, without sacrificing iterability. [`DataStructureBasedFactory`][] allows this to be recursive for an array hierarchy of an arbitrary depth. Useful for transforming an array into a container, especially with other decorators.

### DI
- [`ServiceProvider`][] - A super-simple implementation that allows quick creation of  [service providers][Service Provider] from known maps of factories and extensions.
- [`CompositeCachingServiceProvider`][] - A service provider that aggregates factories and extensions of other service providers. The results of this aggregation will be cached, meaing that it is only performed at most once per instance - when retrieving said factories or extensions.
- [`DelegatingContainer`][] - A container that will invoke the factories and extensions of its configured service provider before returning values. If a parent container is specified, it will be passed to the service definitions instead of this container. This allows [dependency lookup delegation][DDL], which is especially useful when composing a container out of other containers.


[Service Provider]: https://github.com/container-interop/service-provider/
[Dhii]: https://github.com/Dhii/dhii
[PSR-11]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md
[SoC]: https://dev.to/xedinunknown/separation-of-concerns-3e7d

[`ServiceProvider`]: https://github.com/Dhii/containers/blob/develop/src/ServiceProvider.php
[`CompositeCachingServiceProvider`]: https://github.com/Dhii/containers/blob/develop/src/CompositeCachingServiceProvider.php
[`DelegatingContainer`]: https://github.com/Dhii/containers/blob/develop/src/DelegatingContainer.php
[`CachingContainer`]: https://github.com/Dhii/containers/blob/develop/src/CachingContainer.php
[`CompositeContainer`]: https://github.com/Dhii/containers/blob/develop/src/CompositeContainer.php
[`ProxyContainer`]: https://github.com/Dhii/containers/blob/develop/src/ProxyContainer.php
[`AliasingContainer`]: https://github.com/Dhii/containers/blob/develop/src/AliasingContainer.php
[`MappingContainer`]: https://github.com/Dhii/containers/blob/develop/src/MappingContainer.php
[`PrefixingContainer`]: https://github.com/Dhii/containers/blob/develop/src/PrefixingContainer.php
[`DeprefixingContainer`]: https://github.com/Dhii/containers/blob/develop/src/DeprefixingContainer.php
[`MaskingContainer`]: https://github.com/Dhii/containers/blob/develop/src/MaskingContainer.php
[`PathContainer`]: https://github.com/Dhii/containers/blob/develop/src/PathContainer.php
[`SegmentingContainer`]: https://github.com/Dhii/containers/blob/develop/src/SegmentingContainer.php
[`HierarchyContainer`]: https://github.com/Dhii/containers/blob/develop/src/HierarchyContainer.php
[`Dictionary`]: https://github.com/Dhii/containers/blob/develop/src/Dictionary.php
[`DataStructureBasedFactory`]: https://github.com/Dhii/containers/blob/develop/src/DataStructureBasedFactory.php

[DDL]: https://thecodingmachine.io/psr-11-an-in-depth-view-at-the-delegate-lookup-feature
