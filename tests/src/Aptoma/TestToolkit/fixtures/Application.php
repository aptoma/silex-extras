<?php

namespace Mock;

use Silex\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);
    }
}
