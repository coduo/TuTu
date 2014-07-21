<?php

namespace Coduo\TuTu\Extension;

use Coduo\TuTu\Extension;
use Symfony\Component\ClassLoader\ClassLoader;

class Initializer
{
    /**
     * @var \Symfony\Component\ClassLoader\ClassLoader
     */
    private $classLoader;

    /**
     * @param ClassLoader $classLoader
     */
    public function __construct(ClassLoader $classLoader)
    {
        $this->classLoader = $classLoader;
    }

    public function initialize($extensionClass, $args = null)
    {
        if (!class_exists($extensionClass)) {
            if (true !== $this->classLoader->loadClass($extensionClass)) {
                throw new \InvalidArgumentException(sprintf("%s is not valid class.", $extensionClass));
            }
        }

        $extensionReflection = new \ReflectionClass($extensionClass);
        $extension = (is_array($args) && !empty($args))
            ? $extensionReflection->newInstanceArgs($args)
            : $extensionReflection->newInstance();

        if (!$extension instanceof Extension) {
            throw new \InvalidArgumentException(sprintf("Class \"%s\" should be an instance of Coduo\\TuTu\\Extension", $extensionClass));
        }

        return $extension;
    }
}
