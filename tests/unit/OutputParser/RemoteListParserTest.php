<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\OutputParser;

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use Sweetchuck\Robo\Git\OutputParser\RemoteListParser;

/**
 * @covers \Sweetchuck\Robo\Git\OutputParser\RemoteListParser
 */
class RemoteListParserTest extends Unit
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
                    'a' => [
                        'fetch' => 'b@c.d/e.git',
                        'push' => 'b@c.d/e.git',
                    ],
                    'fg' => [
                        'fetch' => 'h@i.j/k.git',
                        'push' => 'h@i.j/k.git',
                    ],
                ],
                0,
                implode(PHP_EOL, [
                    'a  b@c.d/e.git (fetch)',
                    'a  b@c.d/e.git (push)',
                    'fg h@i.j/k.git (fetch)',
                    'fg h@i.j/k.git (push)',
                    '',
                ]),
                '',
            ],
        ];
    }

    #[DataProvider('casesParse')]
    public function testParse(array $expected, int $exitCode, string $stdOutput, string $stdError = ''): void
    {
        $parser = new RemoteListParser();
        static::assertSame($expected, $parser->parse($exitCode, $stdOutput, $stdError));
    }
}
