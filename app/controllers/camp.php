<?php
namespace app;

class CampController extends \app\AppController
{

    function Setup()
    {
        parent::Setup();
    }

    // TODO
    // may change this later to load program and show all tournaments just for 
    // that program!  :) (have to have league assoc finder)
    // TODO: set entity info (see news.php)
    function Index()
    {
        \Redirect(\Path::tournaments());
    }

    function Show()
    {
        $this->StoreLocation();
        $this->program = \simp\Model::FindOne(
            "Program",
            "name = Camps",
            array()
        );

        if ($this->CheckParam("name"))
        {
            $this->camp = \Camp::FindOne(
                "Camp",
                "short_name = ?",
                array($this->GetParam("name"))
            );
        }
        else
        {
            $this->camp = \Camp::FindById("Camp", $this->GetParam('id'));
        }

        if ($this->camp->id < 1)
        {
            AddFlash("camp not found.");
        }
        
        return true;
    }
}
