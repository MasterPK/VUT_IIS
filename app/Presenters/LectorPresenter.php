<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\Utils\FileSystem;

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
		$this->template->courses = $this->lectorModel->getCoursesOfLector($this->user->identity->id);
	}

	public function renderCourses(): void
	{
		$this->template->courses = $this->mainModel->getAllCourses();
	}

	public function renderManagecourses()
	{
		$this->template->courses = $this->lectorModel->getLectorCourses($this->user->identity->id);
	}

	public function renderShowcourse($id)
	{
		$this->lectorModel->renderShowCourse($this, $id);
		Debugger::barDump($this->presenter->template->files, "soubory");
	}
	private $course_id;
	private $task_id;
	public function rendernewFile($course_id, $task_id)
	{
		$this->course_id=$course_id;
		$this->task_id=$task_id;
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
			Debugger::barDump($file,"souborDelete");
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
		$this->redirect('Lector:showcourse $');

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
}
