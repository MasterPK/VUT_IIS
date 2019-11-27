<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class LectorModel
{
    

    private $mainModel;
    
    private $database;
    public function __construct(Nette\Database\Context $database, \App\Model\MainModel $mainModel)
    {
        $this->database = $database;
        $this->mainModel=$mainModel;
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