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
		$data = $this->database->query("SELECT DISTINCT(id_course), course_name, course_type, course_price FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE (id_guarantor = ? AND student_status = 0) OR course_status=0",  $this->user->identity->id)->fetchAll();

		if($data->getRowCount() > 0)
		{
			$this->template->requests=$data;
		}
	}

	private $id_course;
	public function renderRequest($id): void
	{ 
		$requests = $this->database->query("SELECT id_user, email, first_name, surname FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE id_guarantor = ? AND id_course = ? AND student_status = 0", $this->user->identity->id, $id)->fetchAll();
		$this->id_course=$id;
		if($requests)
		{
			$this->template->requests = $requests;
		}
	}



	protected function createComponentRegisterCheckBox(): Form
    {
		$requests = $this->database->query("SELECT id_user, email, first_name, surname FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE id_guarantor = ? AND id_course = ? AND student_status = 0", $this->user->identity->id, $this->id_course)->fetchAll();

		$form = new Form;
		$main = $form->addContainer('main');
		foreach($requests as $row)
		{
			dump($row->id_user);
			$main->addCheckbox(strval($row->id_user), 'a');
		}
        $form->addSubmit('submit', 'Zaregistrovat označené')
        ->setHtmlAttribute('class', 'btn btn-primary');
		
		$form->onSuccess[] = [$this, 'registerStudent'];
        return $form;
    }

    public function registerStudent($form): void
    {
		
		
    	
	}
}
