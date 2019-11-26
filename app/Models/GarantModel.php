<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class GarantModel
{
    /** @var \App\Model\StudentModel @inject */
    public $studentModel;

    private $database;
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}
	

    public function getCoursesOfGarant($id_garant)
    {
        return $this->studentModel->getCoursesOfStudent($id_garant);
    }

    public function getGarantCourses($id_garant)
    {
        $courses = array();
        $data = $this->database->query("SELECT id_course, course_name, course_type, course_status FROM course WHERE id_guarantor = ?",  $this->user->identity->id);
        if($data->getRowCount() > 0)
        {
            foreach($data as $course)
            {
                array_push($courses, $course);
            }
        }

        if(count($courses) > 0)
		{
			return $courses;
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

}