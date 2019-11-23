<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


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
		$data = $this->database->query("SELECT id_course, name, type, price FROM user NATURAL JOIN course_has_lecturer NATURAL JOIN course WHERE id_user = ?",  $this->user->identity->id);

		if($data->getRowCount() > 0)
		{
			$this->template->courses=$data;
		}
	}

	protected function createComponentCreateCourseForm(): Nette\Application\UI\Form
    {
        $form = new Nette\Application\UI\Form;

        $form->addText('id_course', 'Zkratka kurzu')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired("Zkratka kurzu");

        $form->addText('name', 'Název kurzu')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired();

        $form->addText('description', 'Popis')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired();

        $form->addSelect('type', 'Typ', [
		    'P' => 'Povinný',
		    'V' => 'Volitelný',
		])
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired();

		$form->addText('price', 'Cena')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired();

        $form->addSubmit('create', 'Vytvořit kurz')
        ->setHtmlAttribute('class', 'btn btn-block btn-primary');
        
        $form->onSuccess[] = [$this, 'createCourseForm'];
        return $form;
	}

	public function createCourseForm(Nette\Application\UI\Form $form): void
    {
    	$values = $form->getValues();
    	$data = $this->database->query("INSERT INTO course (id_course, name, description, type, price, id_guarantor) VALUES ('', ?, ?, ?, ?, ?)", $values->id_course, $values->name, $values->description, $values->type, $values->price,  $this->user->identity->id);
	}
}
