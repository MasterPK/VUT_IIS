<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class GarantModel
{
    private $lectorModel;
    private $mainModel;
    private $database;
    private $current_course;

	public function __construct(Nette\Database\Context $database, \App\Model\MainModel $mainModel, \App\Model\LectorModel $lectorModel)
	{
        $this->database = $database;
        $this->mainModel = $mainModel;
        $this->lectorModel=$lectorModel;
	}
	

    public function getCoursesOfGarant($id_garant)
    {
        return $this->mainModel->getCoursesOfStudent($id_garant);
    }

    public function getGarantCourses($id_garant)
    {
        $lectorCourses = $this->lectorModel->getLectorCourses($id_garant);
        $garantCourses = $this->database->query("SELECT id_course, course_name, course_type, course_status FROM course WHERE id_guarantor = ?",  $id_garant)->fetchAll();
            
        if($lectorCourses == NULL && $garantCourses == NULL)
        {
            return NULL;
        }
        else if($lectorCourses != NULL && $garantCourses != NULL)
        {
            return array_merge($lectorCourses,$garantCourses);
        }
        else if($garantCourses != NULL)
        {
            return $garantCourses;
        }
        else return $lectorCourses;
    }

   

    private $currentCourseId;
    public function renderShowCourse($presenter,$id)
    {
        $this->lectorModel->renderShowCourse($presenter,$id);
            
        //garant sa nemoze registrovat na svoj kurz
        if($presenter->user->identity->id == $presenter->template->course->id_guarantor)
        {
            $presenter->template->userIsGuarantorInCourse=true;
        }
        else
        {
            $presenter->template->userIsGuarantorInCourse=false;
        }

        
        
        $this->currentCourseId=$id;

  
    }

    public function createComponentOpenRegisterForm($presenter)
	{
		$form = new Nette\Application\UI\Form;

		$form->addHidden('id_course');

		$form->setDefaults([
            'id_course' => $this->currentCourseId,

        ]);

        $form->addSubmit('register', 'Otevřít registrace do kurzu')
		->setHtmlAttribute('class', 'btn btn-sm btn-primary ajax');
		
		$form->onSuccess[] = [$presenter, 'openRegisterFormHandle'];
        return $form;
	}

	public function createComponentCloseRegisterForm($presenter)
	{
		$form = new Nette\Application\UI\Form;

		$form->addHidden('id_course');

		$form->setDefaults([
            'id_course' => $this->currentCourseId,

        ]);

        $form->addSubmit('register', 'Zavřít registrace do kurzu')
		->setHtmlAttribute('class', 'btn btn-sm btn-primary ajax');
		
		$form->onSuccess[] = [$presenter, 'closeRegisterFormHandle'];
        return $form;
	}

    public function getCurrentCourse($presenter, $id_course)
    {
        $this->current_course = $this->database->table("course")->where("id_course",$id_course)->fetch();
        if($this->current_course)
        {
            if($this->current_course->id_guarantor != $presenter->user->identity->id)
            {
                $presenter->redirect("Homepage:");
            }
        }
    }

}