<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit;

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Robo\Git\FormatHandler;

#[CoversClass(FormatHandler::class)]
class FormatHandlerTest extends Unit
{
    protected \Sweetchuck\Robo\Git\Tests\UnitTester $tester;

    public static function casesCreateMachineReadableFormatDefinition(): array
    {
        return [
            'basic' => [
                [
                    'key' => 'refName',
                    'refSeparator' => '|',
                    'propertySeparator' => '&',
                    'keyValueSeparator' => ' ',
                    'properties' => [
                        'myProp01' => 'my.prop.01',
                        'myProp02' => 'my.prop.02',
                        'refName' => 'refname:strip=0',
                    ],
                    'format' => implode('&', [
                        'myProp01 %(my.prop.01)',
                        'myProp02 %(my.prop.02)',
                        'refName %(refname:strip=0)',
                    ]) . '|',
                ],
                [
                    'myProp01' => 'my.prop.01',
                    'myProp02' => 'my.prop.02',
                ],
            ],
        ];
    }

    #[DataProvider('casesCreateMachineReadableFormatDefinition')]
    public function testCreateMachineReadableFormatDefinition($expected, array $properties): void
    {
        $uniqueHash = function (): string {
            static $items = ['|', "&"];

            return array_shift($items);
        };
        $subject = new FormatHandler($uniqueHash);

        $this->tester->assertSame($expected, $subject->createMachineReadableFormatDefinition($properties));
    }

    public static function casesParseStdOutput(): array
    {
        return [
            'basic' => [
                [
                    '1' => [
                        'a' => '1',
                        'b' => false,
                        'c' => 'behind 42',
                        'a.short' => '1',
                        'c.gone' => false,
                        'c.ahead' => null,
                        'c.behind' => 42,
                    ],
                    '4' => [
                        'a' => '4',
                        'b' => true,
                        'c' => 'ahead 8',
                        'a.short' => '4',
                        'c.gone' => false,
                        'c.ahead' => 8,
                        'c.behind' => null,
                    ],
                    '5' => [
                        'a' => '5',
                        'b' => true,
                        'c' => '6',
                        'a.short' => '5',
                        'c.gone' => false,
                        'c.ahead' => null,
                        'c.behind' => null,
                    ],
                ],
                implode("!\n", [
                    'a 1|b 0|c behind 42',
                    'a 4|b 1|c ahead 8',
                    'a 5|b 1|c 6',
                    '',
                ]),
                [
                    'key' => 'a',
                    'refSeparator' => '!',
                    'propertySeparator' => '|',
                    'keyValueSeparator' => ' ',
                    'properties' => [
                        'a' => 'refname',
                        'b' => 'HEAD',
                        'c' => 'upstream:track',
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('casesParseStdOutput')]
    public function testParseStdOutput(array $expected, string $stdOutput, array $definition): void
    {
        $this->tester->assertEquals(
            $expected,
            (new FormatHandler())->parseStdOutput($stdOutput, $definition)
        );
    }
}
