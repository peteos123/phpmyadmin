<?php

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Normalization\SecondNormalForm;

use PhpMyAdmin\Controllers\InvocableController;
use PhpMyAdmin\Current;
use PhpMyAdmin\Http\Response;
use PhpMyAdmin\Http\ServerRequest;
use PhpMyAdmin\Normalization;
use PhpMyAdmin\ResponseRenderer;
use PhpMyAdmin\Template;

use function json_decode;

final class NewTablesController implements InvocableController
{
    public function __construct(
        private readonly ResponseRenderer $response,
        private readonly Template $template,
        private readonly Normalization $normalization,
    ) {
    }

    public function __invoke(ServerRequest $request): Response|null
    {
        $partialDependencies = json_decode($request->getParsedBodyParam('pd'), true);
        $html = $this->normalization->getHtmlForNewTables2NF($partialDependencies, Current::$table);
        $this->response->addHTML($html);

        return null;
    }
}
