<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit;

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Robo\Git\ListStagedFilesItem;
use Sweetchuck\Robo\Git\Tests\UnitTester;

#[CoversClass(ListStagedFilesItem::class)]
class ListStagedFilesItemTest extends Unit
{
    protected UnitTester $tester;

    public static function casesConstruct(): array
    {
        return [
            'empty' => [
                [],
                [],
            ],
            'values' => [
                [
                    'fileName' => 'foo',
                    'status' => 'AA',
                ],
                [
                    'fileName' => 'foo',
                    'status' => 'AA',
                    'foo' => 'bar',
                ],
            ],
        ];
    }

    #[DataProvider('casesConstruct')]
    public function testConstruct($expected, array $args): void
    {
        $expected += [
            'fileName' => null,
            'status' => null,
        ];

        $item = new ListStagedFilesItem($args);

        $this->tester->assertSame($expected['fileName'], $item->fileName);
        $this->tester->assertSame($expected['status'], $item->status);
    }
}
