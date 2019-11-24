<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class StudentPresenter extends Nette\Application\UI\Presenter 
{
	private $database;
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	public function startUp()
	{
		parent::startup();

		if ($this->getUser()->isLoggedIn()) 
		{
			
			$data = $this->database->table("user")
				->where("id_user=?", $this->user->identity->id)
				->fetch();

			if($data->rank<1)
			{
				$this->redirect("Homepage:");
			}

			$userData=new Nette\Security\Identity ($this->user->identity->id,$this->user->identity->rank,$data);

		
			if($userData!=$this->user->identity)
			{
				foreach($data as $key => $item)
				{
					$this->user->identity->$key = $item;
				}
			}
			$this->template->rank=$data->rank;
			switch($data->rank)
			{
				case 1: $this->template->rank_msg="Student";break;
				case 2: $this->template->rank_msg="Lektor";break;
				case 3: $this->template->rank_msg="Garant";break;
				case 4: $this->template->rank_msg="Vedoucí";break;
				case 5: $this->template->rank_msg="Administrátor";break;
			}
		} 
		else 
		{
			$this->template->rank=0;
			$this->template->rank_msg = "Neregistrovaný návštěvník";
		}
	}


	public function renderDefault(): void
	{ }

	public function renderCourses(): void
	{
		$data = $this->database->query("SELECT id_course, name, type, price FROM user NATURAL JOIN course_has_student NATURAL JOIN course WHERE id_user = ?",  $this->user->identity->id);

		if($data->getRowCount() > 0)
		{
			$this->template->courses=$data;
		}
	}

	public function renderLector(): void
	{
		$courses = array();
		switch($this->template->rank)
		{
			case 3:
				$data = $this->database->query("SELECT id_course, name, type, price FROM course WHERE id_guarantor = ?",  $this->user->identity->id)->fetchAll();
				
				foreach($data as $course)
				{
					array_push($courses, $course);
				}
			case 2:
				$data = $this->database->query("SELECT id_course, name, type, price FROM user NATURAL JOIN course_has_lecturer NATURAL JOIN course WHERE id_user = ?",  $this->user->identity->id)->fetchAll();

				foreach($data as $course)
				{
					array_push($courses, $course);
				}
				break;
		}
		

		if(count($courses) > 0)
		{
			$this->template->courses=$data;
		}
	}

	protected function createComponentCreateCourseForm(): Form
    {
        $form = new Form;

        $form->addText('id_course', 'Zkratka kurzu')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired()
        ->addRule(Form::PATTERN, 'Zadejte 3 až 5 velkých písmen!', '([A-Z]\s*){3,5}');

        $form->addText('name', 'Název kurzu')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired()
        ->addRule(Form::MIN_LENGTH, 'Dĺžka jména musí být 5 až 30 znaků!', 5)
        ->addRule(Form::MAX_LENGTH, 'Dĺžka jména musí být 5 až 30 znaků!', 30);

        $form->addText('description', 'Popis')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired()
        ->addRule(Form::MIN_LENGTH, 'Dĺžka popisu musí být 5 až 500 znaků!', 5)
        ->addRule(Form::MAX_LENGTH, 'Dĺžka popisu musí být 5 až 500 znaků!', 500);

        $form->addSelect('type', 'Typ', [
		    'P' => 'Povinný',
		    'V' => 'Volitelný',
		])
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired();

		$form->addText('price', 'Cena')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired()
        ->addRule(Form::PATTERN, 'Zadejte číslo v rozmezí 0 - 1000 000 000!', '([0-9]\s*){1,10}');

        $form->addSubmit('create', 'Vytvořit kurz')
        ->setHtmlAttribute('class', 'btn btn-block btn-primary');
        
        $form->onSuccess[] = [$this, 'createCourseForm'];
        return $form;
	}

	public function createCourseForm(Nette\Application\UI\Form $form): void
    {
    	$values = $form->getValues();

    	try
    	{
    		$data = $this->database->query("INSERT INTO course (id_course, name, description, type, price, id_guarantor) VALUES (?, ?, ?, ?, ?, ?)", $values->id_course, $values->name, $values->description, $values->type, $values->price,  $this->user->identity->id);
    	}
    	catch(Nette\Database\UniqueConstraintViolationException $e)
    	{
    		$this->template->error_insert=true;
    		$this->template->error_course=$values->id_course;
    	}
	}
}
