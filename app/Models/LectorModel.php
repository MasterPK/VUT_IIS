<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class LectorModel
{
    

    private $mainModel;
    private $studentModel;
    private $database;
    public function __construct(Nette\Database\Context $database, \App\Model\MainModel $mainModel , \App\Model\StudentModel $studentModel)
    {
        $this->database = $database;
        $this->mainModel=$mainModel;
        $this->studentModel=$studentModel;
    }

    public function getCoursesOfLector($id_lector)
    {
        return $this->mainModel->getCoursesOfStudent($id_lector);
    }

 

    public function renderShowCourse($presenter,$id)
    {
        $this->studentModel->renderShowcourse($presenter,$id);
    }

}