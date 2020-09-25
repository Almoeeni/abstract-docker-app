<?php
declare(strict_types=1);

namespace App\Common;

use App\Common\Database\AbstractAppModel;
use App\Common\Database\Primary\Test as TestTable;

/**
 * Class Country
 * @package App\Common
 */
class Test extends AbstractAppModel
{
    public const TABLE = TestTable::NAME;

    /** @var string */
    public string $book_name;
    /** @var string */
    public string $author;
    /** @var string */
    public string $email;

}
