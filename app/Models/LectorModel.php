<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class LectorModel
{
    /** @var Nette\Database\Context @inject */
    public $database;

	/** @var \App\Model\StudentModel @inject */
    public $studentModel;

    public function getCoursesOfLector($id_lector)
    {
        return $this->studentModel->getCoursesOfStudent($id_lector);
    }
}