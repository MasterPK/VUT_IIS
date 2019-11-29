<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\Utils\FileSystem;

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

	private $task;
	private $id_course;
	private $rooms;

	private $coursetype = [
        'P' => 'Povinný',
        'V' => 'Volitelný'
    ];

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
		$this->id_course=$id;
	}

	public function renderNewtask($id_course, $id_task)
	{
		$this->id_course = $id_course;
		if($id_task != NULL)
		{
			$this->task = $this->database->query("SELECT * FROM task WHERE id_task = ? AND id_course = ?", $id_task, $id_course)->fetch();
		}
		$rooms = $this->database->query("SELECT id_room FROM room")->fetchAll();
		$category[NULL] = "Žádná";
		foreach($rooms as $room)
		{
			$category[$room->id_room] = $room->id_room;
		}
		$this->rooms = $category;
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
			$this->database->table("course")->insert([
				"id_course" => $values->id_course,
				"course_name" => $values->name,
				"course_description" => $values->description,
				"course_type" => $values->type,
				"course_price" => $values->price,
				"id_guarantor" => $this->user->identity->id,
				"course_status" => 0,
				"tags" => $values->tags
			]);
    		//$data = $this->database->query("INSERT INTO course (id_course, course_name, course_description, course_type, course_price, id_guarantor, course_status) VALUES (?, ?, ?, ?, ?, ?, 0, ?);", $values->id_course, $values->name, $values->description, $values->type, $values->price, $values->tags,  $this->user->identity->id);
			FileSystem::createDir("./Files/$values->id_course");
    		$this->template->success_insert = true;
    	}
    	catch(Nette\Database\UniqueConstraintViolationException $e)
    	{
    		$this->template->error_insert=true;
    		$this->template->error_course=$values->id_course;
		}
		catch(Nette\IOException $e)
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
        $form->addHidden('id_task');

        $form->setDefaults([
            'id_course' => $this->id_course,
            'id_task' => NULL,
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
        ->addRule(Form::RANGE, "Zadejte počet bodů v rozmezí 1 - 100!", [1,100])
        ->addRule(Form::MAX_LENGTH, "Zadejte počet bodů v rozmezí 1 - 100!", 3);

        $allrooms = $this->database->query("SELECT id_room FROM room")->fetchAll();
		$rooms[NULL] = "Žádná";
		foreach($allrooms as $room)
		{
			$rooms[$room->id_room] = $room->id_room;
		}

        $form->addSelect('id_room', 'Místnost', $rooms)
		->setHtmlAttribute('class', 'form-control');

        $form->addText('task_date', 'Datum')
        ->setType('date')
        ->setDefaultValue((new \DateTime)->format('Y-m-d'))
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired();

        $form->addText('task_from', 'Od')
        ->setHtmlAttribute('class', 'form-control')
        ->addRule(Form::RANGE, "Zadejte číslo v rozmezí 0 - 23!", [0,23])
        ->addRule(Form::MAX_LENGTH, "Zadejte číslo v rozmezí 0 - 23!", 2);

        $form->addText('task_to', 'Do')
        ->setHtmlAttribute('class', 'form-control')
        ->addRule(Form::RANGE, "Zadejte číslo v rozmezí 0 - 23!", [0,23])
        ->addRule(Form::MAX_LENGTH, "Zadejte číslo v rozmezí 0 - 23!", 2)
        ->setRequired();

        if($this->task)
        {
        	$form->setDefaults([
	            'task_name' => $this->task->task_name,
	            'task_type' => $this->task->task_type,
	            'task_description' => $this->task->task_description,
	            'task_points' => $this->task->task_points,
	            'task_date' => $this->task->task_date->format('Y-m-d'),
	            'task_from' => $this->task->task_from,
	            'task_to' => $this->task->task_to,
	            'id_room' => $this->task->id_room,
	        ]);

	        $form->setDefaults([
	            'id_task' => $this->task->id_task,
	        ]);

	         $form->addSubmit('create', 'Aktualizovat termín')
        	->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');
        }
        else
        {
        	 $form->addSubmit('create', 'Vytvořit termín')
        	->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');
        }
        $form->onSuccess[] = [$this, 'createTaskForm'];
        return $form;
	}
	
	public function createTaskForm(Nette\Application\UI\Form $form): void
    {
    	$values = $form->getValues();
    	
    	if($values->task_from == '') $values->task_from = NULL;
    	if($values->id_room == '') $values->id_room = NULL;
    	if($values->task_points == '') $values->task_points = NULL;
    	if($values->task_from >= $values->task_to)
    	{
    		if($this->isAjax())
	    	{
	    		$this->template->error = 1;
	    		$this->redrawControl('error_snippet');
	    	}
    		return;
    	}

    	//ak je id_task, tak upravujeme
    	if($values->id_task != NULL)
    	{
    		$result = $this->database->query("UPDATE task SET task_name = ?, task_type = ?, task_description = ?, task_points = ?, task_date = ?, task_from = ?, task_to = ?, id_room = ?, id_course = ? WHERE id_task = ?", $values->task_name, $values->task_type, $values->task_description, $values->task_points, $values->task_date, $values->task_from, $values->task_to, $values->id_room, $values->id_course, $values->id_task);

    		if($result->getRowCount() > 0)
	    	{
	    		$this->template->update_task_success = 1;
	    	}
	    	else
	    	{
	    		$this->template->update_task_success = 0;
	    	}

	    	if($this->isAjax())
	    	{
	    		$this->redrawControl('update_task_snippet');
	    	}
    	}
    	else
    	{
    		$result = $this->database->query("INSERT INTO task (id_task, task_name, task_type, task_description, task_points, task_date, task_from, task_to, id_room, id_course) VALUES ('',?,?,?,?,?,?,?,?,?)", $values->task_name, $values->task_type, $values->task_description, $values->task_points, $values->task_date, $values->task_from, $values->task_to, $values->id_room, $values->id_course);

    		if($result->getRowCount() > 0)
	    	{
				try
				{
					FileSystem::createDir("./Files/$values->id_course/$values->id_task");
					$this->template->create_task_success = 1;
				}
				catch(Nette\IOException $e)
				{
					$this->template->create_task_success = 0;
				}
	    		
	    	}
	    	else
	    	{
	    		$this->template->create_task_success = 0;
	    	}

	    	if($this->isAjax())
	    	{
	    		$this->redrawControl('create_task_snippet');
	    	}
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

	private $current_course;
	public function renderModifyCourse($id)
	{
		$this->current_course=$this->database->table("course")->where("id_course",$id)->fetch();
		if($this->current_course->id_guarantor!=$this->user->identity->id)
		{
			$this->redirect("Homepage:");
		}
	}

	public function createComponentDeleteCourse()
	{
		$form = new Form;
        $form->addHidden('id_course', '')
            ->setRequired()
			->setDefaultValue($this->current_course);

		$form->addCheckBox("really")
		->setRequired("Opravdu?")
		->addCondition(Form::EQUAL, true);
			
		$form->addSubmit('submit', 'Smazat?!')
			->setHtmlAttribute('class', 'btn btn-primary');
			
		$form->onSuccess[] = [$this, 'deleteCourseFormHandle'];

		return $form;
	}

	public function deleteCourseFormHandle(Form $form)
	{

		$values = $form->getValues();

		$this->database->table("course")->where("id_course",$values->id_course)->delete();
		$this->redirect("Garant:managecourses");

	}


	public function createComponentEditCourse()
    {
        $form = new Form;

        $form->addHidden('id_course', '')
			->setDefaultValue($this->current_course["id_course"]);
			
		$form->addText('id_course_show', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setDisabled()
            ->setDefaultValue($this->current_course["id_course"]);

        $form->addText('course_name', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($this->current_course["course_name"]);

        $form->addTextArea('course_description', '')
            ->setHtmlAttribute('class', 'form-control')
			->setRequired()
			->addRule(Form::MAX_LENGTH, 'Popis je příliš dlouhý', 499)
            ->setDefaultValue($this->current_course["course_description"]);

        $form->addSelect('course_type', '',$this->coursetype)
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($this->current_course["course_type"]);

        $form->addInteger('course_price', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
			->setDefaultValue($this->current_course["course_price"]);

		$form->addText('tags', 'tags',)
		->setHtmlAttribute('class', 'form-control')
        ->setDefaultValue($this->current_course["tags"]);

        $form->addSubmit('submit', 'Potvrdit změny')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

        $form->onSuccess[] = [$this, 'editCourseSubmit'];
        return $form;
    }

    public function editCourseSubmit(Form $form)
    {
        $values = $form->getValues();

        $data = $this->database->table("course")->where("id_course", $values->id_course)
            ->update([
                'course_name' => $values->course_name,
                'course_description' => $values->course_description,
                'course_type' => $values->course_type,
                'course_price' => $values->course_price
            ]);

        $this->template->success_notify = true;
        if ($this->isAjax()) {
            $this->redrawControl("notify");
        }
    }

    public function handleDeleteTask($id_task)
    {
    	$this->database->table("task")->where("id_task", $id_task)
    		->delete();

        if ($this->isAjax()) 
        {	
        	$this->template->delete_task_success = 1;
            $this->redrawControl("course_tasks_snippet");
        }
    }
}