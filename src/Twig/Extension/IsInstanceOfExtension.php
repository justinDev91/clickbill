<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\IsInstanceOfExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class IsInstanceOfExtension extends AbstractExtension
{
    public function getTests()
    {
        return [
            new TwigTest('instanceof', [$this, 'isInstanceOf']),
        ];
    }

    public function isInstanceOf($value, $className)
    {
        return $value instanceof $className;
    }
}