<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class GarantModel
{
    /** @var \App\Model\LectorModel @inject */
    public $lectorModel;

    /** @var \App\Model\MainModel @inject */
    public $mainModel;

    private $database;
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}
	

    public function getCoursesOfGarant($id_garant)
    {
        return $this->lectorModel->getCoursesOfStudent($id_garant);
    }

    public function getGarantCourses($id_garant)
    {
        $data = $this->database->query("SELECT id_course, course_name, course_type, course_status FROM course WHERE id_guarantor = ?",  $id_garant)->fetchAll();
        if(count($data) > 0)
        {
            return $data;
        }
    }

    public function createCourseF($meno): Form
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
        
        $form->onSuccess[] = [$meno, 'createCourseForm'];
        return $form;
    }
    
    public function renderShowCourse($presenter,$id)
    {
        $this->lectorModel->renderShowCourse($presenter,$id);
        if($request)
			{
			}
			//ak su otvorene registracie na kurz..
			if($presenter->template->course->course_status == 2)
			{
				
				
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
				
			}

			
			$this->template->course=$course;
    }

}