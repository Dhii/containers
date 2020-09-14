<?php

namespace Dhii\Container;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * A service provider that aggregates service definitions from other providers.
 */
class CompositeCachingServiceProvider implements ServiceProviderInterface
{
    /**
     * @var iterable|callable[]
     */
    protected $providers;

    /**
     * @var iterable|callable[]
     */
    protected $factories;

    /**
     * @var iterable|callable[]
     */
    protected $extensions;

    /**
     * @param iterable|ServiceProviderInterface[] $providers
     */
    public function __construct($providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritDoc}
     */
    public function getFactories()
    {
        if (!is_array($this->factories)) {
            $this->_indexProviderDefinitions($this->providers);
        }

        return $this->factories;
    }

    /**
     * {@inheritDoc}
     */
    public function getExtensions()
    {
        if (!is_array($this->extensions)) {
            $this->_indexProviderDefinitions($this->providers);
        }

        return $this->extensions;
    }

    /**
     * Indexes definitions in the specified service providers.
     *
     * Caches them internally.
     *
     * @param iterable|ServiceProviderInterface[] $providers The providers to index.
     */
    protected function _indexProviderDefinitions($providers)
    {
        $factories = [];
        $extensions = [];

        foreach ($providers as $provider) {
            $factories = $this->_mergeFactories($factories, $provider->getFactories());
            $extensions = $this->_mergeExtensions($extensions, $provider->getExtensions());
        }

        $this->factories = $factories;
        $this->extensions = $extensions;
    }

    /**
     * Merges two maps of factories.
     *
     * @param callable[] $defaults The factory map to merge into.
     * @param callable[] $definitions The factory map to merge. Values from here will override defaults.
     *
     * @return callable[] The merged factories.
     */
    protected function _mergeFactories(array $defaults, array $definitions): array
    {
        return array_merge($defaults, $definitions);
    }

    /**
     * Merged service extensions.
     *
     * @param callable[] $defaults
     * @param callable[] $extensions
     *
     * @return callable[] The merged extensions.
     */
    protected function _mergeExtensions(array $defaults, array $extensions): array
    {
        $merged = [];

        foreach ($extensions as $key => $extension) {
            assert(is_callable($extension));

            if (isset($defaults[$key])) {
                $default = $defaults[$key];
                $merged[$key] = function (PsrContainerInterface $c, $previous = null) use ($default, $extension) {
                    assert(is_callable($default));

                    $result = $default($c, $previous);
                    $result = $extension($c, $result);

                    return $result;
                };

                unset($defaults[$key]);
            } else {
                $merged[$key] = $extension;
            }
        }

        $merged = $this->_mergeFactories($defaults, $merged);

        return $merged;
    }
}
