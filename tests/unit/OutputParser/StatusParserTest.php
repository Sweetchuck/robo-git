<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\OutputParser;

use Codeception\Test\Unit;
use Sweetchuck\Robo\Git\OutputParser\StatusParser;

/**
 * @covers \Sweetchuck\Robo\Git\OutputParser\StatusParser
 */
class StatusParserTest extends Unit
{
    public function casesParse(): array
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

    /**
     * @dataProvider casesParse
     */
    public function testParse(array $expected, int $exitCode, string $stdOutput, string $stdError = ''): void
    {
        $parser = new StatusParser();
        static::assertSame($expected, $parser->parse($exitCode, $stdOutput, $stdError));
    }
}
