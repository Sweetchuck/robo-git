<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\OutputParser;

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Robo\Git\OutputParser\StatusParser;

#[CoversClass(StatusParser::class)]
class StatusParserTest extends Unit
{
    public static function casesParse(): array
    {
        return [
            'empty' => [
                [],
                0,
                '',
                '',
            ],
            'basic' => [
                [
                    'a.txt' => ' D',
                    'b.txt' => 'MM',
                    'c.txt' => 'D ',
                ],
                0,
                implode("\0", [
                    ' D a.txt',
                    'MM b.txt',
                    'D  c.txt',
                ]),
                '',
            ],
        ];
    }

    #[DataProvider('casesParse')]
    public function testParse(array $expected, int $exitCode, string $stdOutput, string $stdError = ''): void
    {
        $parser = new StatusParser();
        static::assertSame($expected, $parser->parse($exitCode, $stdOutput, $stdError));
    }
}
