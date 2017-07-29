<?php

namespace Sweetchuck\Robo\Git\Tests\Unit;

use Sweetchuck\Robo\Git\Utils;
use Codeception\Test\Unit;

class UtilsTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Git\Test\UnitTester
     */
    protected $tester;

    public function casesParseTagListStdOutput(): array
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
                    'tagSeparator' => '!',
                    'propertySeparator' => '|',
                    'keyValueSeparator' => ' ',
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesParseTagListStdOutput
     */
    public function testParseTagListStdOutput(array $expected, string $stdOutput, array  $definition): void
    {
        $this->tester->assertEquals($expected, Utils::parseTagListStdOutput($stdOutput, $definition));
    }
}
