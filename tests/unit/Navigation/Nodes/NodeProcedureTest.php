<?php

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Navigation\Nodes;

use PhpMyAdmin\Config;
use PhpMyAdmin\Navigation\Nodes\NodeProcedure;
use PhpMyAdmin\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NodeProcedure::class)]
class NodeProcedureTest extends AbstractTestCase
{
    /**
     * Test for __construct
     */
    public function testConstructor(): void
    {
        $parent = new NodeProcedure(new Config(), 'default');
        self::assertSame('/database/routines', $parent->link->route);
        self::assertSame(
            ['item_type' => 'PROCEDURE', 'edit_item' => 1, 'db' => null, 'item_name' => null],
            $parent->link->params,
        );
        self::assertSame('/database/routines', $parent->icon->route);
        self::assertSame(
            ['item_type' => 'PROCEDURE', 'execute_dialog' => 1, 'db' => null, 'item_name' => null],
            $parent->icon->params,
        );
    }
}
