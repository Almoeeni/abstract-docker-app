<?php
declare(strict_types=1);

namespace App\Admin\Controllers\Users;

use App\Admin\Controllers\AbstractAdminController;
use App\Common\Database\Primary\Administrators\Logs;
use App\Common\Database\Primary\Countries;
use App\Common\Database\Primary\Users;
use App\Common\Exception\AppControllerException;
use App\Common\Exception\AppException;
use App\Common\Kernel\KnitModifiers;
use App\Common\Users\User;
use App\Common\Validator;
use Comely\Database\Schema;

/**
 * Class Edit
 * @package App\Admin\Controllers\Users
 */
class Edit extends AbstractAdminController
{
    /** @var User */
    private User $user;
    /** @var User|null */
    private ?User $referrer = null;

    /**
     * @throws \Comely\Database\Exception\DbConnectionException
     */
    public function adminCallback(): void
    {
        $db = $this->app->db()->primary();
        Schema::Bind($db, 'App\Common\Database\Primary\Countries');
        Schema::Bind($db, 'App\Common\Database\Primary\Users');

        try {
            $queryUserId = explode("=", strval($this->request()->url()->query()))[0];
            $userId = Validator::UInt($this->input()->get("userId") ?? $queryUserId ?? null);
            if (!$userId) {
                throw new AppControllerException('No user selected');
            }

            $this->user = Users::get($userId);
        } catch (\Exception $e) {
            if ($e instanceof AppException) {
                $this->flash()->danger($e->getMessage());
            } else {
                $this->app->errors()->trigger($e, E_USER_WARNING);
            }

            $this->redirect($this->authRoot . "users/search");
            exit;
        }

        try {
            $this->user->validate();
        } catch (AppException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        // Referrer
        if ($this->user->referrer) {
            try {
                $referrer = Users::get($this->user->referrer);
                $this->referrer = $referrer;
            } catch (AppException $e) {
                trigger_error(sprintf('Failed to retrieve Referrer id # %d', $this->user->referrer), E_USER_WARNING);
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }
    }

    /**
     * @throws AppException
     * @throws \Comely\Knit\Exception\KnitException
     * @throws \Comely\Knit\Exception\TemplateException
     */
    public function get(): void
    {
        $this->page()->title($this->user->username)->index(1100, 10)
            ->prop("icon", "mdi mdi-account-edit-outline");

        $this->breadcrumbs("Users Control", null, "ion ion-ios-people");

        // Country
        try {
            $country = Countries::get($this->user->country);
        } catch (AppException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        // Administrative History
        try {
            $adminLogs = Logs::Find()
                ->query("WHERE `flag` IN ('users','transact') AND `flag_id`=? ORDER BY `id` DESC", [$this->user->id])
                ->all();
        } catch (\Exception $e) {
            $this->app->errors()->trigger($e, E_USER_WARNING);
        }

        // Knit Modifiers
        KnitModifiers::Dated($this->knit());
        KnitModifiers::Null($this->knit());

        $template = $this->template("users/edit.knit")
            ->assign("user", $this->user)
            ->assign("referrer", $this->referrer)
            ->assign("adminLogs", isset($adminLogs) ? $adminLogs : [])
            ->assign("country", isset($country) ? $country : [])
            ->assign("countries", Countries::List());
        $this->body($template);
    }
}
