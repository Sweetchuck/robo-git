<?php

declare(strict_types=1);


namespace Sweetchuck\Robo\Git\Tests\Acceptance;

use Robo\Robo;

class CestBase
{
    /**
     * @var string
     */
    protected $expectedDir = 'tests/_data/expected';

    public function __construct()
    {
        $this->expectedDir = codecept_data_dir('expected');
    }

    public function _after()
    {
        Robo::createDefaultContainer();
    }
}
