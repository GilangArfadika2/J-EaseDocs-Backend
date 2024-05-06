<?php
namespace App\DTO\Letter;

class TemplateFieldDTO {

    public $control_id;
    public $type;
    public $label;
    public $name;
    public $option;
    public $arrayHead;
    public $arrayData;
    
    public function __construct($control_id,$type,$label,$name,$option,$arrayHead,$arrayData)
    {
        $this->control_id = $control_id;
        $this->type = $type;
        $this->label = $label;
        $this->name = $name;
        $this->option = $option;
        $this->arrayHead = $arrayHead;
        $this->arrayData = $arrayData;
    }
}