<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\Utils\Finder;

class StudentModel
{
  
    private $visitorModel;
    private $mainModel;
    private $database;
	public function __construct(Nette\Database\Context $database, \App\Model\VisitorModel $visitorModel, \App\Model\MainModel $mainModel)
	{
        $this->database = $database;
        $this->visitorModel = $visitorModel;
        $this->mainModel = $mainModel;
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

    
        if($this->mainModel->checkOpenRegistration($id))
        {
            
            $presenter->template->openRegistration=true;
        }

        $presenter->template->userCourseStatus=$this->mainModel->checkStudentCourseStatus($id,$presenter->user->identity->id);
        
        $this->currentCourseId=$id;

        $presenter->template->course_tasks = $this->database->query("SELECT * FROM task WHERE id_course = ?", $id)->fetchAll();

        if($presenter->template->course->course_status>=1 && $presenter->template->course->course_status<=3)
        {
            $presenter->template->courseActive=true;
        }
    

    }

    public function renderFiles($presenter,$id_course,$id_task)
    {
        if($presenter->getUser()->isLoggedIn())
        {
            if($presenter->user->identity->rank == 1)
            {
                $check = $this->database->query("SELECT id_user FROM course NATURAL JOIN course_has_student NATURAL JOIN user WHERE id_course = ? AND id_user = ? AND student_status = 1", $id_course, $presenter->user->identity->id)->fetch();
            }
            elseif($presenter->user->identity->rank == 2)
            {
                $check = $this->database->query("SELECT id_user FROM course NATURAL JOIN course_has_lecturer NATURAL JOIN user WHERE id_course = ? AND id_user = ?", $id_course, $presenter->user->identity->id)->fetch();
            }
            elseif($presenter->user->identity->rank > 2)
            {
                $check = $this->database->query("SELECT id_guarantor FROM course WHERE id_course = ? AND id_guarantor = ?", $id_course, $presenter->user->identity->id)->fetch();
            }

            if($check)
            {
                $presenter->template->files=array();   
                foreach (Finder::findFiles('*')->in("Files/$id_course/$id_task") as $key => $file) {
                    array_push($presenter->template->files,["name"=>$key,"extension"=>$file->getExtension(),"size"=>$file->getSize()]); // $key je řetězec s názvem souboru včetně cesty
                }
            }
            else
            {
                $presenter->redirect("Homepage:default");
            }
        }
        else
        {
            $presenter->redirect("Homepage:default");
        }
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

    public function createComponentUnRegisterForm($presenter)
    {
		$form = new Nette\Application\UI\Form;

		$form->addHidden('id_course');

		$form->setDefaults([
            'id_course' => $this->currentCourseId,

        ]);

        $form->addSubmit('register', 'Odregistrovat kurz')
		->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');
		
		$form->onSuccess[] = [$presenter, 'unRegisterFormHandle'];
        return $form;
    }




}