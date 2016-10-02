<?php

namespace Helper\Dummy;

/**
 * Class Process.
 *
 * @package Helper
 */
class Process extends \Symfony\Component\Process\Process
{

    /**
     * @var int[]
     */
    public static $exitCodes = [];

    /**
     * @var string[]
     */
    public static $stdOutputs = [];

    /**
     * @var string[]
     */
    public static $stdErrors = [];

    /**
     * @var int
     */
    public static $instanceIndex = -1;

    /**
     * @var \Helper\Dummy\Process[]
     */
    public static $instances = [];

    public static function reset()
    {
        static::$exitCodes = [];
        static::$stdOutputs = [];
        static::$stdErrors = [];
        static::$instances = [];
        static::$instanceIndex = -1;
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
        array $options = array()
    ) {
        parent::__construct($commandline, $cwd, $env, $input, $timeout, $options);

        static::$instances[] = $this;
        static::$instanceIndex++;
    }

    /**
     * {@inheritdoc}
     */
    public function run($callback = null)
    {
        return $this->getExitCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getExitCode()
    {
        $index = array_search($this, static::$instances);

        return static::$exitCodes[$index];
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        $index = array_search($this, static::$instances);

        return static::$stdOutputs[$index];
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorOutput()
    {
        $index = array_search($this, static::$instances);

        return static::$stdErrors[$index];
    }
}
