<?php

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Normalization\ThirdNormalForm;

use PhpMyAdmin\Controllers\InvocableController;
use PhpMyAdmin\Current;
use PhpMyAdmin\Http\Response;
use PhpMyAdmin\Http\ServerRequest;
use PhpMyAdmin\Normalization;
use PhpMyAdmin\ResponseRenderer;
use PhpMyAdmin\Template;

use function json_decode;

final class CreateNewTablesController implements InvocableController
{
    public function __construct(
        private readonly ResponseRenderer $response,
        private readonly Template $template,
        private readonly Normalization $normalization,
    ) {
    }

    public function __invoke(ServerRequest $request): Response|null
    {
        $newtables = json_decode($request->getParsedBodyParam('newTables'), true);
        $res = $this->normalization->createNewTablesFor3NF($newtables, Current::$database);
        $this->response->addJSON($res);

        return null;
    }
}
