<?php
declare(strict_types=1);

namespace App\Admin\Controllers\Mails;

use App\Admin\Controllers\AbstractAdminController;
use App\Common\Database\Primary\MailsQueue;
use App\Common\Exception\AppControllerException;
use App\Common\Exception\AppException;
use App\Common\Kernel\KnitModifiers;
use App\Common\Mailer\QueuedMail;
use App\Common\Validator;
use Comely\Database\Schema;

/**
 * Class Queue
 * @package App\Admin\Controllers\Mails
 */
class Queue extends AbstractAdminController
{
    private const PER_PAGE_OPTIONS = [50, 100, 250, 500];
    private const PER_PAGE_DEFAULT = self::PER_PAGE_OPTIONS[1];

    /**
     * @throws \Comely\Database\Exception\DbConnectionException
     */
    public function adminCallback(): void
    {
        $db = $this->app->db()->primary();
        Schema::Bind($db, 'App\Common\Database\Primary\MailsQueue');
    }

    /**
     * @throws AppException
     * @throws \Comely\Knit\Exception\KnitException
     * @throws \Comely\Knit\Exception\TemplateException
     */
    public function get(): void
    {
        $this->page()->title('Mails Queue')->index(910, 10)
            ->prop("containerIsFluid", true)
            ->prop("icon", "mdi mdi-mailbox-open-outline");

        $this->breadcrumbs("Mails Queue", null, "ion ion-email");

        $errorMessage = null;
        $result = [
            "status" => false,
            "count" => 0,
            "page" => null,
            "rows" => null,
            "nav" => null
        ];

        $search = [
            "email" => null,
            "subject" => null,
            "status" => null,
            "sort" => "desc",
            "perPage" => self::PER_PAGE_DEFAULT,
            "advanced" => false,
            "link" => null
        ];

        try {
            // E-mail address
            $email = trim(strval($this->input()->get("email")));
            if ($email) {
                if (!Validator::isASCII($email, "-_@.")) {
                    throw new AppControllerException('E-mail contains an illegal character');
                } elseif (strlen($email) > 64) {
                    throw new AppControllerException('E-mail address is too long');
                }

                $search["email"] = $email;
            }

            // Subject
            $subject = trim(strval($this->input()->get("subject")));
            if ($subject) {
                if (!Validator::isASCII($subject, "!-_+=@#$?<>|][}{\\/*.&%~`\"'")) {
                    throw new AppControllerException('Subject contains an illegal character');
                } elseif (strlen($subject) > 64) {
                    throw new AppControllerException('Subject is too long');
                }

                $search["subject"] = $subject;
            }

            // Status
            $status = trim(strval($this->input()->get("status")));
            if ($status) {
                if (!in_array($status, ["pending", "sent", "failed"])) {
                    throw new AppControllerException('Invalid queued mails status');
                }

                $search["status"] = $status;
            }

            // Sort By
            $sort = $this->input()->get("sort");
            if (is_string($sort) && in_array(strtolower($sort), ["asc", "desc"])) {
                $search["sort"] = $sort;
                if ($search["sort"] === "asc") {
                    $search["advanced"] = true;
                }
            }

            // Per Page
            $perPage = Validator::UInt($this->input()->get("perPage"));
            if ($perPage) {
                if (!in_array($perPage, self::PER_PAGE_OPTIONS)) {
                    throw new AppControllerException('Invalid search results per page count');
                }
            }

            $search["perPage"] = is_int($perPage) && $perPage > 0 ? $perPage : self::PER_PAGE_DEFAULT;
            if ($search["perPage"] !== self::PER_PAGE_DEFAULT) {
                $search["advanced"] = true;
            }

            $page = Validator::UInt($this->input()->get("page")) ?? 1;
            $start = ($page * $perPage) - $perPage;

            $db = $this->app->db()->primary();
            $mailsQueue = $db->query()->table(MailsQueue::NAME)
                ->limit($search["perPage"])
                ->start($start);

            if ($search["sort"] === "asc") {
                $mailsQueue->asc("id");
            } else {
                $mailsQueue->desc("id");
            }

            $whereQuery = "`id`>0";
            $whereData = [];

            // Search email
            if (isset($search["email"])) {
                $whereQuery .= ' AND `email` LIKE ?';
                $whereData[] = sprintf('%%%s%%', $search["email"]);
            }

            // Search subject
            if (isset($search["subject"])) {
                $whereQuery .= ' AND `subject` LIKE ?';
                $whereData[] = sprintf('%%%s%%', $search["subject"]);
            }

            // Status
            if (isset($search["status"])) {
                $whereQuery .= ' AND `status`=?';
                $whereData[] = $search["status"];
            }

            $mailsQueue->where($whereQuery, $whereData);
            $mails = $mailsQueue->paginate();

            $result["page"] = $page;
            $result["count"] = $mails->totalRows();
            $result["nav"] = $mails->compactNav();
            $result["status"] = true;
        } catch (AppException $e) {
            $errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->app->errors()->trigger($e, E_USER_WARNING);
            $errorMessage = "An error occurred while searching users";
        }

        if (isset($mails) && $mails->count()) {
            foreach ($mails->rows() as $queuedMailRow) {
                try {
                    $mail = new QueuedMail($queuedMailRow);
                    try {
                        $mail->validate();
                    } catch (AppException $e) {
                    }

                    $result["rows"][] = $mail;
                } catch (\Exception $e) {
                    $this->app->errors()->trigger($e, E_USER_WARNING);
                }
            }
        }

        // Search Link
        $search["link"] = $this->authRoot . sprintf(
                'mails/queue?status=%s&email=%s&subject=%s&sort=%d&perPage=%d',
                $search["status"],
                $search["email"],
                $search["subject"],
                $search["sort"],
                $search["perPage"]
            );

        // Knit Modifiers
        KnitModifiers::Dated($this->knit());
        KnitModifiers::Hex($this->knit());

        $template = $this->template("mails/queue.knit")
            ->assign("errorMessage", $errorMessage)
            ->assign("search", $search)
            ->assign("result", $result)
            ->assign("perPageOpts", self::PER_PAGE_OPTIONS);
        $this->body($template);
    }
}
