<?php

namespace GHOSTLABS\UNDERCARD\RestRoute;

use Generator;
use ReflectionClass;
use ReflectionException;

class RouteRunner
{
    
    public function collectRoutes(): Generator
    {
        foreach ($this->getClassesInNamespace(__NAMESPACE__) as $className) {
            $reflection = new ReflectionClass($className);
            $attributes = $reflection->getAttributes(CollectRoute::class);

            if ($attributes === [] || !$reflection->implementsInterface(RestRouteInterface::class)) {
                continue;
            }

            $instance = $reflection->newInstance();

            if (!$instance instanceof RestRouteInterface) {
                continue;
            }

            foreach ($attributes as $attribute) {
                yield [
                    'class' => $className,
                    'instance' => $instance,
                    'attribute' => $attribute->newInstance(),
                ];
            }
        }
    }

    private function getClassesInNamespace(string $namespace): Generator
    {
        $pluginPath = GHOSTLABS_UNDERCARD_PLUGIN_PATH . 'inc' . DIRECTORY_SEPARATOR . 'RestRoute';

        if (!is_dir($pluginPath)) {
            return;
        }

        $files = glob($pluginPath . DIRECTORY_SEPARATOR . '*.php');

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            $className = $namespace . '\\' . basename($file, '.php');
            if (class_exists($className)) {
                yield $className;
            }
        }
    }
}
