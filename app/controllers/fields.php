<?php
namespace app;

class FieldsController extends \app\AppController
{
    function Setup()
    {
        $this->MapAction('index', 'Show', \simp\Request::POST);
    }

    function Index()
    {
        $this->StoreLocation();
        $this->elements = new \FormElement(
            array(
                'date' => \FormElement::DATE,
                'division' => \FormElement::TEXT,
            )
        );

        return true;
    }

    function Show()
    {
        $vars = $this->GetFormVariable('FormElement');
        $this->elements = new \FormElement(
            array(
                'date' => \FormElement::DATE,
                'division' => \FormElement::TEXT,
            )
        );
        $this->elements->UpdateFromArray($vars);
        if ($this->elements->Check())
        {
            list ($div, $age, $gen) = explode(":", $this->elements->division);
            $this->division = \simp\Model::FindOne('Division', 'name = ?', array($div));
            $this->date = strtotime($this->elements->date);
            $this->SetAction('show');
        }
        return true;
    }

}
