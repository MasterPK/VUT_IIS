<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI\Form;
use Nette\Utils\Json;

use Tracy\Debugger;


final class RequestPresenter extends Nette\Application\UI\Presenter
{
	/** @var \App\Model\StartUp @inject */
    public $startup;

    /** @var \App\Model\VisitorModel @inject */
    public $visitorModel;

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
		//zobraz svoje predmety, pre ktore existuju ziadosti, ak mas rank garant a vyssi
		if($this->template->rank >= 3)
		{
			$data = $this->database->query("SELECT COUNT(*) AS cnt, id_course, course_name, course_type, id_guarantor FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE id_guarantor = ? AND student_status = 0",  $this->user->identity->id)->fetchAll();

			if(count($data) > 0)
			{
				$this->template->requests=$data;
			}

			//ak si veduci..
			if($this->template->rank > 3)
			{
				//zobraz predmety, ktore cakaju na schvalenie
				$data2 = $this->database->query("SELECT id_course, course_name, course_type, id_guarantor FROM course WHERE course_status = 0")->fetchAll();

				if($data2)
				{
					foreach($data2 as $course)
					{
						$course->id_guarantor = $this->visitorModel->getCourseGuarantorName($course->id_guarantor);
					}
					$this->template->courses=$data2;
				}
			}
		}
	}

	private $id_course;
	public function renderRequest($id): void
	{ 
		$requests = NULL;
		$this->template->id_course=$id;
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

	public function handleRegister($users, $course): void
    {

		$this->flashMessage("register handle");
		if(empty($users))
		{
			//return;
		}

		/*foreach($users as $user)
		{
			$result = $this->database->table('course_has_student')
			->where('id_course', $course)
			->where('id_user', $user)
			->where('student_status', 0)
			->update([
				'student_status' => '1'
			]);

			if($result->getRowCount() == 0)
			{
				return;
			}
		}*/
		

		if ($this->isAjax())
		{
			$this->template->error_notif = 2;
			$this->redrawControl('error_notif_snippet');
            //$this->sendResponse( new Nette\Application\Responses\JsonResponse( ['status' => 'error', 'message' => 'Problem'] ) );
        }
		
    	
	}

	public function handleApproveCourse($id_course): void
    {

		
		if(empty($id_course))
		{
			return;
		}

		$count = $this->database->table('course')
		->where('id_course', $id_course)
		->update([
			'course_status' => '1'
		]);

		if ($this->isAjax() && $count==1)
		{
			$this->template->error_notif = 3;
            $this->redrawControl('content_snippet');
		}
		else
		{
			$this->template->error_notif = 4;
            $this->redrawControl('content_snippet');
		}
		
		
    	
	}
}
