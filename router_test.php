<?
function test($router, $request)
{
    $controller_path = '';
    $controller = '';
    if ($router->GetController($request, $controller, $controller_path))
    {
        echo "**{$controller}->{$controller_path}\n";
    }
    else
    {
        echo "!! no controller found.\n";
    }
    echo "\n** remaining request parameters: ";
    echo (implode("/", $request)) . "\n\n";
}

$APP_DIR = "app/";

require_once("simp/router.php");
$router = new \simp\Router();

test($router, array( "foo", "bar"));
test($router, array( "test", "foo"));
test($router, array( "test" ));
test($router, array( "admin", "foo" ));
test($router, array( "admin", "event" ));
test($router, array( "news", "1", "article", "2" ));
test($router, array( "event", "3" ));
