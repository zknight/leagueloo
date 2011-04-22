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
 * /[a:program]/team/[a:gender]/[a:name] -> team->show(gender, name)
 * /[a:program]/team/[a:gender]/[a:name]/news/[i:id] -> show_team_news
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
    $router->AddRoute('/')->Controller('main');
    $router->AddRoute('/main')->Controller('main');
    $router->AddRoute('/administrator/[A:action]')->Controller('administrator');
    $router->AddRoute('/administrator')->Controller('administrator');

    $router->AddRoute('/admin/[A:controller]')->Module('admin');
    $router->AddRoute('/admin/[A:controller]/[A:action]')->Module('admin');
    $router->AddRoute('/admin/[A:controller]/[A:action]/[i:id]')->Module('admin');

    $router->AddRoute('/content/[A:controller]')->Module('content');
    $router->AddRoute('/content/[A:controller]/[A:action]')->Module('content');
    $router->AddRoute('/content/[A:controller]/[A:action]/[i:id]')->Module('content');

    $router->AddRoute('/user/[A:action]')->Controller('user');
    $router->AddRoute('/user/[A:action]/[i:id]')->Controller('user');

    $router->AddRoute('/program/[i:id]')->Controller('program');
    //$router->AddRoute('/program/[A:action]')->Controller('program');
    //$router->AddRoute('/program/[A:action]/[i:id]')->Controller('program');

    $router->AddRoute('/[a:program]')->Controller('program');
    $router->AddRoute('/[a:program]/[A:controller]')->Action('index');
    $router->AddRoute('/[a:program]/[A:controller]/[A:name]')->Action('show_by_name');
    // this ain't gonna never get cawled
    $router->AddRoute('/[a:program]/[A:controller]/[A:action]');
    $router->AddRoute('/[a:program]/[A:controller]/[A:action]/[i:id]');
}
