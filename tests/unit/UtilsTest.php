<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit;

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Robo\Git\Tests\UnitTester;
use Sweetchuck\Robo\Git\Utils;

#[CoversClass(Utils::class)]
class UtilsTest extends Unit
{
    protected UnitTester $tester;

    public static function casesParseDiffFilter(): array
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

    #[DataProvider('casesParseDiffFilter')]
    public function testParseDiffFilter(string $expected, array $diffFilter): void
    {
        $this->tester->assertSame($expected, Utils::parseDiffFilter($diffFilter));
    }
}
