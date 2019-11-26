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

		$this->template->courses=$this->garantModel->getCoursesOfGarant($this->user->identity->id);
		
	}
	
	public function createComponentCreateCourseForm(): Form
	{
		return $this->garantModel->createCourseF();
	}
	public function createCourseForm(Nette\Application\UI\Form $form)
	{
		return $this->garantModel->createCourse($form);
	}
}