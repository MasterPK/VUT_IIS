<?php

namespace App\Model;


use Nette;

class StudentModel
{

    private $database;
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function getCoursesOfStudent($id_student)
    {
        $data = $this->database->query("SELECT id_course, course_name, course_type, course_price FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE id_user = ? AND student_status = 1 AND course_status != 0",  $id_student);

		if($data->getRowCount() > 0)
		{
			return $data;
        }
        else
        {
            throw new \Exception("Error in SQL query");
        }
    }

}