<?
function RouteSetup($router)
{
    // shortcuts
    $get = \simp\Request::GET;
    $post = \simp\Request::POST;
    $put = \simp\Request::PUT;
    $delete = \simp\Request::DELETE;
    $any = \simp\Request::ANY;
    $program_type = array('type' => 'Program');


    // hard-coded routes first!
    $router->AddRoute($get, '/administrator', 'administrator');
    $router->AddRoute($get, '/main', 'main');

    // user
    $router->AddRoute($post, '/user/login', 'user', 'authorize');
    $router->AddRoute($post, '/user/signup', 'user', 'create');
    $router->AddRoute($post, '/user/confirm', 'user', 'confirm_post');
    $router->AddRoute($post, '/user/request_confirmation', 'user', 'request_confirm');
    $router->AddRoute($put, '/user/edit', 'user', 'update');
    $router->AddRoute($get, '/user/[a:action]', 'user');

    // admin
    $router->AddRoute($get, '/admin/configuration', 'admin/configuration');
    $router->AddRoute($put, '/admin/configuration/update/[:id]', 'admin/configuration', 'update');

    $router->AddRoute($get, '/[a:name]', 'program');
    $router->AddRoute($get, '/program/[i:id]', 'program');
    $router->AddRoute($get, '/[a:entity]/[a:short_title]', 'news', 'show', $program_type);
    $router->AddRoute($get, '/[a:entity]/news/[i:id]', 'news', 'show', $program_type);
    $router->AddRoute($get, '/program/[i:program_id]/news/[i:id]', 'news', 'show');
}
