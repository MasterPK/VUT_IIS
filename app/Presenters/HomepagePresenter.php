<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
	/** @var \App\Model\StartUp @inject */
    public $startup;

	private $database;
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	private $current_course_id;


	public function startUp()
	{
		parent::startup();

		$this->startup->mainStartUp($this); 
		
	}


	public function renderDefault(): void
	{ 

	}

	public function renderCourses($search, $filter): void
	{
		if($search)
		{
			$data = $this->database->table("course")->where( $filter . " LIKE ?",  "%" . $search . "%")->fetchAll();		
			
			if($data)
			{
				$this->template->courses=$data;
			}
		}
		else
		{
			$data = $this->database->table("course")->fetchAll();
			if($data)
			{
				$this->template->courses=$data;
			}	
		}
	}

	public function renderShowcourse($id): void
	{
		if(empty($id))
		{
			$this->redirect('Homepage:courses');
		}
		$course = $this->database->table("course")->where("id_course=?", $id)->fetch();
		$this->current_course_id=$id;
		
		

		if($course)
		{

			$request = $this->database->table("request")->where("id_course=? AND id_user=?", $id, $this->user->identity->id )->fetch();

			if($request)
			{
				$this->template->request=$request->state;
			}

			$course_guarantor = $this->database->table("user")
				->where("id_user=?", $course->id_guarantor)
				->fetch();

			$this->template->guarantor=$course_guarantor->first_name . " " . $course_guarantor->surname;
			
			$this->template->register=true;
			//garant sa nemoze registrovat na svoj kurz
			if($this->user->identity->id == $course->id_guarantor)
			{
				$this->template->register=false;
			}
			$course_lectors = $this->database->query("SELECT id_user FROM user NATURAL JOIN course_has_lecturer NATURAL JOIN course WHERE id_user = ? AND id_course = ?",  $this->user->identity->id, $course->id_course);
			//ani lektori sa nemozu registrovat na kurzy, ktore ucia
			if($course_lectors->getRowCount() > 0)
			{
				$this->template->register=false;
			}

			$course_students = $this->database->query("SELECT id_user FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE id_user = ? AND id_course = ?", $this->user->identity->id, $course->id_course);

			//a ani uz registrovani studenti
			if($course_students->getRowCount() > 0)
			{
				$this->template->register=false;
			}

			switch($course->course_type) 
			{
				case "P":$this->template->type="Povinný";break;
				case "V":$this->template->type="Volitelný";break;
			}
			$this->template->course=$course;
		}
		else
		{
			$this->redirect('Homepage:courses');
		}
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
    	$this->redirect("Homepage:courses", $values->search, $values->filter);
	}
	

	protected function createComponentRegisterForm(): UI\Form
    {
		$form = new UI\Form;
		$form->getElementPrototype()->class('ajax');
		$form->addHidden('id_course');

		$form->setDefaults([
            'id_course' => $this->current_course_id,

        ]);

        $form->addSubmit('register', 'Registrovat kurz')
		->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');
		
		$form->onSuccess[] = [$this, 'addNotification'];
        return $form;
    }

    public function addNotification($form): void
    {
		
		$values = $form->getValues();
    	$get = $this->database->query("SELECT id_request FROM request WHERE id_course = ? AND id_user = ?", $values->id_course, $this->user->identity->id);

    	if($get->getRowCount() == 0)
    	{
			$data = $this->database->query("INSERT INTO request ( id_request, id_course, id_user) VALUES ('', ?, ?)", $values->id_course, $this->user->identity->id);
			$this->template->error_notif = 2;
    	}
    	else
    	{
    		$this->template->error_notif = 1;
		}
		
		if ($this->isAjax())
		{
            $this->redrawControl('error_notif_snippet');
        }
    	
	}
}
