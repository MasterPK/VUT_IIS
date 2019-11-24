<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI\Form;


final class RequestPresenter extends Nette\Application\UI\Presenter
{
	/** @var \App\Model\StartUp @inject */
    public $startup;

	private $database;
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


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
	{ 
		$data = $this->database->query("SELECT DISTINCT(id_course), course_name, course_type, course_price FROM user NATURAL JOIN request NATURAL JOIN course WHERE id_guarantor = ?",  $this->user->identity->id);

		if($data->getRowCount() > 0)
		{
			$this->template->requests=$data;
		}
	}

	public function renderRequest($id): void
	{ 
		$requests = $this->database->query("SELECT id_user, email, first_name, surname FROM user NATURAL JOIN request NATURAL JOIN course WHERE id_guarantor = ? AND id_course = ?", $this->user->identity->id, $id);
		if($requests->getRowCount() > 0)
		{
			$this->template->requests_length=sizeof($requests);
			$this->template->requests = $requests;
		}
	}

	protected function createComponentRegisterCheckBox(): Form
    {
		$form = new Form;

        $form->addCheckbox('registerBox', '')
    		->setDefaultValue(false);
		
		$form->onSuccess[] = [$this, 'registerStudent'];
        return $form;
    }

    public function registerStudent($form): void
    {
		
		
    	
	}
}
