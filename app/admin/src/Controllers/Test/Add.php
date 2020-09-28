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
      //  $db = $this->app->db()->primary();
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

      $db = $this->app()->db()->primary();

        try {
            $book_name = trim(strval($this->input()->get("book_name")));
            $book_name_len = strlen($book_name);
            if(!$book_name_len){
                throw new AppControllerException("Book name is required");
            }

        } catch (AppControllerException $e){
            $e->setParam("book_name");
            throw $e;
        }

      $this->response()->set("status", true);
      $this->messages()->success("New Author account has been registered!");
      $this->messages()->info("Redirecting...");
      $this->response()->set("disabled", true);
      //$this->response()->set("redirect", $this->authRoot . "users/search?key=email&value=" . $user->email);


    }

}