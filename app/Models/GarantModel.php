<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class GarantModel
{
    /** @var Nette\Database\Context @inject */
    public $database;

	/** @var \App\Model\StudentModel @inject */
    public $studentModel;

    public function getCoursesOfGarant($id_garant)
    {
        return $this->studentModel->getCoursesOfStudent($id_garant);
    }

}