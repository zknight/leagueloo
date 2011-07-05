<?php
namespace app;

class TournamentController extends \app\AppController
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
            "name = Tournaments",
            array()
        );
        if ($this->CheckParam("name"))
        {
            $this->tournament = \Tournament::FindOne(
                "Tournament",
                "short_name = ?",
                array($this->GetParam("name"))
            );
        }
        else
        {
            $this->tournament = \Tournament::FindById("Tournament", $this->GetParam('id'));
        }

        if ($this->tournament->id < 1)
        {
            AddFlash("tournament not found.");
        }

        return true;
    }
}
