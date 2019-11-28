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

    /**
     * Return list of lector courses
     *
     * @param [int] $id_lector
     * @return void
     */
    public function getLectorCourses($id_lector)
    {
        $data = $this->database->query("SELECT id_course, course_name, course_type, course_status FROM user NATURAL JOIN course_has_lecturer NATURAL JOIN course WHERE id_user = ? AND course_status != 0",  $id_lector)->fetchAll();
        
        if(count($data) > 0)
        {
            return $data;
        }
    }

 

    public function renderShowCourse($presenter,$id)
    {
        $this->studentModel->renderShowcourse($presenter,$id);

        $course_lectors = $this->database->query("SELECT id_user FROM user NATURAL JOIN course_has_lecturer NATURAL JOIN course WHERE id_user = ? AND id_course = ?",  $presenter->user->identity->id, $id);
        //ani lektori sa nemozu registrovat na kurzy, ktore ucia
        if($course_lectors->getRowCount() == 0)
        {
            $presenter->template->userIsNotLectorInCourse=false;
        }
            
        
    }
}