<?php

declare(strict_types=1);

namespace App\Presenters;
use Nette;
use Nette\Application\UI\Form;


class ChiefPresenter extends BasePresenter
{
	public function startUp()
	{
		parent::startup();

		
		$this->startup->mainStartUp($this);
		if(!$this->startup->roleCheck($this,4))
		{
			$this->redirect("Homepage:default");
		}
	}

	public function renderLector()
	{
		$lectorCourses = $this->lectorModel->getLectorCourses($this->user->identity->id);
		$garantCourses = $this->garantModel->getGarantCourses($this->user->identity->id);

		if($lectorCourses == NULL && $garantCourses == NULL)
		{
			$this->template->courses = NULL;
		}
		else if($garantCourses == NULL)
		{
			$this->template->courses = $lectorCourses;
		}
		else if($lectorCourses == NULL)
		{
			$this->template->courses = $garantCourses;
		}
		else
		{
			$this->template->courses = array_merge($lectorCourses,$garantCourses);
		}
	}
}
