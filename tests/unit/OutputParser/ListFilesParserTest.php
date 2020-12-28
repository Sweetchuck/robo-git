<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\OutputParser;

use Codeception\Test\Unit;
use Sweetchuck\Robo\Git\ListFilesItem;
use Sweetchuck\Robo\Git\OutputParser\ListFilesParser;

/**
 * @covers \Sweetchuck\Robo\Git\OutputParser\ListFilesParser
 */
class ListFilesParserTest extends Unit
{
    public function casesParse(): array
    {
        return [
            'empty' => [
                [],
                0,
                '',
                '',
                [],
            ],
            'minimal - \n' => [
                [
                    'a.php' => new ListFilesItem(['fileName' => 'a.php']),
                    'b.php' => new ListFilesItem(['fileName' => 'b.php']),
                    'c.php' => new ListFilesItem(['fileName' => 'c.php']),
                ],
                0,
                implode("\n", [
                    'a.php',
                    'b.php',
                    'c.php',
                    '',
                ]),
                '',
                [],
            ],
            'minimal - \0' => [
                [
                    'a.php' => new ListFilesItem(['fileName' => 'a.php']),
                    'b.php' => new ListFilesItem(['fileName' => 'b.php']),
                    'c.php' => new ListFilesItem(['fileName' => 'c.php']),
                ],
                0,
                implode("\0", [
                    'a.php',
                    'b.php',
                    'c.php',
                    '',
                ]),
                '',
                [
                    'separatedWithNullChar' => true,
                ],
            ],
            'showStaged' => [
                [
                    'a.php' => new ListFilesItem([
                        'fileName' => 'a.php',
                        'mask' => intval('100644', 8),
                        'objectName' => 'a000000',
                        'unknown' => '0',
                    ]),
                ],
                0,
                implode("\0", [
                    '100644 a000000 0 a.php',
                ]),
                '',
                [
                    'showStaged' => true,
                ],
            ],
            'fileStatusWithTags' => [
                [
                    'a.php' => new ListFilesItem([
                        'fileName' => 'a.php',
                        'status' => 'H',
                    ]),
                ],
                0,
                implode("\0", [
                    'H a.php',
                ]),
                '',
                [
                    'fileStatusWithTags' => true,
                ],
            ],
            'showLineEndings' => [
                [
                    'a.php' => new ListFilesItem([
                        'fileName' => 'a.php',
                        'eolInfoI' => 'i/lf',
                        'eolInfoW' => 'w/lf',
                        'eolAttr' => 'attr/',
                    ]),
                ],
                0,
                implode("\0", [
                    'i/lf w/lf attr/ a.php',
                ]),
                '',
                [
                    'showLineEndings' => true,
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesParse
     */
    public function testParse(array $expected, int $exitCode, string $stdOutput, string $stdError, array $options): void
    {
        $parser = new ListFilesParser();
        static::assertEquals($expected, $parser->parse($exitCode, $stdOutput, $stdError, $options));
    }
}
