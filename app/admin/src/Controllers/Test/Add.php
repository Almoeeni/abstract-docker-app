<?php
declare(strict_types=1);
namespace App\Admin\Controllers\Test;

use App\Admin\Controllers\AbstractAdminController;
use App\Common\Exception\AppControllerException;
use App\Common\Exception\AppException;
use App\Common\Validator;
use Comely\Database\Schema;
use Comely\Utils\Security\Passwords;

/**
 * Class Add
 * @package App\Admin\Controllers\Test
 */
class Add extends AbstractAdminController {

    public function adminCallback(): void
    {

        echo "hello";
        $db = $this->app->db()->primary();

    }

    public function get()
    {
        echo "i am in get";
    }


}