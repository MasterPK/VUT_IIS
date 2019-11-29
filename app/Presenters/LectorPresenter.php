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
		try {
			Debugger::barDump($file,"soubor");
			FileSystem::delete("/$file");
			$this->template->success_notif = true;
		} catch (Nette\IOException $e) {
			$this->template->error_notif = true;
		}
		
		if ($this->isAjax()) {
			
			$this->redrawControl("content_snippet");
		}
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
