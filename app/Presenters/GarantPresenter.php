<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;
use Tracy\Debugger;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Http\Url;

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

	/** @var \App\Model\DataGridModel @inject */
    public $dataGridModel;

	private $task;
	private $id_course;
	private $task_type;
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
		
	}

	public function createComponentCourses($name)
	{
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey('id_course');
		$grid->setDataSource($this->database->table('course'));

		$grid->addColumnText('id_course', 'Zkratka kurzu')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('course_name', 'Jméno kurzu')
		->setSortable()
		->setFilterText();
		

		$grid->addColumnText('course_type', 'Typ kurzu')
		->setReplacement([
			'P' => 'Povinný',
			'V' => 'Volitelný'
		])
		->setSortable();

		$grid->addFilterSelect('course_type', 'Typ kurzu:', ["P" => 'Povinný', "V" => 'Volitelný']);
		
		$grid->addColumnText('course_price', 'Cena kurzu')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('tags', 'Štítky')
		->setSortable()
		->setFilterText();

		$grid->addAction("select","Detail", 'Garant:showcourse')
		->setClass("btn btn-primary");

		$grid->setTranslator($this->dataGridModel->dataGridTranslator);

	
		return $grid;
	}

	public function createComponentMyCourses($name)
	{
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey('id_course');
		$grid->setDataSource($this->database->query("SELECT id_course, course_name, course_type, course_price FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE id_user = ? AND student_status = 1 AND course_status != 0",  $this->user->identity->id)->fetchAll());

		$grid->addColumnText('id_course', 'Zkratka kurzu')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('course_name', 'Jméno kurzu')
		->setSortable()
		->setFilterText();
		

		$grid->addColumnText('course_type', 'Typ kurzu')
		->setReplacement([
			'P' => 'Povinný',
			'V' => 'Volitelný'
		])
		->setSortable();

		$grid->addFilterSelect('course_type', 'Typ kurzu:', [""=>"Vše","P" => 'Povinný', "V" => 'Volitelný']);
		
		$grid->addColumnText('course_price', 'Cena kurzu')
		->setSortable()
		->setFilterText();

		$grid->addAction("select","Detail", 'Student:showcourse')
		->setClass("btn btn-primary");

		$grid->setTranslator($this->dataGridModel->dataGridTranslator);

	
		return $grid;
	}

	public function renderMycourses(): void
	{
		$this->template->courses=$this->mainModel->getCoursesOfStudent($this->user->identity->id);	
	}

	public function renderManagecourses($deleted_course, $course_delete_status): void
	{
		if($course_delete_status)
		{
			$this->template->course_delete_status = $course_delete_status;
			$this->template->deleted_course = $deleted_course;
		}
		$this->template->courses = $this->garantModel->getGarantCourses($this->user->identity->id);
	}

	
	public function rendershowCourse($id_course)
	{
		$this->garantModel->renderShowCourse($this,$id_course);
		$this->id_course=$id_course;
		$this->template->id_course = $id_course;
	}

	private $course_id;
	private $task_id;
	public function rendernewFile($course_id, $task_id)
	{
		$this->course_id = $course_id;
		$this->task_id = $task_id;
	}

	public function renderNewtask($id_course, $task_type, $id_task)
	{
		$this->id_course = $id_course;
		$this->task_type = $task_type;
		$this->template->task_type = $task_type;

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

	public function renderManagelectors($id_course)
	{
		//vyber lektorov, ktori este nie su lektormi daneho kurzu
		$this->template->select_lectors = $this->database->query("SELECT id_user, email, first_name, surname FROM user WHERE rank >= 2 AND id_user NOT IN
										(SELECT id_user FROM user NATURAL LEFT JOIN course_has_lecturer WHERE rank >= 2 AND id_course = ?)", $id_course)->fetchAll();
		$this->template->current_lectors = $this->database->query("SELECT id_user, email, first_name, surname FROM user NATURAL JOIN course_has_lecturer WHERE id_course = ?", $id_course)->fetchAll();
		$this->template->id_course = $id_course;
		$this->id_course = $id_course;
	}

	public function renderCourse($id)
	{
		if($id)
        {
			$this->garantModel->getCurrentCourse($this, $id);
		}
	}

	private $id_task;
	public function renderShowtask($id_task)
	{
		$this->id_task = $id_task;
	}
	
	public function createComponentCreateCourseForm(): Form
	{
		return $this->garantModel->createCourseF($this);
	}

	public function createCourseForm(Nette\Application\UI\Form $form): void
    {
    	$values = $form->getValues();

    	$check = $this->database->query("SELECT id_course FROM course WHERE id_course = ?", $values->id_course);
		if($check->getRowCount() == 1)
		{
			if(($values->old_id_course != NULL && $values->old_id_course != $values->id_course) || $values->old_id_course == NULL)
    		{
				$this->template->error_course_exists=true;
				if($this->isAjax())
				{
					$this->redrawControl('course_snippet');
				}
				return;
			}
		}

    	if($values->old_id_course != NULL)
    	{			
			try
	    	{	    		
    			$data = $this->database->table("course")->where("id_course", $values->old_id_course)
	            ->update([
	            	"id_course" => $values->id_course,
	                'course_name' => $values->course_name,
	                'course_description' => $values->course_description,
	                'course_type' => $values->course_type,
	                'course_price' => $values->course_price,
					"tags" => $values->tags
	            ]);

	            if($data == 1)
	            {
	            	//ak nastala zmena, nastav status na ziadost o schvalenie
	            	$data = $this->database->table("course")->where("id_course", $values->id_course)
		            ->update([
		            	"course_status" => 0
		            ]);
	            	
	            	FileSystem::rename("Files/$values->old_id_course", "Files/$values->id_course");
    				$this->template->success_update = true;

    				$values->old_id_course = $values->id_course;
    				$form->setValues($values);
	            }
	            else
	            {
	            	$this->template->no_change = true;
	            }
				
	    	}
	    	catch(Nette\Database\UniqueConstraintViolationException $e)
	    	{
	    		$this->template->error_update=true;
			}
			catch(Nette\IOException $e)
	    	{
	    		$this->template->error_update=true;
			}
			
    	}
    	else
    	{
			try
	    	{
				$this->database->table("course")->insert([
					"id_course" => $values->id_course,
	                'course_name' => $values->course_name,
	                'course_description' => $values->course_description,
	                'course_type' => $values->course_type,
	                'course_price' => $values->course_price,
					"id_guarantor" => $this->user->identity->id,
					"course_status" => 0,
					"tags" => $values->tags
				]);
	   
				FileSystem::createDir("Files/$values->id_course");
				
	    		$this->template->success_insert = true;
	    		
	    		$values->old_id_course = $values->id_course;
    			$form->setValues($values);
	    	}
	    	catch(Nette\Database\UniqueConstraintViolationException $e)
	    	{
	    		$this->template->error_insert=true;
			}
			catch(Nette\IOException $e)
	    	{
	    		$this->template->error_insert=true;
			}
    	}

		if($this->isAjax())
		{
			$this->redrawControl('course_snippet');
		}
	}

	public function createComponentCoursesMng($name)
	{
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey('id_course');
		$grid->setDataSource($this->garantModel->getGarantCourses($this->user->identity->id));

		$grid->addColumnText('id_course', 'Zkratka kurzu')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('course_name', 'Jméno kurzu')
		->setSortable()
		->setFilterText();
		

		$grid->addColumnText('course_type', 'Typ kurzu')
		->setReplacement([
			'P' => 'Povinný',
			'V' => 'Volitelný'
		])
		->setSortable();

		$grid->addFilterSelect('course_type', 'Typ kurzu:', [""=>"Vše", "P" => 'Povinný', "V" => 'Volitelný']);
		

		$grid->addColumnText('course_status', 'Stav kurzu')
		->setSortable()
            ->setReplacement([
                '0' => 'Čeká na schválení',
				'1' => 'Schválen',
				'2' => 'Otevřené registrace',
				'3' => 'Uzavřené registrace',
				'4' => 'Zamítnut'
            ])
            ->setFilterSelect([
				"" => "Vše",
                '0' => 'Čeká na schválení',
				'1' => 'Schválen',
				'2' => 'Otevřené registrace',
				'3' => 'Uzavřené registrace',
				'4' => 'Zamítnut'
            ]);

        $grid->addToolbarButton('course', '')
            ->setIcon('plus')
            ->setTitle('Nový kurz')
            ->setClass('btn btn-xs btn-primary');

		$grid->addAction("select", "", 'Garant:showcourse')
		->setIcon('info')
		->setClass("btn btn-sm btn-info");

		$grid->addAction("select2", "", 'editCourse!')
		->setIcon('fas edit')
		->setClass("btn btn-sm btn-secondary");

		$grid->setTranslator($this->dataGridModel->dataGridTranslator);
	
		return $grid;
	}

	public function handleEditCourse($id_course)
	{
		$this->redirect("Garant:course", $id_course);
	}

	public function createComponentLectorsAdd($name)
	{
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey('id_user');
		$grid->setDataSource($this->database->query("SELECT id_user, email, first_name, surname FROM user WHERE rank >= 2 AND id_user NOT IN (SELECT id_user FROM user NATURAL LEFT JOIN course_has_lecturer WHERE rank >= 2 AND id_course = ?)", $this->id_course)->fetchAll());

		$grid->addColumnText('email', 'Email')
		->setSortable()
		->setFilterText();
		
		$grid->addColumnText('first_name', 'Jméno')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('surname', 'Přijmení')
		->setSortable()
		->setFilterText();
		
		$grid->addAction("select", "", 'add!')
		->setIcon('fas plus')
		->setClass("btn btn-sm btn-primary");

		$grid->setTranslator($this->dataGridModel->dataGridTranslator);
	
		return $grid;
	}

	public function createComponentLectorsRemove($name)
	{
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey('id_user');
		$grid->setDataSource($this->database->query("SELECT DISTINCT id_user, email, first_name, surname FROM user NATURAL JOIN course_has_lecturer WHERE id_course = ?", $this->id_course)->fetchAll());

		$grid->addColumnText('email', 'Email')
		->setSortable()
		->setFilterText();
		
		$grid->addColumnText('first_name', 'Jméno')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('surname', 'Přijmení')
		->setSortable()
		->setFilterText();
		
		$grid->addAction("select", "", 'remove!')
		->setIcon('fas minus')
		->setClass("btn btn-sm btn-primary");

		$grid->setTranslator($this->dataGridModel->dataGridTranslator);
	
		return $grid;
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


	public function createComponentCreateTaskForm(): Nette\Application\UI\Form
    {
        $form = new Nette\Application\UI\Form;

        $form->addHidden('id_course');
        $form->addHidden('id_task');
        $form->addHidden('task_type');

        $form->setDefaults([
            'id_course' => $this->id_course,
            'id_task' => NULL,
            'task_type' => $this->task_type,
        ]);

        $form->addText('task_name', 'Název termínu')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired("Tohle pole je povinné")
        ->addRule(Form::MAX_LENGTH, 'Dĺžka názvu je maximálně 50 znaků!', 50);

        $form->addText('task_description', 'Popis')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired("Tohle pole je povinné")
        ->addRule(Form::MAX_LENGTH, 'Dĺžka popisu je maximálně 100 znaků!', 100);

        $form->addText('task_points', 'Počet bodů')
        ->setHtmlAttribute('class', 'form-control')
        ->setDefaultValue(0)
        ->addRule(Form::RANGE, "Zadejte počet bodů v rozmezí 0 - 100!", [0,100])
        ->addRule(Form::MAX_LENGTH, "Zadejte počet bodů v rozmezí 0 - 100!", 3);

        $allrooms = $this->database->query("SELECT id_room FROM room")->fetchAll();
		$rooms[NULL] = "Žádná";
		foreach($allrooms as $room)
		{
			$rooms[$room->id_room] = $room->id_room;
		}

        $form->addSelect('id_room', 'Místnost', $rooms)
		->setHtmlAttribute('class', 'form-control');

		switch($this->task_type)
		{
			case 'DU':
			case 'PJ':
				$form->addText('task_date', 'Datum odevzdání')
		        ->setType('date')
		        ->setDefaultValue((new \DateTime)->format('Y-m-d'))
		        ->setHtmlAttribute('class', 'form-control')
		        ->setRequired("Tohle pole je povinné");

		        $form->addText('task_to', 'Čas odevzdání')
		        ->setHtmlAttribute('class', 'form-control')
		        ->addRule(Form::RANGE, "Zadejte číslo v rozmezí 0 - 23!", [0,23])
		        ->addRule(Form::MAX_LENGTH, "Zadejte číslo v rozmezí 0 - 23!", 2);

				break;
			default:
				 $form->addText('task_date', 'Datum')
		        ->setType('date')
		        ->setDefaultValue((new \DateTime)->format('Y-m-d'))
		        ->setHtmlAttribute('class', 'form-control')
		        ->setRequired("Tohle pole je povinné");

		        $form->addText('task_from', 'Od')
		        ->setHtmlAttribute('class', 'form-control')
		        ->addRule(Form::RANGE, "Zadejte číslo v rozmezí 0 - 23!", [0,23])
		        ->addRule(Form::MAX_LENGTH, "Zadejte číslo v rozmezí 0 - 23!", 2);

		        $form->addText('task_to', 'Do')
		        ->setHtmlAttribute('class', 'form-control')
		        ->addRule(Form::RANGE, "Zadejte číslo v rozmezí 0 - 23!", [0,23])
		        ->addRule(Form::MAX_LENGTH, "Zadejte číslo v rozmezí 0 - 23!", 2)
		        ->setRequired("Tohle pole je povinné");
		        
				break;
		}
       

        if($this->task)
        {
        	$form->setDefaults([
        		'id_task' => $this->task->id_task,
	            'task_name' => $this->task->task_name,
	            'task_type' => $this->task->task_type,
	            'task_description' => $this->task->task_description,
	            'task_points' => $this->task->task_points,
	            'task_date' => $this->task->task_date->format('Y-m-d'),
	            'task_from' => $this->task->task_from,
	            'task_to' => $this->task->task_to,
	            'id_room' => $this->task->id_room,
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
    	if($values->task_from >= $values->task_to)
    	{
    		if($this->isAjax())
	    	{
	    		$this->template->error = 1;
	    		$this->redrawControl('error_snippet');
	    	}
    		return;
    	}


    	//vezmi vsetky terminy daneho kurzu a spocitaj celkove body
    	$points = $this->database->query("SELECT SUM(task_points) AS total_points FROM task NATURAL JOIN course WHERE id_course = ? AND id_task != ?", $values->id_course, $values->id_task)->fetch();

    	if(($points->total_points + $values->task_points) > 100)
    	{
    		if($this->isAjax())
	    	{
	    		$this->template->error_points = 1;
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
			$task_id = $this->database->getInsertId('task');
	
    		if($result->getRowCount() > 0)
	    	{
	    		$students = $this->database->query("SELECT id_user FROM course_has_student WHERE id_course = ? AND student_status > 0", $values->id_course)->fetchAll();
	    		foreach($students as $student)
	    		{
	    			$result = $this->database->query("INSERT INTO student_has_task (id_user, id_task) VALUES (?,?)", $student->id_user, $task_id);
	    		
	    			if($result->getRowCount() == 0)
	    			{
	    				$this->template->add_students_success = 0;
	    				return;
	    			}
	    		}


				try
				{
					FileSystem::createDir("Files/$values->id_course/$task_id");
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

	public function handleDeleteCourse($id_course)
	{
		$result = $this->database->table("course")->where("id_course",$id_course)->delete();

		if($result > 0)
		{
			FileSystem::delete("Files/$id_course");
			$this->redirect("Garant:managecourses", $id_course, 1);
		}
		else
		{
			$this->redirect("Garant:managecourses", $id_course, 0);
		}		
	}

    public function handleDeleteTask($id_task,$id_course)
    {
    	
			
		
    	$task = $this->database->table("task")->where("id_task", $id_task)
			->fetch();

    	if($task)
    	{	
    		$result = $this->database->table("task")->where("id_task", $id_task)
    			->delete();

    		$this->template->task_name = $task->task_name;
    		if ($result > 0) 
	        {	
				FileSystem::delete("Files/$id_course/$id_task");
	        	$this->template->delete_task_success = 1;
	        }
	        else
	        {
	        	$this->template->delete_task_success = 0;
	        }
    	}
    	else
    	{
    		$this->template->delete_task_success = 0;
    	}

    	if($this->isAjax())
	    {
    		//$this->redrawControl("delete_task_snippet");
			$this->redrawControl("course_tasks_snippet");
		}
	}
	
	public function handleDeleteFile($file)
	{
		/*try {*/
		Debugger::barDump($file, "souborDelete");
		FileSystem::delete("$file");
		$this->template->success_notif = true;
		/*} catch (Nette\IOException $e) {
			$this->template->error_notif = true;
		}*/

		if ($this->isAjax()) {

			$this->redrawControl("content_snippet");
		}
	}

	public function createComponentNewFileToCourseForm()
	{
		$form = new Form;

		$form->addHidden("course_id")
			->setDefaultValue($this->course_id);

		$form->addHidden("task_id")
			->setDefaultValue($this->task_id);

		$form->addUpload('file', '')
			->setRequired(true)
			->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru je 5 MB.', 5242880 /* v bytech */);

		$form->addSubmit('submit', 'Odeslat')
			->setHtmlAttribute('class', 'btn btn-block btn-primary');

		$form->onSuccess[] = [$this, 'newFileToCourseFormSubmit'];

		return $form;
	}

	public function newFileToCourseFormSubmit(Form $form)
	{
		$values = $form->getValues();
		$path = "Files/$values->course_id/$values->task_id/" . $values->file->getName();
		$values->file->move($path);
		$this->redirect('Garant:showcourse',$values->course_id);
	}

	public function handleRemove($id_user, $name, $id_course): void
    {
    	
		$result = $this->database->query("DELETE FROM course_has_lecturer WHERE id_user = ? AND id_course = ?", $id_user, $id_course);

		$this->template->name = $name;
		if($result->getRowCount() > 0)
		{
			$this->template->remove_lector_success = 1;
		}
		else
		{
			$this->template->remove_lector_success = 0;
		}
		
		if ($this->isAjax())
		{
            $this->redrawControl("manage_snippet");
        }
	}

	public function handleAdd($id_user, $name, $id_course): void
    {
    	
		$result = $this->database->query("INSERT INTO course_has_lecturer (id_user, id_course) VALUES (?,?)", $id_user, $id_course);

		$this->template->name = $name;
		if($result->getRowCount() > 0)
		{
			$this->template->add_lector_success = 1;
		}
		else
		{
			$this->template->add_lector_success = 0;
		}		
		
		if ($this->isAjax())
		{
            $this->redrawControl("manage_snippet");
        }	
	}

	public function createComponentTaskStudentsGrid($name)
    {
        \Tracy\Debugger::barDump($this->template);
        
        $id_task;
        if($this->id_task == NULL)
        {
            $httpRequest = $this->getHttpRequest();
            $id_task = $httpRequest->getQuery('id_task');
        }
        else
        {
        	$id_task = $this->id_task;
        }

        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('user.id_user');
        //$grid->setDataSource($this->database->query("SELECT id_user, email, first_name, surname, points FROM user NATURAL JOIN student_has_task"));
        $grid->setDataSource($this->database->table("student_has_task")->where("student_has_task.id_task = ?", $id_task)->select("user.id_user,user.email,user.first_name,user.surname,student_has_task.points"));

        $grid->addColumnText('email', 'Email studenta')
        ->setSortable()
        ->setFilterText();

        $grid->addColumnText('first_name', 'Jméno studenta')
        ->setSortable()
        ->setFilterText();
        
        $grid->addColumnText('surname', 'Přijmení studenta')
        ->setSortable()
        ->setFilterText();
        
        $grid->addColumnText('points', 'Body')
        ->setSortable()
        ->setFilterText();

        $grid->addGroupTextAction('Nastavit body')
            ->onSelect[] = function ($students, $value): void {
                $httpRequest = $this->getHttpRequest();
                $id_task = $httpRequest->getQuery('id_task');
                $maxpoints = $this->database->query("SELECT task_points FROM task WHERE id_task = ?", $id_task)->fetch();
                if($maxpoints->task_points >= $value)
                {
                    foreach($students as $student)
                    {
                        $this->database->query("UPDATE student_has_task SET points = ? WHERE id_user = ? AND id_task = ?", $value, $student, $id_task);
					}
					
					$this->template->error = false;
					$this->redrawControl('content_snippet');
					$this->redrawControl('points_snippet');
                }
                else
                {
                    $this->template->error = true;
                    $this->redrawControl('points_snippet');
                }
            };

        $grid->addInlineEdit()
            ->onControlAdd[] = function (Nette\Forms\Container $container): void {
            $httpRequest = $this->getHttpRequest();
            $id_task = $httpRequest->getQuery('id_task');
            $maxpoints = $this->database->query("SELECT task_points FROM task WHERE id_task = ?", $id_task)->fetch();
            $container->addText('points', '')
                    ->addRule(Form::RANGE, "Zadejte počet bodů v rozmezí 0 - ".$maxpoints->task_points, [0,$maxpoints->task_points]);
        };

        $grid->getInlineEdit()->onSetDefaults[] = function (Nette\Forms\Container $container, $item): void {

            $container->setDefaults([
                'points' => $item->points
            ]);
        };

        $grid->getInlineEdit()->onSubmit[] = function ($id, Nette\Utils\ArrayHash $values): void {
            $httpRequest = $this->getHttpRequest();
            $id_task = $httpRequest->getQuery('id_task');
            
			$this->database->query("UPDATE student_has_task SET points = ? WHERE id_user = ? AND id_task = ?", $values->points, $id, $id_task);
			
			$this->template->error = false;

			if ($this->isAjax()) 
			{
				$this->redrawControl('points_snippet');
			} 
			else 
			{
				$this->redirect('this');
			}
        };

        $grid->setTranslator($this->dataGridModel->dataGridTranslator);

        return $grid;
    }

    
}