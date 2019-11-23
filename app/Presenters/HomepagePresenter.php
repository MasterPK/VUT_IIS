<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI;


final class HomepagePresenter extends Nette\Application\UI\Presenter
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

			$userData=new Nette\Security\Identity ($this->user->identity->id,$this->user->identity->rank,$data);

		
			if($userData!=$this->user->identity)
			{
				foreach($data as $key => $item)
				{
					$this->user->identity->$key = $item;
				}
			}
			$this->template->id_user=$data->id_user;
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
		
		if($course)
		{
			$course_guarantor = $this->database->table("user")->where("id_user=?", $course->id_guarantor)->fetch();
			$this->template->guarantor=$course_guarantor->first_name . " " . $course_guarantor->surname;
			switch($course->type)
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
		    'name' => 'Název',
		    'id_course' => 'Zkratka',
		    'type' => 'Typ',
		    'price' => 'Cena',
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

        $form->addSubmit('register', 'Registrovat kurz')
		->setHtmlAttribute('class', 'btn btn-block btn-primary');
		
        return $form;
    }
}
