<?php

declare(strict_types=1);

namespace App\Presenters;
use Nette;
use Nette\Application\UI\Form;


class StudentPresenter extends BasePresenter
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
}
