<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit;

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Robo\Git\ListFilesItem;

#[CoversClass(ListFilesItem::class)]
class ListFilesItemTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Git\Tests\UnitTester
     */
    protected $tester;

    public static function casesToString(): array
    {
        return [
            'basic' => [
                'a/b/c.php',
                [
                    'fileName' => 'a/b/c.php',
                ],
            ],
        ];
    }

    #[DataProvider('casesToString')]
    public function testToString($expected, array $args): void
    {
        $item = new ListFilesItem($args);

        $this->tester->assertSame($expected, (string) $item);
    }
}
