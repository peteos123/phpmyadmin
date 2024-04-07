<?php

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Sql;

use PhpMyAdmin\Controllers\InvocableController;
use PhpMyAdmin\Current;
use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Http\Response;
use PhpMyAdmin\Http\ServerRequest;
use PhpMyAdmin\Message;
use PhpMyAdmin\ResponseRenderer;
use PhpMyAdmin\Table\Table;
use PhpMyAdmin\Template;

use function array_map;
use function explode;
use function intval;
use function is_string;

final class ColumnPreferencesController implements InvocableController
{
    public function __construct(
        private readonly ResponseRenderer $response,
        private readonly Template $template,
        private readonly DatabaseInterface $dbi,
    ) {
    }

    public function __invoke(ServerRequest $request): Response|null
    {
        $tableObject = $this->dbi->getTable(Current::$database, Current::$table);
        $status = false;

        /** @var string|null $tableCreateTime */
        $tableCreateTime = $request->getParsedBodyParam('table_create_time');

        // set column order
        $colorder = $request->getParsedBodyParam('col_order');
        if (is_string($colorder)) {
            $propertyValue = array_map(intval(...), explode(',', $colorder));
            $status = $tableObject->setUiProp(Table::PROP_COLUMN_ORDER, $propertyValue, $tableCreateTime);
        }

        // set column visibility
        $colvisib = $request->getParsedBodyParam('col_visib');
        if ($status === true && is_string($colvisib)) {
            $propertyValue = array_map(intval(...), explode(',', $colvisib));
            $status = $tableObject->setUiProp(Table::PROP_COLUMN_ORDER, $propertyValue, $tableCreateTime);
        }

        if ($status instanceof Message) {
            $this->response->setRequestStatus(false);
            $this->response->addJSON('message', $status->getString());

            return null;
        }

        $this->response->setRequestStatus($status);

        return null;
    }
}
