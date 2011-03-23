<?
class Foo
{
    function __construct()
    {
        echo "Foo";
        echo "\n";
    }

    function doit()
    {
        if (method_exists($this, "growl"))
        {
            $this->growl();
        }
        else
        {
            echo "burp!\n";
        }
    }

    function whoami()
    {
        echo __CLASS__;
        echo "\n";
    }
}

class Bar extends Foo
{
    function whoami()
    {
        echo __CLASS__;
        echo "\n";
    }

    function growl()
    {
        echo "grrrrr!!!\n";
    }
}

$v = new Foo();
$v->whoami();
$v->doit();
$q = new Bar();
$q->whoami();
$q->doit();

