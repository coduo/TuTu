<?php

namespace Coduo\TuTu\Extension\Faker\Twig\Extension;

use Faker\Factory;

class FakerExtension extends \Twig_Extension
{
    /**
     * @var null
     */
    private $locale;

    public function __construct($locale = null)
    {
        $this->locale = $locale;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'tutu.faker.extension';
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        $fakerFactory = new Factory();
        return [
            'faker' => (isset($this->locale))
                    ? $fakerFactory->create($this->locale)
                    : $fakerFactory->create()
        ];
    }

}
