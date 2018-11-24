<?php

namespace Sweetchuck\Robo\Git\Test\Helper;

use PHPUnit\Framework\Assert;

class Unit extends \Codeception\Module
{
    /**
     * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
     * is the same.
     *
     * @param \Countable|iterable $expected
     * @param \Countable|iterable $actual
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function assertSameSize($a, $b, string $message = '')
    {
        Assert::assertSameSize($a, $b, $message);
    }
}
