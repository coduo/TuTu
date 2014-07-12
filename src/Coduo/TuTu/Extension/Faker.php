<?php

namespace Coduo\TuTu\Extension;

use Coduo\TuTu\Extension;
use Coduo\TuTu\Extension\Faker\Twig\Extension\FakerExtension;
use Pimple\Container;

class Faker implements Extension
{
    /**
     * @var null
     */
    private $locale;

    /**
     * @param string|null $locale
     */
    public function __construct($locale = null)
    {
        $this->locale = $locale;
    }

    /**
     * @param Container $container
     */
    public function load(Container $container)
    {
        $fakerExtension = new FakerExtension($this->locale);
        $container['twig']->addExtension($fakerExtension);
    }
}
