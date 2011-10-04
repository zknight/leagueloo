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
    $router->AddRoute('/main/[A:action]?/[a:name]?')->Controller('main');
    $router->AddRoute('/main/[A:action]?/[i:id]?')->Controller('main');
    $router->AddRoute('/Club/[A:action]?/[a:name]?')->Controller('main');
    $router->AddRoute('/Club/[A:action]?/[i:id]?')->Controller('main');
    $router->AddRoute('/administrator/[A:action]')->Controller('administrator');
    $router->AddRoute('/administrator/')->Controller('administrator');

    $router->AddRoute('/admin/[A:controller]')->Group('admin');
    $router->AddRoute('/admin/[A:controller]/[A:action]/[A:entity]/[i:entity_id]')->Group('admin');
    $router->AddRoute('/admin/[A:controller]/[A:action]/[i:id]')->Group('admin');
    $router->AddRoute('/admin/[A:controller]/[A:action]/[a:name]')->Group('admin');
    $router->AddRoute('/admin/[A:controller]/[A:action].[A:format]?')->Group('admin');

    $router->AddRoute('/content/')->Controller('content');
    $router->AddRoute('/content/[A:controller]')->Group('content');
    //$router->AddRoute('/content/[A:controller]/[A:action]')->Group('content');
    $router->AddRoute('/content/[A:controller]/[A:action].[A:format]?')->Group('content');
    $router->AddRoute('/content/[A:controller]/[A:action]/[i:id]')->Group('content');
    $router->AddRoute('/content/[A:controller]/[A:action]/[A:entity]?/[a:entity_id]?/[i:id]?')->Group('content');
    //$router->AddRoute('/content/[A:controller]/[A:action]/[A:entity]/[a:entity_id]')->Group('content');
    $router->AddRoute('/content/[A:controller]/[A:action]/[A:entity]/[a:entity_id]/[**:extra]?')->Group('content');
    $router->AddRoute('/content/event/[A:action]/[i:year]/[i:month]/[i:day]')
        ->Group('content')
        ->Controller('event');
    $router->AddRoute('/content/event/calendar/[i:year]/[i:month]')
        ->Group('content')
        ->Controller('event')
        ->Action('calendar');

    $router->AddRoute('/user/[A:action]')->Controller('user');
    $router->AddRoute('/user/[A:action]/[i:id]')->Controller('user');
    $router->AddRoute('/user/[A:action]/[i:id]/[a:token]')->Controller('user');

    $router->AddRoute('/message/[A:action]?/[i:id]?')->Controller('message');

    $router->AddRoute('/news/[A:action]/[i:id]')->Controller('news');
    $router->AddRoute('/page/[A:action]/[i:id]')->Controller('page');
    $router->AddRoute('/event/calendar/[i:year]/[i:month]/[a:entity_type]?/[i:id]?')
        ->Controller('event')->Action('calendar');
    $router->AddRoute('/event/[A:action]/[i:id]?')->Controller('event');
    $router->AddRoute('/event/[A:action]/[a:entity_type]?/[i:id]?')->Controller('event');

    $router->AddRoute('/staff/[A:action]?/[i:id]?')->Controller('staff');

    $router->AddRoute('/reschedule/[A:action]?/[i:id]?')->Controller('reschedule');
    $router->AddRoute('/fields/[A:action]?/[i:id]?')->Controller('fields');

    $router->AddRoute('/[a:program]/teams')->Controller('teams');
    $router->AddRoute('/[a:program]/teams/[A:gender]')->Controller('teams')->Action('by_gender');
    $router->AddRoute('/[a:program]/teams/[A:gender]/[a:division]')->Controller('teams')->Action('by_division');
    $router->AddRoute('/[a:program]/teams/[A:gender]/[a:division]/[a:name]')->Controller('teams')->Action('team');

    $router->AddRoute('/program/[i:id]')->Controller('program');
    //$router->AddRoute('/program/[A:action]')->Controller('program');
    //$router->AddRoute('/program/[A:action]/[i:id]')->Controller('program');

    $router->AddRoute('/[a:program]')->Controller('program');
    $router->AddRoute('/[a:program]/about')->Controller('program')->Action('about')->Param('type', 'program');
    $router->AddRoute('/[a:program]/[A:controller]')->Action('index')->Param('type', 'program');
    $router->AddRoute('/[a:program]/[A:controller]/[A:action]')->Param('type', 'program');
    $router->AddRoute('/[a:program]/[A:controller]/[A:action]/[i:id]')->Param('type', 'program');
    $router->AddRoute('/[a:program]/[A:controller]/[A:action]/[a:name]')->Param('type', 'program');

}
