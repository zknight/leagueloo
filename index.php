<?php
require_once "simp/boot.php";
require_once "app/controllers/main.php";

$controller = new \app\MainController($router);
$controller->Dispatch($req);
?>
<html>
    <head>
        <title>test</title>
    </head>
    <body>
        <form name="foo" method="post" action="blargh">
            <input type="hidden" name="method" value="delete" />
            <script type="text/javascript">function submit() { document.foo.submit();}</script>
            <a href="javascript:submit();">delete</a>
        </form>
        <form name="bar" method="post">
            <input type="hidden" name="method" value="put" />
            <input type="text" name="foo" />
            <input type="submit" value="put" />
        </form>
        <form name="baz" method="post">
            <input type="text" name="foo" />
            <input type="submit" value="post" />
        </form>
        <a href="">get</a>
        <p>method: <?= $req->GetMethod(); ?><br />
           base path: <?= $req->GetBasePath(); ?><br />
           relative path: <?= $req->GetRelativePath(); ?><br />
           request: <pre><? print_r($req->GetRequest()); ?></pre><br />
           variables: <pre><? print_r($req->GetVariables()); ?></pre><br />
        </p>

    </body>
</html>
