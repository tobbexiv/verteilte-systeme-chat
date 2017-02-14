<?php

namespace AppBundle\Util\Inflector;

use FOS\RestBundle\Inflector\InflectorInterface;

class NoopInflector implements InflectorInterface
{
    public function pluralize($word)
    {
        return $word;
    }
}