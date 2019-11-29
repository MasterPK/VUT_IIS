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

        $presenter->template->files=array();
        foreach ($presenter->template->course_tasks as $value) {
            $presenter->template->files[$value->id_task]=array();
            foreach (Finder::findFiles('*')->in("Files/$id/$value->id_task") as $key => $file) {
                array_push($presenter->template->files[$value->id_task],$key); // $key je řetězec s názvem souboru včetně cesty
            }
        }

        $presenter->template->course_tasks = $this->database->query("SELECT * FROM task WHERE id_course = ?", $id)->fetchAll();
		
        \Tracy\Debugger::barDump($presenter->template->course_tasks);

        if($presenter->template->course->course_status>=1)
        {
            $presenter->template->courseActive=true;
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