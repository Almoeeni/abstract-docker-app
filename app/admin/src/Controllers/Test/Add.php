<?php
declare(strict_types=1);
namespace App\Admin\Controllers\Test;

use App\Admin\Controllers\AbstractAdminController;
use App\Common\Exception\AppControllerException;
use App\Common\Exception\AppException;
use App\Common\Validator;
use Comely\Database\Schema;
use Comely\Utils\Security\Passwords;

class Add extends AbstractAdminController {

    public function adminCallback(): void
    {
        $db = $this->app->db()->primary();
        echo "hello";
    }

}