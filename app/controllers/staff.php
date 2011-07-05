<?php
namespace app;

class StaffController extends \simp\Controller
{
    function Setup()
    {
    }

    function Index()
    {
        $this->coaches = \simp\Model::Find("Coach", "active = ? order by title asc", array(true));
        $this->staff = \simp\Model::Find("Staff", "1 order by group_weight, weight asc", array());
        return true;
    }

    function Coaches()
    {
        $this->coaches = \simp\Model::Find("Coach", "active = ? order by title asc", array(true));
        return true;
    }

    function Coach()
    {
        $this->coach = \simp\Model::FindById("Coach", $this->GetParam("id"));
        return true;
    }

    function Administration()
    {
        $this->staff = \simp\Model::Find("Staff", "1 order by group_weight, weight asc", array());
        return true;
    }

    function StaffMember()
    {
        $this->staff_member = \simp\Model::FindById("Staff", $this->GetParam("id"));
        return true;
    }
}
