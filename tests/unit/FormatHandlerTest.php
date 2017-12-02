<?php

namespace Sweetchuck\Robo\Git\Tests\Unit;

use Codeception\Test\Unit;
use Sweetchuck\Robo\Git\FormatHandler;

class FormatHandlerTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Git\Test\UnitTester
     */
    protected $tester;

    public function casesParseStdOutput(): array
    {
        return [
            'basic' => [
                [
                    '1' => [
                        'a' => '1',
                        'b' => '2',
                        'c' => '3',
                    ],
                    '4' => [
                        'a' => '4',
                        'b' => '5',
                        'c' => '6',
                    ],
                ],
                implode("!\n", [
                    'a 1|b 2|c 3',
                    'a 4|b 5|c 6',
                    '',
                ]),
                [
                    'key' => 'a',
                    'refSeparator' => '!',
                    'propertySeparator' => '|',
                    'keyValueSeparator' => ' ',
                    'properties' => [
                        'a' => '_',
                        'b' => '_',
                        'c' => '_',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesParseStdOutput
     */
    public function testParseStdOutput(array $expected, string $stdOutput, array  $definition): void
    {
        $this->tester->assertEquals(
            $expected,
            (new FormatHandler())->parseStdOutput($stdOutput, $definition)
        );
    }
}
