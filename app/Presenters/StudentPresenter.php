<?php

declare(strict_types=1);


namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;
use Tracy\Debugger;
use Nette\Utils\DateTime;

class StudentPresenter extends Nette\Application\UI\Presenter
{
	/** @var \App\Model\StartUp @inject */
    public $startup;

	/** @var \App\Model\StudentModel @inject */
	public $studentModel;

	/** @var \App\Model\MainModel @inject */
	public $mainModel;
	
	/** @var Nette\Database\Context @inject */
	public $database;

	/** @var \App\Model\DataGridModel @inject */
    public $dataGridModel;

	public function startUp()
	{
		parent::startup();
		
		
		$this->startup->mainStartUp($this);
		if(!$this->startup->roleCheck($this,1))
		{
			$this->redirect("Homepage:default");
		}
	}

	public function renderCourses(): void
	{
	}

	public function renderShowcourse($id_course): void
	{
		$this->studentModel->renderShowcourse($this,$id_course);
		
	}

	public function renderMycourses(): void
	{
		
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

		$grid->addFilterSelect('course_type', 'Typ kurzu:', ["P" => 'Povinný', "V" => 'Volitelný']);
		
		$grid->addColumnText('course_price', 'Cena kurzu')
		->setSortable()
		->setFilterText();

		$grid->addAction("select","Detail", 'Student:showcourse')
		->setClass("btn btn-primary");

		$grid->setTranslator($this->dataGridModel->dataGridTranslator);

	
		return $grid;
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

		$grid->addFilterSelect('course_type', 'Typ kurzu:', [""=>"Vše","P" => 'Povinný', "V" => 'Volitelný']);
		
		$grid->addColumnText('course_price', 'Cena kurzu')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('tags', 'Štítky')
		->setSortable()
		->setFilterText();

		$grid->addAction("select","Detail", 'Student:showcourse')
		->setClass("btn btn-primary");

		$grid->setTranslator($this->dataGridModel->dataGridTranslator);

	
		return $grid;
	}
	
	public function renderMyCourseDetails($id_course): void
	{
		$data = $this->database->query("SELECT * FROM course_has_task NATURAL JOIN task WHERE id_course = ?",  $id_course)->fetchAll();
		$this->template->courses = $data;
		
		$body = 0;
		foreach ($data as $tmp)
		{
			$body += $tmp->task_points;
		}
		$this->template->body = $body;
	}

	public function createComponentRegisterForm()
	{
		return $this->studentModel->createComponentRegisterForm($this);
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

	public function createComponentUnRegisterForm()
	{
		return $this->studentModel->createComponentUnRegisterForm($this);
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

	public function renderTimetable()
	{
		//Get all tasks in student courses
		$data=$this->database->query("SELECT task.* FROM course NATURAL JOIN course_has_student NATURAL JOIN task WHERE id_user=?;",$this->user->identity->id)->fetchAll();

		if(!$data)
		{
			return;
		}
		$dayTasksCount=array();
		foreach ($data as $value) {
			setlocale(LC_ALL,"cs-CZ");
			$day = date ('l',$value->task_date->getTimestamp());
			Debugger::barDump($day,"den");
			$day = strftime ('%A',$value->task_date->getTimestamp());
			Debugger::barDump($day,"den1");
		}
		

	}
	
}
