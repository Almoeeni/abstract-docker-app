<?php
declare(strict_types=1);
namespace App\Admin\Controllers\Test;

use App\Admin\Controllers\AbstractAdminController;
use App\Common\Exception\AppControllerException;
use App\Common\Exception\AppException;
use App\Common\Database\Primary\Test as TestTable;
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
//        Schema::Bind($db, 'App\Common\Database\Primary\Countries');
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


      $this->response()->set("status", true);
      $this->messages()->success("New Author account has been registered!");
      $this->messages()->info("Redirecting...");
      $this->response()->set("disabled", true);
      //$this->response()->set("redirect", $this->authRoot . "users/search?key=email&value=" . $user->email);


    }

}