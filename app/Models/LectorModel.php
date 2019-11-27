<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class LectorModel
{
    
    /** @var \App\Model\MainModel @inject */
    public $mainModel;
    
    private $database;
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

	/** @var \App\Model\StudentModel @inject */
    public $studentModel;

    public function getCoursesOfLector($id_lector)
    {
        return $this->mainModel->getCoursesOfStudent($id_lector);
    }

 

    public function renderShowCourse($presenter,$id)
    {
        $this->studentModel->renderShowcourse($presenter,$id);
    }

}