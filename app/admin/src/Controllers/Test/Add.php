<?php
declare(strict_types=1);
namespace App\Admin\Controllers\Test;

use App\Admin\Controllers\AbstractAdminController;
use App\Common\Exception\AppControllerException;
use App\Common\Exception\AppException;
use App\Common\Database\Primary\Test as TestTable;
use App\Common\Test;
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
        $db = $this->app->db()->primary();
        Schema::Bind($db, 'App\Common\Database\Primary\Test');
//        Schema::Bind($db, 'App\Common\Database\Primary\Users');
    }

    public function get()
    {
        $this->page()->title('Create Test')->index(610, 20)
            ->prop("icon", "mdi mdi-account-plus-outline");

        $this->breadcrumbs("Test Control", null, "ion ion-ios-people");
        $template = $this->template("/test/add.knit");
         //   ->assign("form", $form->array());
        $this->body($template);
        // Response
     /*   $this->response()->set("status", true);
        $this->response()->set("disabled", true);
        $this->messages()->success('You have been logged in!');
        $this->messages()->info('Please wait...');
        $this->response()->set("redirect", $this->request()->url()->root($authToken->base16()->hexits() . "/dashboard"));
        return;*/
    }

    public function post () : void
    {
        $this->verifyXSRF();
       // $this->totpSessionCheck();

        if (!$this->authAdmin->privileges()->root()) {
            if (!$this->authAdmin->privileges()->manageUsers) {
                throw new AppControllerException('You do not have permission to add new user');
            }
        }

        $db = $this->app->db()->primary();

        try {
            $book_name = trim(strval($this->input()->get("book_name")));
            $book_name_len = strlen($book_name);
            if(!$book_name_len){
                throw new AppControllerException("Book name is required");
            }
            elseif($book_name_len < 3){
                throw new AppControllerException("Book name is too short");
            }
            elseif($book_name_len > 32){
                throw new AppControllerException("Book name is too Long");
            }

        } catch (AppControllerException $e){
            $e->setParam("book_name");
            throw $e;
        }

// E-mail address
        try {
            $email = trim(strval($this->input()->get("email")));
            if (!$email) {
                throw new AppControllerException('E-mail address is required');
            } elseif (strlen($email) > 64) {
                throw new AppControllerException('E-mail address is too long');
            } elseif (!Validator::isValidEmailAddress($email)) {
                throw new AppControllerException('Invalid e-mail address');
            }

            // Duplicate Check
            $dup = $db->query()->table(TestTable::NAME)
                ->where('`email`=?', [$email])
                ->fetch();
            if ($dup->count()) {
                throw new AppControllerException('E-mail address is already registered!');
            }
        } catch (AppControllerException $e) {
            $e->setParam("email");
            throw $e;
        }

        try {
            $author_name = trim(strval($this->input()->get("author_name")));
            $author_name_len = strlen($author_name);
            if(!$author_name_len){
                throw new AppControllerException("Book name is required");
            }
            elseif($author_name_len < 3){
                throw new AppControllerException("Book name is too short");
            }
            elseif($author_name_len > 32){
                throw new AppControllerException("Book name is too Long");
            }

        } catch (AppControllerException $e){
            $e->setParam("author_name");
            throw $e;
        }

       // try {
           // $db->beginTransaction();
            $author = new Test();
            $author->id = 0;
            $author->bookName = $book_name;
            $author->email = $email;
            $author->author = $author_name;
            $author->timeStamp = time();
            $author->query()->insert(function () {
                throw new AppControllerException('Failed to insert author row');
            });
//        }catch (AppException $e) {
//            print_r($e);
//            $db->rollBack();
//            throw $e;
//        }
        $this->response()->set("status", true);
        $this->messages()->success("New Author account has been registered!");
        $this->messages()->info("Redirecting...");
        $this->response()->set("disabled", true);
        $this->response()->set("redirect", $this->authRoot . "test/add");


    }

    public function  getList() : void
    {
        $db = $this->app->db()->primary();
        $authors = $db->query()->table(TestTable::NAME)->fetch();

        $this->page()->title('Author Listing')->index(610, 20)
            ->prop("icon", "mdi mdi-account-plus-outline");
        $this->breadcrumbs("Test Control", null, "ion ion-ios-people");
        $template = $this->template("/test/author.knit")->assign("authors", $authors->all());
        $this->body($template);
    }

    public function getEdit()
    {
        $author_id = $this->input()->get("id");
        $db = $this->app->db()->primary();
        $author = $db->query()->table(TestTable::NAME)->where('`id`=?', [$author_id])->fetch()->first();
//        echo "<pre>";
//        var_dump($author);
//        exit;
        $template = $this->template("/test/edit.knit")->assign("editAuthor",$author);
        $this->body($template);


    }

}