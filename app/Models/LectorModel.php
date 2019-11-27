<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class LectorModel
{
    
    private $database;
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

	/** @var \App\Model\StudentModel @inject */
    public $studentModel;

    public function getCoursesOfLector($id_lector)
    {
        return $this->studentModel->getCoursesOfStudent($id_lector);
    }

    public function getLectorCourses($id_lector)
    {
        $data = $this->database->query("SELECT id_course, course_name, course_type, course_status FROM user NATURAL JOIN course_has_lecturer NATURAL JOIN course WHERE id_user = ? AND course_status != 0",  $id_lector)->fetchAll();
        if(count($data) > 0)
        {
            return $data;
        }
    }
}