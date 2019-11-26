<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class StudentModel
{

    /** @var \App\Model\VisitorModel @inject */
    public $visitorModel;

    private $database;
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

    /**
     * Return list of courses, where is student registered
     * Exception on error
     * @param [type] $id_student
     * @return void
     */
    public function getCoursesOfStudent($id_student)
    {
        $data = $this->database->query("SELECT id_course, course_name, course_type, course_price FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE id_user = ? AND student_status = 1 AND course_status != 0",  $id_student);

		if($data->getRowCount() > 0)
		{
			return $data;
        }
        else
        {
            return NULL;
        }
    }

    public function renderShowcourse($presenter,$id)
    {

    }


}