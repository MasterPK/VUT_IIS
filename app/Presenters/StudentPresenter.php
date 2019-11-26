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
		$this->studentModel->renderShowcourse($this,$id);
	}

	public function renderMycourses(): void
	{
		$this->template->courses=$this->studentModel->getCoursesOfStudent($this->user->identity->id);	
	}

	public function renderCourses(): void
	{
		$this->template->courses=$this->studentModel->visitorModel->getAllCourses();	
	}

	public function createComponentRegisterForm()
	{
		return $this->studentModel->createComponentRegisterForm($this);
	}

	public function registerFormHandle($form)
	{
		$this->studentModel->registerFormHandle($this,$form);
	}

	protected function createComponentSearchCourseForm(): Nette\Application\UI\Form
    {
        $form = new Nette\Application\UI\Form;

        $form->addSelect('filter', 'Filter', [
		    'course_name' => 'Název',
		    'id_course' => 'Zkratka',
		    'course_type' => 'Typ',
		    'course_price' => 'Cena',
		]);

        $form->addText('search', 'Hledat:')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired();

        $form->addSubmit('send', 'Hledat')
        ->setHtmlAttribute('class', 'btn btn-block btn-primary');
        
        $form->onSuccess[] = [$this, 'searchCourseForm'];
        return $form;
	}
	
	public function searchCourseForm(Nette\Application\UI\Form $form): void
    {
    	$values = $form->getValues();
    	$this->redirect("Student:courses", $values->search, $values->filter);
	}
}
