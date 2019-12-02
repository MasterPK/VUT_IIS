<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI\Form;
use Nette\Utils\Json;
use Ublaboo\DataGrid\DataGrid;
use Tracy\Debugger;


final class RequestPresenter extends Nette\Application\UI\Presenter
{
	/** @var \App\Model\StartUp @inject */
    public $startup;

    /** @var \App\Model\VisitorModel @inject */
	public $visitorModel;
	
	/** @var \App\Model\MainModel @inject */
	public $mainModel;

	/** @var \App\Model\DataGridModel @inject */
    public $dataGridModel;

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
						$course->id_guarantor = $this->mainModel->getCourseGuarantorName($course->id_guarantor);
					}
					$this->template->courses=$data2;
				}
			}
		}
	}

	public function renderRequest($id_course): void
	{ 
		$requests = NULL;
		$this->template->id_course=$id_course;
		$course = $this->database->query("SELECT * FROM course WHERE id_course = ?", $id_course)->fetch();
		//ak kurz nebol schvaleny, vypis ho
		if($this->template->rank > 3 && $course->course_status == 0)
		{
			$guarantor = $this->database->query("SELECT first_name, surname FROM user WHERE id_user = ?", $course->id_guarantor)->fetch();

			$course->id_guarantor = $guarantor->first_name . " " . $guarantor->surname;

			switch($course->course_type)
			{
				case 'P':
					$course->course_type = 'Povinný';
					break;
				default:
					$course->course_type = 'Volitelný';
					break;
			}
			
			$this->template->course = $course;
		}
		else
		{
			//ak bol schvaleny, vypis ziadosti
			if($this->template->rank >= 3)
			{
				$requests = $this->database->query("SELECT id_user, email, first_name, surname FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE  id_course = ? AND student_status = 0", $id_course)->fetchAll();
			}
		}
		$this->id_course=$id_course;
		if($requests)
		{
			$this->template->requests = $requests;
		}
	}

	public function handleRegister($users, $id_course, $accept): void
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
			$result = $this->database->query("UPDATE course_has_student SET student_status = ? WHERE id_user = ? AND id_course = ? AND student_status = 0", $accept, $user, $id_course);

			//ak sa nejaky update nevykona, ukonci s chybou
			if($result->getRowCount() == 0)
			{
				$this->sendResponse( new Nette\Application\Responses\JsonResponse( ['status' => 'error'] ) );
				return;
			}
			else
			{
				$tasks = $this->database->query("SELECT id_task FROM task WHERE id_course = ?", $id_course)->fetchAll();
				foreach($tasks as $task)
				{
					$result = $this->database->query("INSERT INTO student_has_task (id_user, id_task) VALUES (?, ?)", $user, $task->id_task);
					if($result->getRowCount() == 0)
					{
						$this->sendResponse( new Nette\Application\Responses\JsonResponse( ['status' => 'error'] ) );
						return;
					}
				}
			}
		}
		

		if ($this->isAjax())
		{
            $this->sendResponse( new Nette\Application\Responses\JsonResponse( ['status' => 'success'] ) );
        }
		
    	
	}

	public function handleFinish(): void
	{
		if($this->isAjax())
		{
			$this->redrawControl("reg_snippet");
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

		if($count == 1)
		{
			$this->template->success_approve = true;
        }
		else
		{
			$this->template->success_approve = false;
		}

		if($this->isAjax())
		{
			$this->redrawControl('content_snippet');
		}
		
		
    	
	}

	public function handleDenyCourse($id_course): void
    {

		
		if(empty($id_course))
		{
			return;
		}

		$count = $this->database->table('course')
		->where('id_course', $id_course)
		->update([
			'course_status' => '4'
		]);

		if($count == 1)
		{
			$this->template->success_deny = true;
        }
		else
		{
			$this->template->success_deny = false;
		}

		if($this->isAjax())
		{
			$this->redrawControl('content_snippet');
		}
		
    	
	}

	public function createComponentRequests($name)
	{
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey('id_course');
		$grid->setDataSource($this->database->query("SELECT COUNT(*) AS cnt, id_course, course_name, course_type, id_guarantor FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE id_guarantor = ? AND student_status = 0",  $this->user->identity->id)->fetchAll(););

		$grid->addColumnText('id_course', 'Zkratka kurzu')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('course_name', 'Jméno kurzu')
		->setSortable()
		->setFilterText();
		

		$grid->addColumnText('course_type', 'Typ kurzu')
		->setReplacement([
			'P' => 'Povinný',
			'V' => 'Volitelný'
		])
		->setSortable();

		$grid->addFilterSelect('course_type', 'Typ kurzu:', [""=>"Vše", "P" => 'Povinný', "V" => 'Volitelný']);
		
		$grid->addColumnText('cnt', 'Počet žádostí')
		->setSortable()
		->setFilterText();

		$grid->addAction("select1", "", 'Request:request')
		->setIcon('info')
		->setClass("btn btn-sm btn-info");

		$grid->addAction("select2", "", 'approveCourse!')
		->setIcon('check')
		->setClass("btn btn-sm btn-success");

		$grid->addAction("select3", "", 'denyCourse!')
		->setIcon('times')
		->setClass("btn btn-sm btn-danger");

		$grid->setTranslator($this->dataGridModel->dataGridTranslator);
	
		return $grid;
	}
}
