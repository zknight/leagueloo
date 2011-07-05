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

        $this->MapAction("index", "Update", \simp\Request::PUT);
    }

    public function Index()
    {
        $this->page = \simp\Model::FindOrCreate("Page", "name = ?", array("club_information"));
        $this->show_preview = false;
        return true;
    }

    public function Update()
    {
        //print_r($this->_form_vars);
        $this->page = \simp\Model::FindOrCreate("Page", "name = ?", array("club_information"));
        $vars = $this->GetFormVariable('Page');
        $vars['name'] = 'club_information';
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
