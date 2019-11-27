<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class StudentModel
{
  
    public $visitorModel, $mainModel;

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