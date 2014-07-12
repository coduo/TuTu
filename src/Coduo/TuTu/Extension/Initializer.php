<?php

namespace Coduo\TuTu\Extension;

use Coduo\TuTu\Extension;

class Initializer
{
    public function initialize($extensionClass, $args = null)
    {
        if (!class_exists($extensionClass)) {
            throw new \InvalidArgumentException(sprintf("%s is not valid class.", $extensionClass));
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
