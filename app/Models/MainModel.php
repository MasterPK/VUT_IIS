<?php

namespace App\Model;
use Nette;

class MainModel
{

    private $database;
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }
    /**
     * Return list of all courses in database
     * Exception on error
     * @return void
     */
    public function getAllCourses()
    {
        $data = $this->database->table("course")->fetchAll();

        if ($data) {
            return $data;
        } else {
            return NULL;
        }
    }

    /**
     * Return list of all users
     *
     * @return void
     */
    public function getAllUsers()
    {
        $data = $this->database->table("user")->fetchAll();

        if ($data) {
            return $data;
        } else {
            return NULL;
        }
    }

    /**
     * Return user detail
     *
     * @param integer $id_user
     * @return void
     */
    public function getUserDetail($id_user)
    {
        $data = $this->database->table("user")->select("id_user,first_name,surname,email,phone,rank,active")->where("id_user",$id_user)->fetch();

        if ($data) {
            return $data;
        } else {
            return NULL;
        }
    }

    /**
     * Return list of all approved courses in database
     * Exception on error
     * @return void
     */
    public function getAllApprovedCourses()
    {
        $data = $this->database->table("course")->where("course_status != 0")->fetchAll();

        if ($data) {
            return $data;
        } else {
            return NULL;
        }
    }

    /**
     * Return list of all approved courses in database that name matches filter
     * Return NULL when no result
     * @param [string] $filter
     * @return void
     */
    public function getAllCoursesByFilter(string $filter, string $search)
    {
        $query = "SELECT id_course, course_name, course_type, course_price FROM course WHERE (" . $filter . " LIKE '%" . $search . "%' AND course_status > 0)";

        $data = $this->database->query($query)->fetchAll();

        if ($data) {
            return $data;
        } else {
            return NULL;
        }
    }

    /**
     * Return list of details about specified course 
     * Exception on error
     * @param integer $course_id
     * @return void
     */
    public function getCourseDetails($course_id)
    {
        $data = $this->database->table("course")->where("id_course=?", $course_id)->fetch();

        if ($data) {
            return $data;
        } else {
            throw new \Exception("Error in SQL query");
        }
    }

    /**
     * Return full name of course guarantor
     * Exception on error
     * @param integer $id_guarantor
     * @return string
     */
    public function getCourseGuarantorName($id_guarantor)
    {
        $data = $this->database->table("user")->where("id_user=?", $id_guarantor)->fetch();

        if ($data) {
            return $data->first_name . " " . $data->surname;
        } else {
            throw new \Exception("Error in SQL query");
        }
    }

    /**
     * Check if user is Garant of course
     *
     * @param [int] $id_guarantor
     * @param [int] $id_course
     * @return boolean
     */
    public function isUserGuarantOfCourse($id_guarantor,$id_course)
    {
        $data = $this->database->table("course")->where("id_guarantor=?", $id_guarantor)->where("id_course",$id_course)->fetch();

        if ($data) {
            return true;
        } else {
            return false;
        }
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

    /**
     * Return true if course has open registration for students, otherwise false
     * Return NULL when course doesnt exist
     * @param [type] $course_id
     * @return int
     */
    public function checkOpenRegistration($course_id)
    {
        $data=$this->database->table("course")->select("course_status")->where("id_course=?", $course_id)->fetch();
        if($data)
        {
            if($data->course_status==2)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return NULL;
        }
    }
    /**
     * Return status of user in course
     * Return NULL when no record found
     * @param [type] $courseId
     * @param [type] $userId
     * @return int
     */
    public function checkStudentCourseStatus($courseId, $userId)
    {
        $data = $this->database->table("course_has_student")->select("student_status")->where("id_course=? AND id_user=?", $courseId, $userId )->fetch();
        if($data)
        {
            return $data->student_status;
        }
        else
        {
            return NULL;
        }
    }

    /**
     * Create universal search component
     *
     * @param [type] $presenter
     * @return Nette\Application\UI\Form
     */
    public function createComponentSearchCourseForm($presenter): Nette\Application\UI\Form
    {
        $form = new Nette\Application\UI\Form;

        $form->addSelect('filter', 'Filter', [
		    'course_name' => 'Název',
		    'id_course' => 'Zkratka',
		    'course_type' => 'Typ',
		    'course_price' => 'Cena',
		]);

        $form->addText('search', 'Hledat:')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired();

        $form->addSubmit('send', 'Hledat')
        ->setHtmlAttribute('class', 'btn btn-block btn-primary');
        
        $form->onSuccess[] = [$presenter, 'searchCourseForm'];
        return $form;
	}

}