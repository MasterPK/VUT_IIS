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
		$data = array();
		//zobraz garantove predmety, pre ktore existuju ziadosti
		if($this->template->rank == 3)
		{
			$data = $this->database->query("SELECT DISTINCT(id_course), course_name, course_type, id_guarantor FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE id_guarantor = ? AND student_status = 0",  $this->user->identity->id)->fetchAll();
		}
		//zobraz vsetky predmety, pre ktore existuju ziadosti, ak si veduci
		else if($this->template->rank > 3)
		{
			//zobraz predmety, ktore cakaju na schvalenie
			$data2 = $this->database->query("SELECT id_course, course_name, course_type, id_guarantor FROM course WHERE course_status = 0")->fetchAll();

			if($data2)
			{
				foreach($data2 as $course)
				{
					$guarantor = $this->database->query("SELECT first_name, surname FROM user WHERE id_user = ?", $course->id_guarantor)->fetch();

					$course->id_guarantor = $guarantor->first_name . " " . $guarantor->surname;
				}
				$this->template->courses=$data2;
			}

			//zobraz predmety, kde su ziadosti studentov
			$data = $this->database->query("SELECT DISTINCT(id_course), course_name, course_type, id_guarantor FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE student_status = 0")->fetchAll();	
		}
		
		if(count($data) > 0)
		{
			foreach($data as $course)
			{
				$guarantor = $this->database->query("SELECT first_name, surname FROM user WHERE id_user = ?", $course->id_guarantor)->fetch();

				$course->id_guarantor = $guarantor->first_name . " " . $guarantor->surname;
			}
			$this->template->requests=$data;
		}
	}

	private $id_course;
	public function renderRequest($id): void
	{ 
		$requests = NULL;
		$course = $this->database->query("SELECT * FROM course WHERE id_course = ?", $id)->fetch();

		//ak kurz nebol schvaleny, vypis ho
		if($this->template->rank > 3 && $course->course_status == 0)
		{
			$guarantor = $this->database->query("SELECT first_name, surname FROM user WHERE id_user = ?", $course->id_guarantor)->fetch();

			$course->id_guarantor = $guarantor->first_name . " " . $guarantor->surname;
			$this->template->course = $course;

		}
		else
		{
			//ak bol schvaleny, vypis ziadosti
			if($this->template->rank > 3)
			{
				$requests = $this->database->query("SELECT id_user, email, first_name, surname FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE  id_course = ? AND student_status = 0", $id)->fetchAll();
			}
		}
		
		$this->id_course=$id;
		if($requests)
		{
			$this->template->requests = $requests;
		}
	}



	protected function createComponentRegisterCheckBox(): Form
    {
		$requests = $this->database->query("SELECT id_user, email, first_name, surname FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE id_guarantor = ? AND id_course = ? AND student_status = 0", $this->user->identity->id, $this->id_course)->fetchAll();

		if($requests)
		{
			$this->template->requests = $requests;
		}
		$form = new Form;
		$form->getElementPrototype()->class('ajax');
		foreach($requests as $row)
		{
			$form->addCheckbox("id_".strval($row->id_user),"");
		}
        $form->addSubmit('submit', 'Zaregistrovat označené')
        ->setHtmlAttribute('class', 'btn btn-primary ajax');
		
		$form->onSuccess[] = [$this, 'registerStudent'];
        return $form;
    }

    public function registerStudent($form): void
    {
		$values = $form->getValues();
		
    	
	}


	public function handleRegister($id): void
    {
		$this->template->error_notif = 1;
		if ($this->isAjax())
		{
            $this->redrawControl('error_notif_snippet');
        }
		
    	
	}
}
