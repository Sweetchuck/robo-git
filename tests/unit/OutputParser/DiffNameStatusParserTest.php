<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\OutputParser;

use Codeception\Test\Unit;
use Sweetchuck\Robo\Git\ListStagedFilesItem;
use Sweetchuck\Robo\Git\OutputParser\DiffNameStatusParser;

/**
 * @covers \Sweetchuck\Robo\Git\OutputParser\DiffNameStatusParser
 */
class DiffNameStatusParserTest extends Unit
{

    public function casesParse(): array
    {
        return [
            'empty' => [
                [
                    'fileNames' => [],
                    'files' => [],
                ],
                0,
                '',
                '',
            ],
            'basic' => [
                [
                    'fileNames' => [
                        'a.php',
                        'b.php',
                    ],
                    'files' => [
                        'a.php' => new ListStagedFilesItem([
                            'status' => 'M',
                            'fileName' => 'a.php',
                        ]),
                        'b.php' => new ListStagedFilesItem([
                            'status' => 'M',
                            'fileName' => 'b.php',
                        ]),
                    ],
                ],
                0,
                implode("\0", [
                    "M\0a.php",
                    "M\0b.php",
                    '',
                ]),
                '',
            ],
        ];
    }

    /**
     * @dataProvider casesParse
     */
    public function testParse(array $expected, int $exitCode, string $stdOutput, string $stdError): void
    {
        $parser = new DiffNameStatusParser();
        static::assertEquals(
            $expected,
            $parser->parse($exitCode, $stdOutput, $stdError)
        );
    }
}
