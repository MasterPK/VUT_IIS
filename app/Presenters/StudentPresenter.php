<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class StudentPresenter extends Nette\Application\UI\Presenter 
{
	/** @var \App\Model\StartUp @inject */
	public $startup;
	
	/** @var \App\Model\StudentModel @inject */
    public $studentModel;

	/** @var Nette\Database\Context @inject */
	public $database;


	public function startUp()
	{
		parent::startup();

		$this->startup->mainStartUp($this);
		if(!$this->startup->roleCheck($this,1))
		{
			$this->redirect("Homepage:default");
		}

	}


	public function renderShowcourse($id): void
	{ 
		$this->template->course= $this->visitorModel->getCourseDetails($id);
	}

	public function renderMycourses(): void
	{
		$this->template->courses=$this->studentModel->getCoursesOfStudent($this->user->identity->id);	
	}
}
