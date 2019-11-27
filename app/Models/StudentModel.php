<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class StudentModel
{

  
    public $visitorModel;

    private $database;
	public function __construct(Nette\Database\Context $database, \App\Model\VisitorModel $visitorModel)
	{
        $this->database = $database;
        $this->visitorModel=$visitorModel;
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
            if($data->course_status>=2)
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

    private $currentCourseId;
    /**
     * Handle Showcourse
     *
     * @param [type] $presenter
     * @param [type] $id
     * @return void
     */
    public function renderShowcourse($presenter,$id)
    {
        $this->visitorModel->renderShowcourse($presenter,$id);
        
    
        if($this->checkOpenRegistration($id))
        {
            
            $presenter->template->openRegistration=true;
        }

        $presenter->template->userCourseStatus=$this->checkStudentCourseStatus($id,$presenter->user->identity->id);
        dump($presenter->template->userCourseStatus);
        $this->currentCourseId=$id;
    

    }

    public function createComponentRegisterForm($presenter)
    {
		$form = new Nette\Application\UI\Form;

		$form->addHidden('id_course');

		$form->setDefaults([
            'id_course' => $this->currentCourseId,

        ]);

        $form->addSubmit('register', 'Registrovat kurz')
		->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');
		
		$form->onSuccess[] = [$presenter, 'registerFormHandle'];
        return $form;
    }




}