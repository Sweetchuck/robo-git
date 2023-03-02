<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit;

use Codeception\Test\Unit;
use Sweetchuck\Robo\Git\Utils;

/**
 * @covers \Sweetchuck\Robo\Git\Utils
 */
class UtilsTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Git\Test\UnitTester
     */
    protected $tester;

    public function casesParseDiffFilter(): array
    {
        return [
            'empty' => ['', []],
            'basic' => [
                'Ab',
                [
                    'n' => null,
                    'a' => true,
                    'b' => false,
                ],
            ],
            'cases' => [
                'aB',
                [
                    'n' => null,
                    'a' => true,
                    'A' => false,
                    'B' => false,
                    'b' => true,
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesParseDiffFilter
     */
    public function testParseDiffFilter(string $expected, array $diffFilter)
    {
        $this->tester->assertSame($expected, Utils::parseDiffFilter($diffFilter));
    }
}
