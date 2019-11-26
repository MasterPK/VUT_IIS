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

	private $id_course;

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
			$data = $this->database->query("SELECT COUNT(*) AS cnt, id_course, course_name, course_type, id_guarantor FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE id_guarantor = ? AND student_status = 0 HAVING cnt > 0",  $this->user->identity->id)->fetchAll();

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



	/*protected function createComponentRegisterCheckBox(): Form
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
		
    	
	}*/

	public function handleRegister($users, $id_course): void
    {
    	//ak neni ziaden checkbox, tak sa odosle []
    	$users = substr($users, 1, -1);
    	//po substr ostane prazdny
    	if(empty($users))
		{
			$this->sendResponse( new Nette\Application\Responses\JsonResponse( ['status' => 'notify'] ) );
			return;	
		}

		//inak tam je aspon jedno id
		$users = preg_split("/[,]/", $users);
		
		//po preg_split sa z toho stava array
		foreach($users as $user)
		{
			$result = $this->database->query("UPDATE course_has_student SET student_status = 1 WHERE id_user = ? AND id_course = ? AND student_status = 0", $user, $id_course);

			//ak sa nejaky update nevykona, ukonci s chybou
			if($result->getRowCount() == 0)
			{
				$this->redrawControl("success_reg_snippet");
				$this->sendResponse( new Nette\Application\Responses\JsonResponse( ['status' => 'error'] ) );
				return;
			}
		}
		

		if ($this->isAjax())
		{
			$this->redrawControl("success_reg_snippet");
            $this->sendResponse( new Nette\Application\Responses\JsonResponse( ['status' => 'success'] ) );
        }
		
    	
	}

	public function handleApproveCourse($id): void
    {

		
		if(empty($id))
		{
			return;
		}

		$count = $this->database->table('course')
		->where('id_course', $id)
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
