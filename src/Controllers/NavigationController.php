<?php

declare(strict_types=1);

namespace PhpMyAdmin\Controllers;

use PhpMyAdmin\Config\PageSettings;
use PhpMyAdmin\ConfigStorage\Relation;
use PhpMyAdmin\Http\Response;
use PhpMyAdmin\Http\ServerRequest;
use PhpMyAdmin\Message;
use PhpMyAdmin\Navigation\Navigation;
use PhpMyAdmin\ResponseRenderer;
use PhpMyAdmin\Template;
use PhpMyAdmin\Utils\SessionCache;

use function __;

/**
 * The navigation panel
 *
 * Displays server, database and table selection tree.
 */
final class NavigationController implements InvocableController
{
    public function __construct(
        private readonly ResponseRenderer $response,
        private readonly Template $template,
        private readonly Navigation $navigation,
        private readonly Relation $relation,
        private readonly PageSettings $pageSettings,
    ) {
    }

    public function __invoke(ServerRequest $request): Response|null
    {
        if (! $request->isAjax()) {
            $this->response->addHTML(
                Message::error(
                    __('Fatal error: The navigation can only be accessed via AJAX'),
                )->getDisplay(),
            );

            return null;
        }

        if ($request->hasBodyParam('getNaviSettings')) {
            $this->pageSettings->init('Navi', 'pma_navigation_settings');
            $this->response->addHTML($this->pageSettings->getErrorHTML());
            $this->response->addJSON('message', $this->pageSettings->getHTML());

            return null;
        }

        if ($request->hasBodyParam('reload')) {
            SessionCache::set('dbs_to_test', false);// Empty database list cache, see #14252
        }

        $relationParameters = $this->relation->getRelationParameters();
        if ($relationParameters->navigationItemsHidingFeature !== null) {
            $itemName = $request->getParsedBodyParam('itemName', '');
            $itemType = $request->getParsedBodyParam('itemType', '');
            $dbName = $request->getParsedBodyParam('dbName', '');

            if ($request->getParsedBodyParam('hideNavItem') !== null) {
                if ($itemName !== '' && $itemType !== '' && $dbName !== '') {
                    $this->navigation->hideNavigationItem($itemName, $itemType, $dbName);
                }

                return null;
            }

            if ($request->hasBodyParam('unhideNavItem')) {
                if ($itemName !== '' && $itemType !== '' && $dbName !== '') {
                    $this->navigation->unhideNavigationItem($itemName, $itemType, $dbName);
                }

                return null;
            }

            if ($request->hasBodyParam('showUnhideDialog')) {
                if ($dbName !== '') {
                    $this->response->addJSON(
                        'message',
                        $this->navigation->getItemUnhideDialog($dbName),
                    );
                }

                return null;
            }
        }

        $this->response->addJSON('message', $this->navigation->getDisplay());

        return null;
    }
}
