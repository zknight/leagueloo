<?

/*
 * /news/show/[i:id]
 * /news/show/[a:short_title]
 * /news/
 * 
 * /team/show/[i:id]
 * /team/news/show/[i:id]
 *
 * /[a:program]
 * /recreational
 * /recreational/news/short_title -> news controller with entity type program
 * /competitive/team/boys/cosmos99blue
 *
 * /[a:program]/news index of news for program
 * /[a:program]/news/[a:short_title] show only
 * /[a:program]/news/show/[i:id] 
 * /[a:program]/news/[a:action]/[i:id] show/create/edit/update/remove, depending upon method
 *                                       |     |     |     |      |
 *                      show       get  -+     |     |     |      |
 *                      add        post -------+     |     |      |
 *                      edit       get  -------------+     |      |
 *                      edit       put  -------------------+      |
 *                      delete     delete ------------------------+
 *
 * /[a:program]/team/[a:name]   team->show(name)
 * /[a:program]/team/show/[i:id]
 * /[a:program]/team/news/show/[i:id]
 * /[a:program]/team/news/
 * /[a:program]/[a:controller] -> controller index
 * /[a:program]/[a:controller]/[a:name] -> show by name
 * /[a:program]/[a:controller]/[a:action]/[i:id] cruddy stuff
 *
 * /user/login
 * /user/signup
 * need to specify controller with these
 * /user/[a:action]
 * /user/[a:action]/[i:id]
 * /administrator/[a:action]
 *
 * /admin/[a:controller]/[a:action]
 * /admin/[a:controller]/[a:action]/[i:id]
 *
 * need to specify: route, [controller], [action], [params?]
 *
 * each route has expression, controller, and action
 *
 * AddRoute() returns Route object
 */

function RouteSetup($router)
{
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
