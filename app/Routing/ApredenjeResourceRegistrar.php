<?php

namespace App\Routing;

use Illuminate\Routing\ResourceRegistrar;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;

class ApredenjeResourceRegistrar extends ResourceRegistrar
{
    /**
     * Get the name for a given resource.
     *
     * @param  string  $resource
     * @param  string  $method
     * @param  array  $options
     */
    protected function getResourceRouteName($resource, $method, $options): string
    {
        $name = $resource;

        if (isset($options['names'])) {
            if (is_string($options['names'])) {
                $name = $options['names'];
            } elseif (isset($options['names'][$method])) {
                return $options['names'][$method];
            }
        }

        $prefix = isset($options['as']) ? $options['as'].'.' : '';

        return trim(sprintf('%s%s.%s', $prefix, $name, $method == 'index' ? '' : $method), '.');
    }

    /**
     * Route a resource to a controller.
     *
     * @param  string  $name
     * @param  string  $controller
     * @return RouteCollection
     */
    public function register($name, $controller, array $options = [])
    {
        if (isset($options['parameters']) && ! isset($this->parameters)) {
            $this->parameters = $options['parameters'];
        }

        if (str_contains($name, '/')) {
            $this->prefixedResource($name, $controller, $options);

            return;
        }

        $base = $this->getResourceWildcard(last(explode('.', $name)));

        $defaults = $this->resourceDefaults;

        $collection = new RouteCollection;

        $resourceMethods = $this->getResourceMethods($defaults, $options);

        foreach ($resourceMethods as $m) {
            $optionsForMethod = $options;

            if (isset($optionsForMethod['middleware_for'][$m])) {
                $optionsForMethod['middleware'] = $optionsForMethod['middleware_for'][$m];
            }

            if (isset($optionsForMethod['excluded_middleware_for'][$m])) {
                $optionsForMethod['excluded_middleware'] = Router::uniqueMiddleware(array_merge(
                    $optionsForMethod['excluded_middleware'] ?? [],
                    $optionsForMethod['excluded_middleware_for'][$m]
                ));
            }

            $name = str($name)
                ->split('/\./')
                ->map(fn ($value, $key) => $key < count(explode('.', $name)) - 1 ? str($value)->singular() : $value)
                ->join('.');

            $route = $this->{'addResource'.ucfirst($m)}(
                strtolower($m) == 'index' ? str($name)->plural()->toString() : str($name)->singular()->toString(),
                $base,
                $controller,
                $optionsForMethod
            );

            if (isset($options['bindingFields'])) {
                $this->setResourceBindingFields($route, $options['bindingFields']);
            }

            if (isset($options['trashed']) &&
                in_array($m, ! empty($options['trashed']) ? $options['trashed'] : array_intersect($resourceMethods, ['show', 'edit', 'update']))) {
                $route->withTrashed();
            }

            $collection->add($route);
        }

        return $collection;
    }
}
