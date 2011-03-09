<?php
require "lib/rb.php";

R::setup("sqlite:db/database.db");

$obj1 = R::dispense("obj");
$obj1->title = "obj1";

$obj2 = R::dispense("obj");
$obj2->title = "obj2";

$foo = R::dispense("foo");
$foo->name = "foo";
$id = R::store($foo);

$bar = R::dispense("bar");
$bar->name = "bar";
$id = R::store($bar);

$linker = new RedBean_LinkManager(R::$toolbox);
$linker->link($obj1, $foo);
$linker->link($obj2, $bar);
$id = R::store($obj1);
$id = R::store($obj2);
?>
<html>
    <head>
        <title>DB Test</title>
    </head>
    <body>
        <pre>
            <? $obj = R::findOne("obj", "title=?", array("obj2")); ?>
            obj is: <?= $obj->title; ?>
        </pre>
    </body>
</html>
<?// R::wipe("obj"); ?>
