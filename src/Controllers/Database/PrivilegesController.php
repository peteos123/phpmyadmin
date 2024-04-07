<?php
/**
 * Controller for database privileges
 */

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Database;

use PhpMyAdmin\Config;
use PhpMyAdmin\Controllers\InvocableController;
use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Http\Response;
use PhpMyAdmin\Http\ServerRequest;
use PhpMyAdmin\Identifiers\DatabaseName;
use PhpMyAdmin\Identifiers\InvalidDatabaseName;
use PhpMyAdmin\LanguageManager;
use PhpMyAdmin\Message;
use PhpMyAdmin\ResponseRenderer;
use PhpMyAdmin\Server\Privileges;
use PhpMyAdmin\Template;
use PhpMyAdmin\Util;

use function __;
use function mb_strtolower;

/**
 * Controller for database privileges
 */
final class PrivilegesController implements InvocableController
{
    public function __construct(
        private readonly ResponseRenderer $response,
        private readonly Template $template,
        private readonly Privileges $privileges,
        private readonly DatabaseInterface $dbi,
    ) {
    }

    public function __invoke(ServerRequest $request): Response|null
    {
        try {
            $db = DatabaseName::from($request->getParam('db'));
            if ($this->dbi->getLowerCaseNames() === 1) {
                $db = DatabaseName::from(mb_strtolower($db->getName()));
            }
        } catch (InvalidDatabaseName $exception) {
            $this->response->addHTML(Message::error($exception->getMessage())->getDisplay());

            return null;
        }

        $this->response->addScriptFiles(['server/privileges.js', 'vendor/zxcvbn-ts.js']);

        /**
         * Checks if the user is allowed to do what they try to...
         */
        $isGrantUser = $this->dbi->isGrantUser();
        $isCreateUser = $this->dbi->isCreateUser();

        if (! $this->dbi->isSuperUser() && ! $isGrantUser && ! $isCreateUser) {
            $this->response->render('server/sub_page_header', ['type' => 'privileges', 'is_image' => false]);
            $this->response->addHTML(
                Message::error(__('No Privileges'))
                    ->getDisplay(),
            );

            return null;
        }

        if (! $isGrantUser && ! $isCreateUser) {
            $this->response->addHTML(Message::notice(
                __('You do not have the privileges to administrate the users!'),
            )->getDisplay());
        }

        $scriptName = Util::getScriptNameForOption(Config::getInstance()->settings['DefaultTabDatabase'], 'database');

        $privileges = [];
        if ($this->dbi->isSuperUser()) {
            $privileges = $this->privileges->getAllPrivileges($db);
        }

        $this->response->render('database/privileges/index', [
            'is_superuser' => $this->dbi->isSuperUser(),
            'db' => $db->getName(),
            'database_url' => $scriptName,
            'text_dir' => LanguageManager::$textDir,
            'is_createuser' => $this->dbi->isCreateUser(),
            'is_grantuser' => $this->dbi->isGrantUser(),
            'privileges' => $privileges,
        ]);
        $this->response->render('export_modal', []);

        return null;
    }
}
