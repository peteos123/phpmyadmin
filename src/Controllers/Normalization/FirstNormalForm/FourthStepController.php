<?php

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Normalization\FirstNormalForm;

use PhpMyAdmin\Controllers\InvocableController;
use PhpMyAdmin\Current;
use PhpMyAdmin\Http\Response;
use PhpMyAdmin\Http\ServerRequest;
use PhpMyAdmin\Normalization;
use PhpMyAdmin\ResponseRenderer;
use PhpMyAdmin\Template;

final class FourthStepController implements InvocableController
{
    public function __construct(
        private readonly ResponseRenderer $response,
        private readonly Template $template,
        private readonly Normalization $normalization,
    ) {
    }

    public function __invoke(ServerRequest $request): Response|null
    {
        $res = $this->normalization->getHtmlContentsFor1NFStep4(Current::$database, Current::$table);
        $this->response->addJSON($res);

        return null;
    }
}
