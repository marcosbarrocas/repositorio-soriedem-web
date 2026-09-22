<?php

namespace Source\App\Admin;

use Source\Models\Auth;
use Source\Models\Product;
use Source\Models\Request;
use Source\Models\Seller;

/**
 * Class Dash
 * @package Source\App\Admin
 */
class Dash extends Admin
{
    /**
     * Dash constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     */
    public function dash(): void
    {
        redirect("/admin/dash/home");
    }

    /**
     * @param array|null $data
     * @throws \Exception
     */
    public function home(?array $data): void
    {
        $requests = (new Request())->find()->group('request_number')->fetch(true);

        $requestCount = 0;
        // foreach ($requests as $request) {
        //     if (date_diff_system($request->created_at) == 0) {
        //         $requestCount += 1;
        //     }
        // }

        foreach ($requests as $request) {
            if (date('Y-m-d', strtotime($request->created_at)) === date('Y-m-d')) {

                $requestCount += 1;
            }
        }

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Dashboard",
            CONF_SITE_DESC,
            url("/admin"),
            theme("/assets/images/image.jpg", CONF_VIEW_ADMIN),
            false
        );

        echo $this->view->render("widgets/dash/home", [
            "app" => "dash",
            "head" => $head,
            "requestToday" => ($requestCount) ? $requestCount : 0,
            "requestPending" => (new Request())->find()->group('request_number')->count(),
            "sellers" => (new Seller())->find()->count(),
            "products" => (new Product())->find()->count()
        ]);
    }

    /**
     *
     */
    public function logoff(): void
    {
        $this->message->success("Você saiu com sucesso {$this->user->first_name}.")->flash();

        Auth::logout();
        redirect("/admin/login");
    }
}
