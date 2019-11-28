<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class GarantPresenter extends Nette\Application\UI\Presenter 
{
	/** @var \App\Model\StartUp @inject */
    public $startup;

	/** @var Nette\Database\Context @inject */
	public $database;

	/** @var \App\Model\GarantModel @inject */
	public $garantModel;

	/** @var \App\Model\StudentModel @inject */
	public $studentModel;

	/** @var \App\Model\MainModel @inject */
	public $mainModel;

	private $item;

	public function startUp()
	{
		parent::startup();

		
		$this->startup->mainStartUp($this);
		if(!$this->startup->roleCheck($this,3))
		{
			$this->redirect("Homepage:default");
		}
	}
	public function renderDefault(): void
	{ }

	/**
	 * Generuje aktuálne zapsané predmety lektora
	 *
	 * @return void
	 */
	public function renderCourses(): void
	{
		$this->template->courses=$this->mainModel->getAllCourses();
	}

	public function renderMycourses(): void
	{
		$this->template->courses=$this->mainModel->getCoursesOfStudent($this->user->identity->id);	
	}

	public function renderManagecourses(): void
	{
		$this->template->courses = $this->garantModel->getGarantCourses($this->user->identity->id);
	}

	public function rendershowCourse($id)
	{
		$this->garantModel->renderShowCourse($this,$id);
	}
	
	public function createComponentCreateCourseForm(): Form
	{
		return $this->garantModel->createCourseF($this);
	}

	public function createCourseForm(Nette\Application\UI\Form $form): void
    {
    	$values = $form->getValues();

    	try
    	{
    		$data = $this->database->query("INSERT INTO course (id_course, course_name, course_description, course_type, course_price, id_guarantor, course_status) VALUES (?, ?, ?, ?, ?, ?, 0)", $values->id_course, $values->name, $values->description, $values->type, $values->price,  $this->user->identity->id);

    		$this->template->success_insert = true;
    	}
    	catch(Nette\Database\UniqueConstraintViolationException $e)
    	{
    		$this->template->error_insert=true;
    		$this->template->error_course=$values->id_course;
    	}
	}

	public function createComponentRegisterForm()
	{
		return $this->studentModel->createComponentRegisterForm($this);
	}

	public function createComponentUnRegisterForm()
	{
		return $this->studentModel->createComponentUnRegisterForm($this);
	}

	public function createComponentOpenRegisterForm()
	{
		return $this->garantModel->createComponentOpenRegisterForm($this);
	}

	public function createComponentCloseRegisterForm()
	{
		return $this->garantModel->createComponentCloseRegisterForm($this);
	}

	public function openRegisterFormHandle($form)
	{
		$values = $form->getValues();
		$get = $this->database->query("UPDATE course SET course_status = 2 WHERE id_course = ?", $values->id_course);

    	if($get->getRowCount() == 1)
    	{
    		$this->template->succes_notif = true;
    	}
    	else
    	{
    		$this->template->error_notif = false;
    	}	

    	if ($this->isAjax())
		{
            $this->redrawControl('content_snippet');
        }
	}

	public function closeRegisterFormHandle($form)
	{
		$values = $form->getValues();
		$get = $this->database->query("UPDATE course SET course_status = 3 WHERE id_course = ?", $values->id_course);

    	if($get->getRowCount() == 1)
    	{
    		$this->template->succes_notif = true;
    	}
    	else
    	{
    		$this->template->error_notif = false;
    	}	

    	if ($this->isAjax())
		{
            $this->redrawControl('content_snippet');
        }
	}

	public function createComponentSearchCourseForm(): Nette\Application\UI\Form
    {
        return $this->mainModel->createComponentSearchCourseForm($this);
	}
	
	public function searchCourseForm(Nette\Application\UI\Form $form): void
    {
    	$values = $form->getValues();
    	$this->redirect("Homepage:courses", $values->search, $values->filter);
	}

	public function createComponentCreateTaskForm(): Nette\Application\UI\Form
    {
        $form = new Nette\Application\UI\Form;

        $form->addHidden('id_course');
        $form->setDefaults([
            'id_course' => $this->template->course->course_id,
        ]);

        $form->addText('task_name', 'Název termínu')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired()
        ->addRule(Form::MAX_LENGTH, 'Dĺžka názvu je maximálně 50 znaků!', 50);

        $form->addSelect('task_type', 'Typ termínu', [
		    'CV' => 'Cvičení',
		    'PR' => 'Přednáška',
		    'DU' => 'Domácí úkol',
		    'PJ' => 'Projekt',
		    'ZK' => 'Zkouška',
		])
		->setHtmlAttribute('class', 'form-control')
        ->setRequired();

        $form->addText('task_description', 'Popis')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired()
        ->addRule(Form::MAX_LENGTH, 'Dĺžka popisu je maximálně 100 znaků!', 100);

        $form->addText('task_points', 'Počet bodů')
        ->setHtmlAttribute('class', 'form-control')
        ->addRule(Form::RANGE, "Zadejte počet bodů v rozmezí 1 - 100!", [1,100]);

        $form->addText('task_date', 'Datum')
        ->setType('date')
        ->setDefaultValue((new \DateTime)->format('dd.MM.yyyy'))
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired();

        $form->addText('task_from', 'Od')
        ->setType('time')
        ->setDefaultValue((new \DateTime)->format('HH:mm'))
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired();

        $form->addText('task_to', 'Do')
        ->setType('time')
        ->setDefaultValue((new \DateTime)->format('HH.mm'))
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired();

        //itemom je task, takze chceme upravovat
        if($this->template->task->id_task)
        {
        	$form->setDefaults([
	            'task_name' => $this->template->task->task_name,
	            'task_type' => $this->template->task->task_type,
	            'task_description' => $this->template->task->task_description,
	            'task_points' =>$this->template->task->task_points,
	            'task_date' => $this->template->task->task_date,
	            'task_from' => $this->template->task->task_from,
	            'task_to' => $this->template->task->task_to,
	        ]);
        }

        $form->addSubmit('create', 'Vytvořit termín')
        ->setHtmlAttribute('class', 'btn btn-block btn-primary');
        
        $form->onSuccess[] = [$this, 'createTaskForm'];
        return $form;
	}
	
	public function createTaskForm(Nette\Application\UI\Form $form): void
    {
    	$values = $form->getValues();
    	$result = $this->database->query("INSERT INTO task (id_task, task_name, task_type, task_description, task_points, task_date, task_from, task_to, id_course) VALUES ('',?,?,?,?,?,?,?,?)", $values->task_name, $values->task_type, $values->task_description, $values->task_points, $values->task_date, $values->task_from, $values->task_to, $values->id_course);
    	if($result->getRowCount() > 0)
    	{
    		$this->template->create_task_success = 1;
    	}
    	else
    	{
    		$this->template->create_task_success = 0;
    	}
	}

	public function registerFormHandle($form)
	{
		$values = $form->getValues();
		//Check if registration exists
    	$get = $this->database->query("SELECT `id` FROM `course_has_student` WHERE `id_course` = ? AND `id_user` = ?", $values->id_course, $this->user->identity->id);

    	if($get->getRowCount() == 0)
    	{
			$data = $this->database->query("INSERT INTO course_has_student ( id, id_course, id_user, student_status) VALUES ('', ?, ?, 0)", $values->id_course, $this->user->identity->id);
			$this->template->succes_notif = true;
    	}
    	else
    	{
    		$this->template->error_notif = true;
		}
		
		if ($this->isAjax())
		{
            $this->redrawControl('content_snippet');
        }
	}

	public function unRegisterFormHandle($form)
	{
		$values = $form->getValues();
		//Check if registration exists
    	$get = $this->database->query("SELECT `id` FROM `course_has_student` WHERE `id_course` = ? AND `id_user` = ?", $values->id_course, $this->user->identity->id);

    	if($get->getRowCount() == 1)
    	{
			$this->database->table("course_has_student")->where("id_course",$values->id_course)->where("id_user",$this->user->identity->id)->delete();
			$this->template->succes_notif = true;
    	}
    	else
    	{
    		$this->template->error_notif = true;
		}
		
		if ($this->isAjax())
		{
            $this->redrawControl('content_snippet');
        }
	}

}