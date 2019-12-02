<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\Utils\FileSystem;
use Ublaboo\DataGrid\DataGrid;

final class LectorPresenter extends Nette\Application\UI\Presenter
{
	/** @var \App\Model\StartUp @inject */
	public $startup;

	/** @var Nette\Database\Context @inject */
	public $database;

	/** @var \App\Model\LectorModel @inject */
	public $lectorModel;

	/** @var \App\Model\StudentModel @inject */
	public $studentModel;

	/** @var \App\Model\MainModel @inject */
	public $mainModel;

	/** @var \App\Model\DataGridModel @inject */
    public $dataGridModel;

	public function startUp()
	{
		parent::startup();


		$this->startup->mainStartUp($this);
		if (!$this->startup->roleCheck($this, 2)) {
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
	public function renderMycourses(): void
	{
		
	}

	public function renderCourses(): void
	{
		
	}

	public function renderManagecourses()
	{
		
	}

	private $id_task;
	public function renderShowtask($id_task)
	{
		$this->id_task = $id_task;
	}

	public function renderShowcourse($id)
	{
		$this->lectorModel->renderShowCourse($this, $id);
	}

	private $task;
	private $task_type;
	private $id_course;
	public function renderNewtask($id_course, $task_type, $id_task)
	{
		if($id_task != NULL)
		{
			$check = $this->database->query("SELECT id_course FROM user NATURAL JOIN course_has_lecturer NATURAL JOIN course WHERE id_user = ? AND id_course = ?",  $this->user->identity->id, $id_course)->fetchAll();

			if($check)
			{
				$this->task = $this->database->query("SELECT * FROM task WHERE id_task = ? AND id_course = ?", $id_task, $id_course)->fetch();
			}
		}
		
		if($id_task == NULL || $this->task == NULL)
		{
			$this->template->error = 1;
			$this->redrawControl("error_snippet");
			$this->redirect('showcourse',$id_course);
		}

		$this->id_course = $id_course;
		$this->task_type = $task_type;
		$this->template->task_type = $task_type;

		$rooms = $this->database->query("SELECT id_room FROM room")->fetchAll();
		$category[NULL] = "Žádná";
		foreach($rooms as $room)
		{
			$category[$room->id_room] = $room->id_room;
		}
		$this->rooms = $category;
	}

	private $course_id;
	private $task_id;
	private $rooms;
	public function rendernewFile($course_id, $task_id)
	{
		$this->course_id = $course_id;
		$this->task_id = $task_id;
	}

	public function renderFiles($id_course, $id_task)
	{
		$this->studentModel->renderFiles($this, $id_course, $id_task);
		$this->course_id = $id_course;
		$this->task_id = $id_task;
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

	public function createComponentCoursesMng($name)
	{
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey('id_course');
		$grid->setDataSource($this->database->query("SELECT id_course, course_name, course_type, course_status FROM user NATURAL JOIN course_has_lecturer NATURAL JOIN course WHERE id_user = ? AND course_status != 0",  $this->user->identity->id)->fetchAll());

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

		$grid->addAction("select", "", 'showCourse!')
		->setIcon('info')
		->setClass("btn btn-sm btn-info center-block");

		$grid->setTranslator($this->dataGridModel->dataGridTranslator);

	
		return $grid;
	}

	public function handleShowCourse($id_course)
	{
		$this->redirect('Lector:showcourse', $id_course);
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
		
		$grid->addColumnText('course_price', 'Cena kurzu (Kč)')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('tags', 'Štítky')
		->setSortable()
		->setFilterText();

		$grid->addAction("select","", 'Student:showcourse')
		->setIcon("info")
		->setClass("btn btn-info");

		$grid->setTranslator($this->dataGridModel->dataGridTranslator);

	
		return $grid;
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
			->setRequired("Tohle pole je povinné.");

		$form->addText('task_description', 'Popis')
			->setHtmlAttribute('class', 'form-control')
			->setRequired()
			->addRule(Form::MAX_LENGTH, 'Dĺžka popisu je maximálně 100 znaků!', 100);

		$form->addText('task_points', 'Počet bodů')
			->setHtmlAttribute('class', 'form-control')
			->addRule(Form::RANGE, "Zadejte počet bodů v rozmezí 1 - 100!", [1, 100])
			->addRule(Form::MAX_LENGTH, "Zadejte počet bodů v rozmezí 1 - 100!", 3);

		$allrooms = $this->database->query("SELECT id_room FROM room")->fetchAll();
		$rooms[NULL] = "Žádná";
		foreach ($allrooms as $room) {
			$rooms[$room->id_room] = $room->id_room;
		}

		$form->addSelect('id_room', 'Místnost', $rooms)
			->setHtmlAttribute('class', 'form-control');

		$form->addText('task_date', 'Datum')
			->setType('date')
			->setDefaultValue((new \DateTime)->format('Y-m-d'))
			->setHtmlAttribute('class', 'form-control')
			->setRequired("Tohle pole je povinné.");

		$form->addText('task_from', 'Od')
			->setHtmlAttribute('class', 'form-control')
			->addRule(Form::RANGE, "Zadejte číslo v rozmezí 0 - 23!", [0, 23])
			->addRule(Form::MAX_LENGTH, "Zadejte číslo v rozmezí 0 - 23!", 2);

		$form->addText('task_to', 'Do')
			->setHtmlAttribute('class', 'form-control')
			->addRule(Form::RANGE, "Zadejte číslo v rozmezí 0 - 23!", [0, 23])
			->addRule(Form::MAX_LENGTH, "Zadejte číslo v rozmezí 0 - 23!", 2)
			->setRequired("Tohle pole je povinné.");

		if ($this->task) {
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
		} else {
			$form->addSubmit('create', 'Vytvořit termín')
				->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');
		}
		$form->onSuccess[] = [$this, 'createTaskForm'];
		return $form;
	}

	public function createTaskForm(Nette\Application\UI\Form $form): void
	{
		$values = $form->getValues();

		if ($values->task_from == '') $values->task_from = NULL;
		if ($values->id_room == '') $values->id_room = NULL;
		if ($values->task_points == '') $values->task_points = NULL;
		if ($values->task_from >= $values->task_to) {
			if ($this->isAjax()) {
				$this->template->error = 1;
				$this->redrawControl('error_snippet');
			}
			return;
		}

		//ak je id_task, tak upravujeme
		if ($values->id_task != NULL) {
			$result = $this->database->query("UPDATE task SET task_name = ?, task_type = ?, task_description = ?, task_points = ?, task_date = ?, task_from = ?, task_to = ?, id_room = ?, id_course = ? WHERE id_task = ?", $values->task_name, $values->task_type, $values->task_description, $values->task_points, $values->task_date, $values->task_from, $values->task_to, $values->id_room, $values->id_course, $values->id_task);

			if ($result->getRowCount() > 0) {
				$this->template->update_task_success = 1;
			} else {
				$this->template->update_task_success = 0;
			}

			if ($this->isAjax()) {
				$this->redrawControl('update_task_snippet');
			}
		} else {
			$result = $this->database->query("INSERT INTO task (id_task, task_name, task_type, task_description, task_points, task_date, task_from, task_to, id_room, id_course) VALUES ('',?,?,?,?,?,?,?,?,?)", $values->task_name, $values->task_type, $values->task_description, $values->task_points, $values->task_date, $values->task_from, $values->task_to, $values->id_room, $values->id_course);
			$task_id = $this->database->getInsertId('task');
			Debugger::barDump($result);
			Debugger::barDump($task_id, "id");
			if ($result->getRowCount() > 0) {
				try {
					FileSystem::createDir("Files/$values->id_course/$task_id");
					$this->template->create_task_success = 1;
				} catch (Nette\IOException $e) {
					$this->template->create_task_success = 0;
				}
			} else {
				$this->template->create_task_success = 0;
			}

			if ($this->isAjax()) {
				$this->redrawControl('create_task_snippet');
			}
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
		$this->redirect('Lector:showcourse',$values->course_id);
	}

	/*public function renderLector(): void
	{
		$courses = array();
		switch($this->template->rank)
		{
			case 5:
			case 4:
			case 3:
				$data = $this->database->query("SELECT id_course, course_name, course_type, course_status FROM course WHERE id_guarantor = ?",  $this->user->identity->id);
				if($data->getRowCount() > 0)
				{
					foreach($data as $course)
					{
						
						array_push($courses, $course);
					}
				}
			case 2:
				$data = $this->database->query("SELECT id_course, course_name, course_type, course_status FROM user NATURAL JOIN course_has_lecturer NATURAL JOIN course WHERE id_user = ? AND course_status != 0",  $this->user->identity->id);
				if($data->getRowCount() > 0)
				{
					foreach($data as $course)
					{
						array_push($courses, $course);
					}
				}
				break;
			default:
				break;
		}
		
		if(count($courses) > 0)
		{
			$this->template->courses=$courses;
		}
	}*/

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
                }
                else
                {
                    $this->template->error_set = true;
                    \Tracy\Debugger::barDump($this->template);
                    $this->redrawControl('error_snippet');
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
                
        };

        $grid->setTranslator($this->dataGridModel->dataGridTranslator);

        return $grid;
    }

    public function createComponentFiles($name)
	{
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey('name');
		Debugger::barDump($this->template->files);
		$grid->setDataSource($this->template->files);

		$replacement = [];
        foreach($this->template->files as $file)
        {
            $replacement[$file['name']] = explode(".",basename($file['name']))[0];
        }

		$grid->addColumnText('name', 'Name', '')
		->setReplacement($replacement)
		->setSortable()
		->setFilterText();

		$grid->addColumnText('extension', 'Přípona')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('size', 'Velikost (B)')
		->setSortable()
		->setFilterText();

		$grid->addAction("select", "", "download!")
			->setIcon('fas download')
			->setClass("btn btn-sm btn-primary");

		$grid->addToolbarButton('Lector:newFile $this->course_id, $this->task_id', '')
            ->setIcon('plus')
            ->setTitle('Přidat soubor')
			->setClass('btn btn-xs btn-primary');

		$grid->setTranslator($this->dataGridModel->dataGridTranslator);


		return $grid;
	}
}
