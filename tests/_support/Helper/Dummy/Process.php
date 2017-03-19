<?php

namespace Cheppers\Robo\Git\Test\Helper\Dummy;

class Process extends \Symfony\Component\Process\Process
{
    /**
     * @var array
     */
    public static $prophecy = [];

    /**
     * @var static[]
     */
    public static $instances = null;

    public static function reset()
    {
        static::$prophecy = [];
        static::$instances = [];
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $commandline,
        $cwd = null,
        array $env = null,
        $input = null,
        $timeout = 60,
        array $options = []
    ) {
        parent::__construct($commandline, $cwd, $env, $input, $timeout, $options);

        static::$instances[] = $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run($callback = null)
    {
        $index = array_search($this, static::$instances);

        if ($callback) {
            foreach ([static::OUT, static::ERR] as $type) {
                if (static::$prophecy[$index][$type]) {
                    call_user_func($callback, $type, static::$prophecy[$index][$type]);
                }
            }
        }

        return static::$prophecy[$index]['exitCode'];
    }

    /**
     * {@inheritdoc}
     */
    public function getExitCode()
    {
        $index = array_search($this, static::$instances);

        return static::$prophecy[$index]['exitCode'];
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        $index = array_search($this, static::$instances);

        return static::$prophecy[$index][static::OUT];
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorOutput()
    {
        $index = array_search($this, static::$instances);

        return static::$prophecy[$index][static::ERR];
    }
}
