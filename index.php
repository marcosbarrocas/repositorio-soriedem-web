<?php
ob_start();

require __DIR__ . "/vendor/autoload.php";

/**
 * BOOTSTRAP
 */

use CoffeeCode\Router\Router;
use Source\Core\Session;

$session = new Session();
// RouterLocal estende o Router do CoffeeCode e, sob o servidor embutido (php -S),
// deriva a rota do REQUEST_URI (sob Apache o .htaccess injeta ?route=).
$route = new Source\Core\RouterLocal(url(), ":");
$route->namespace("Source\App");

/**
 * WEB ROUTES
 */
$route->group(null);
$route->get("/", "Web:home");


/**
 * ADMIN ROUTES
 */
$route->namespace("Source\App\Admin");
$route->group("/admin");

//login
$route->get("/", "Login:root");
$route->get("/login", "Login:login");
$route->post("/login", "Login:login");

//dash
$route->get("/dash", "Dash:dash");
$route->get("/dash/home", "Dash:home");
$route->post("/dash/home", "Dash:home");
$route->get("/logoff", "Dash:logoff");

//users
$route->get("/users/home", "Users:home");
$route->post("/users/home", "Users:home");
$route->get("/users/home/{search}/{page}", "Users:home");
$route->get("/users/user", "Users:user");
$route->post("/users/user", "Users:user");
$route->get("/users/user/{user_id}", "Users:user");
$route->post("/users/user/{user_id}", "Users:user");

//sellers
$route->get("/sellers/home", "Sellers:home");
$route->post("/sellers/home", "Sellers:home");
$route->get("/sellers/home/{search}/{page}", "Sellers:home");
$route->get("/sellers/omie", "Sellers:omie");
$route->post("/sellers/omie", "Sellers:omie");
$route->post("/sellers/create-login", "Sellers:createLogin");
$route->get("/sellers/access/{omie_codigo}", "Sellers:access");
$route->post("/sellers/access/{omie_codigo}", "Sellers:access");
$route->get("/sellers/seller", "Sellers:seller");
$route->post("/sellers/seller", "Sellers:seller");
$route->get("/sellers/seller/{seller_id}", "Sellers:seller");
$route->post("/sellers/seller/{seller_id}", "Sellers:seller");

//clients
$route->get("/clients/home", "Clients:home");
$route->post("/clients/home", "Clients:home");
$route->get("/clients/home/{search}/{page}", "Clients:home");
$route->get("/clients/client", "Clients:client");
$route->post("/clients/client", "Clients:client");
$route->get("/clients/client/{client_id}", "Clients:client");
$route->post("/clients/client/{client_id}", "Clients:client");

//providers
$route->get("/providers/home", "Providers:home");
$route->post("/providers/home", "Providers:home");
$route->get("/providers/home/{search}/{page}", "Providers:home");
$route->get("/providers/provider", "Providers:provider");
$route->post("/providers/provider", "Providers:provider");
$route->get("/providers/provider/{provider_id}", "Providers:provider");
$route->post("/providers/provider/{provider_id}", "Providers:provider");

//categories
$route->get("/categories/home", "Categories:home");
$route->post("/categories/home", "Categories:home");
$route->get("/categories/home/{search}/{page}", "Categories:home");
$route->get("/categories/category", "Categories:category");
$route->post("/categories/category", "Categories:category");
$route->get("/categories/category/{category_id}", "Categories:category");
$route->post("/categories/category/{category_id}", "Categories:category");
$route->post("/categories/selectCategory/{category_id}", "Categories:selectCategory");

//sub-categories
$route->get("/sub-categories/home", "SubCategories:home");
$route->post("/sub-categories/home", "SubCategories:home");
$route->get("/sub-categories/home/{search}/{page}", "SubCategories:home");
$route->get("/sub-categories/sub-category", "SubCategories:subCategory");
$route->post("/sub-categories/sub-category", "SubCategories:subCategory");
$route->get("/sub-categories/sub-category/{sub-category_id}", "SubCategories:subCategory");
$route->post("/sub-categories/sub-category/{sub-category_id}", "SubCategories:subCategory");

//products
$route->get("/products/home", "Products:home");
$route->post("/products/home", "Products:home");
$route->get("/products/home/{search}/{page}", "Products:home");
$route->get("/products/product", "Products:product");
$route->post("/products/product", "Products:product");
$route->get("/products/product/{product_id}", "Products:product");
$route->post("/products/product/{product_id}", "Products:product");
$route->post("/products/get-products/{subcategory_id}", "Products:getProducts");
$route->post("/products/get-products-client/{client_id}", "Products:getProductsClient");
$route->post("/products/search", "Products:searchProducts");
$route->get("/products/photos", "Products:photos");
$route->post("/products/photos", "Products:photos");
$route->get("/products/photos/{search}/{page}", "Products:photos");
$route->post("/products/link-photo", "Products:linkPhoto");

//requests
$route->get("/requests/home", "Requests:home");
$route->post("/requests/home", "Requests:home");
$route->get("/requests/home/{search}/{page}", "Requests:home");
$route->get("/requests/request", "Requests:request");
$route->post("/requests/request", "Requests:request");
$route->get("/requests/request/{request_id}", "Requests:request");
$route->post("/requests/request/{request_id}", "Requests:request");

//clients-products
$route->get("/clients-products/home", "ClientsProducts:home");
$route->post("/clients-products/home", "ClientsProducts:home");
$route->get("/clients-products/home/{search}/{page}", "ClientsProducts:home");
$route->get("/clients-products/add/{client_id}", "ClientsProducts:clientProducts");
$route->get("/clients-products/client-products", "ClientsProducts:clientProducts");
$route->post("/clients-products/client-products", "ClientsProducts:clientProducts");
$route->get("/clients-products/client-products/{clientProducts_id}", "ClientsProducts:clientProducts");
$route->post("/clients-products/client-products/{clientProducts_id}", "ClientsProducts:clientProducts");
$route->get("/clients-products/list-products/{clientProducts_id}", "ClientsProducts:listProducts");

//notification center
$route->post("/notifications/count", "Notifications:count");
$route->post("/notifications/list", "Notifications:list");

/**
 * ERROR ROUTES
 */
$route->group("/ops");
$route->get("/{errcode}", "Web:error");

/**
 * ROUTE
 */
$route->dispatch();

/**
 * ERROR REDIRECT
 */
if ($route->error()) {
    $route->redirect("/ops/{$route->error()}");
}

ob_end_flush();