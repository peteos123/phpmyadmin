<?php

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Preferences;

use PhpMyAdmin\Config;
use PhpMyAdmin\Config\ConfigFile;
use PhpMyAdmin\Config\Forms\User\ImportForm;
use PhpMyAdmin\ConfigStorage\Relation;
use PhpMyAdmin\Controllers\InvocableController;
use PhpMyAdmin\Current;
use PhpMyAdmin\Http\Response;
use PhpMyAdmin\Http\ServerRequest;
use PhpMyAdmin\Message;
use PhpMyAdmin\ResponseRenderer;
use PhpMyAdmin\Template;
use PhpMyAdmin\Theme\ThemeManager;
use PhpMyAdmin\TwoFactor;
use PhpMyAdmin\Url;
use PhpMyAdmin\UserPreferences;

use function define;
use function ltrim;

final class ImportController implements InvocableController
{
    public function __construct(
        private readonly ResponseRenderer $response,
        private readonly Template $template,
        private readonly UserPreferences $userPreferences,
        private readonly Relation $relation,
        private readonly Config $config,
        private readonly ThemeManager $themeManager,
    ) {
    }

    public function __invoke(ServerRequest $request): Response|null
    {
        $GLOBALS['cf'] ??= null;
        $GLOBALS['error'] ??= null;
        $GLOBALS['tabHash'] ??= null;
        $GLOBALS['hash'] ??= null;

        $GLOBALS['cf'] = new ConfigFile($this->config->baseSettings);
        $this->userPreferences->pageInit($GLOBALS['cf']);

        $formDisplay = new ImportForm($GLOBALS['cf'], 1);

        if ($request->hasBodyParam('revert')) {
            // revert erroneous fields to their default values
            $formDisplay->fixErrors();
            $this->response->redirectToRoute('/preferences/import', []);

            return null;
        }

        $GLOBALS['error'] = null;
        if ($formDisplay->process(false) && ! $formDisplay->hasErrors()) {
            // Load 2FA settings
            $twoFactor = new TwoFactor(Config::getInstance()->selectedServer['user']);
            // save settings
            $result = $this->userPreferences->save($GLOBALS['cf']->getConfigArray());
            // save back the 2FA setting only
            $twoFactor->save();
            if ($result === true) {
                // reload config
                $this->config->loadUserPreferences($this->themeManager);
                $GLOBALS['tabHash'] = $request->getParsedBodyParam('tab_hash');
                $GLOBALS['hash'] = ltrim($GLOBALS['tabHash'], '#');
                $this->userPreferences->redirect('index.php?route=/preferences/import', null, $GLOBALS['hash']);

                return null;
            }

            $GLOBALS['error'] = $result;
        }

        $relationParameters = $this->relation->getRelationParameters();

        $this->response->render('preferences/header', [
            'route' => $request->getRoute(),
            'is_saved' => $request->hasQueryParam('saved'),
            'has_config_storage' => $relationParameters->userPreferencesFeature !== null,
        ]);

        $formErrors = $formDisplay->displayErrors();

        $this->response->render('preferences/forms/main', [
            'error' => $GLOBALS['error'] instanceof Message ? $GLOBALS['error']->getDisplay() : '',
            'has_errors' => $formDisplay->hasErrors(),
            'errors' => $formErrors,
            'form' => $formDisplay->getDisplay(
                true,
                Url::getFromRoute('/preferences/import'),
                ['server' => Current::$server],
            ),
        ]);

        if ($request->isAjax()) {
            $this->response->addJSON('disableNaviSettings', true);
        } else {
            define('PMA_DISABLE_NAVI_SETTINGS', true);
        }

        return null;
    }
}
