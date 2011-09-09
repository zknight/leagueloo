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
                'format' => \FormElement::TEXT,
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
                'format' => \FormElement::TEXT,
            )
        );
        $this->elements->UpdateFromArray($vars);
        if ($this->elements->Check())
        {
            //list ($div, $age, $gen) = explode(":", $this->elements->division);
            $this->format = $this->elements->format; //\simp\Model::FindOne('Division', 'name = ?', array($div));
            $this->date = strtotime($this->elements->date);
            $this->fields = \simp\Model::Find("Field", "format = ?", array($this->format));
            $this->SetAction('show');
        }
        return true;
    }

}
