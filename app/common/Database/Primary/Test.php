<?php
declare(strict_types=1);

namespace App\Common\Database\Primary;


use App\Common\Database\AbstractAppTable;

use Comely\Database\Exception\ORM_ModelNotFoundException;
use Comely\Database\Schema\Table\Columns;
use Comely\Database\Schema\Table\Constraints;

class Test extends AbstractAppTable
{

    public const NAME = 'tests';
    public const MODEL = 'App\Common\Test';

    /**
     * @param Columns $cols
     * @param Constraints $constraints
     */

    public function structure(Columns $cols,Constraints $constraints ): void
    {
        $cols->int('id')->bytes(4)->Unsigned()->autoIncrement();
        $cols->string('book_name')->length(32)->nullable();
        $cols->string('author')->length(32)->nullable();
        $cols->string("email")->length(32)->unique();
        $cols->int("time_stamp")->bytes(4)->unSigned();
        $cols->primaryKey("id");
    }
}