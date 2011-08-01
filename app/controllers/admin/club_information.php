<?php
namespace app\admin;
class ClubInformationController extends \simp\Controller
{
    public function Setup()
    {
        $this->SetLayout("admin");
        $this->RequireAuthorization(
            array(
                'index',
                'update',
            )
        );

        $this->AddPreaction("all", "CheckAccess");

        $this->MapAction("index", "Update", \simp\Request::PUT);
    }

    protected function CheckAccess()
    {
        if (!$this->GetUser()->super)
        {
            AddFlash("You don't have sufficient privilege for this action.");
            \Redirect(GetReturnURL());
        }
    }

    public function Index()
    {
        $this->page = \simp\Model::FindOrCreate("Page", "short_title = ?", array("club_information"));
        $this->show_preview = false;
        return true;
    }

    public function Update()
    {
        $this->page = \simp\Model::FindOrCreate("Page", "short_title = ?", array("club_information"));
        $vars = $this->GetFormVariable('Page');
        $vars['title'] = 'Club Information';
        $vars['location'] = \Page::MAIN_MENU;
        $submit = $this->GetFormVariable('submit');
        $this->page->UpdateFromArray($vars);
        switch($submit)
        {
        case 'Preview':
            $this->show_preview = true;
            break;
        case 'Save':
            if ($this->page->Save())
            {
                AddFlash("Club Information updated.");
                Redirect(GetReturnURL());
            }
            break;
        }
        $this->SetAction("index");
        return true;
    }
}
