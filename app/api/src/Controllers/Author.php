<?php
namespace App\API\Controllers;

use App\Common\Database\Primary\Test;
use Comely\Database\Schema;

class Author extends AbstractAPIController
{

    public function apiCallback(): void
    {
        $db = $this->app->db()->primary();
        Schema::Bind($db, 'App\Common\Database\Primary\Test');
    }
    public function get() : void
    {
        $authors=Test::List(1);
        $this->status(true);
        $this->response()->set("authors", $authors);
    }
}