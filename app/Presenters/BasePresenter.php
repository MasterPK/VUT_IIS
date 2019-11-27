<?php

declare(strict_types=1);

namespace App\Presenters;
use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
	/** @var \App\Model\StartUp @inject */
	public $startup;
	
	/** @var \App\Model\MainModel @inject */
	public $mainModel;

	/** @var \App\Model\VisitorModel @inject */
	public $visitorModel;

	/** @var \App\Model\StudentModel @inject */
	public $studentModel;

	/** @var \App\Model\LectorModel @inject */
	public $lectorModel;

	/** @var \App\Model\GarantModel @inject */
	public $garantModel;

	/** @var \App\Model\ChiefModel @inject */
	public $chiefModel;

	/** @var \App\Model\AdminModel @inject */
	public $adminModel;

	public $database;
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	private $current_course_id;


	public function startUp()
	{
		parent::startup();

		$this->startup->mainStartUp($this);
		
	}


	public function renderDefault(): void
	{ 

	}

	public function renderCourses($search, $filter): void
	{
		if($search)
		{
			$this->template->courses=$this->mainModel->getAllCoursesByFilter($filter, $search);
		}
		else
		{
			//zobraz vsetky schvalene kurzy
			$this->template->courses=$this->mainModel->getAllApprovedCourses();
		}
	}

	public function renderShowcourse($id): void
	{
		$this->visitorModel->renderShowcourse($this,$id);
		
	}

	public function renderMycourses(): void
	{
		$this->template->courses=$this->mainModel->getCoursesOfStudent($this->user->identity->id);	
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
}