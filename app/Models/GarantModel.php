<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class GarantModel
{
    private $lectorModel;
    private $mainModel;
    private $database;
	public function __construct(Nette\Database\Context $database, \App\Model\MainModel $mainModel, \App\Model\LectorModel $lectorModel)
	{
        $this->database = $database;
        $this->mainModel = $mainModel;
        $this->lectorModel=$lectorModel;
	}
	

    public function getCoursesOfGarant($id_garant)
    {
        return $this->mainModel->getCoursesOfStudent($id_garant);
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
       
            //garant sa nemoze registrovat na svoj kurz
            if($presenter->user->identity->id != $presenter->template->course->id_guarantor)
            {
                $presenter->template->userIsNotGuarantorInCourse=true;
            }
  
    }

    public function createComponentOpenRegisterForm($presenter)
	{
		$form = new Nette\Application\UI\Form;

		$form->addHidden('id_course');

		$form->setDefaults([
            'id_course' => $this->currentCourseId,

        ]);

        $form->addSubmit('register', 'Otevřít registrace do kurzu')
		->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');
		
		$form->onSuccess[] = [$presenter, 'openRegisterFormHandle'];
        return $form;
	}

	public function createComponentCloseRegisterForm($presenter)
	{
		$form = new Nette\Application\UI\Form;

		$form->addHidden('id_course');

		$form->setDefaults([
            'id_course' => $this->currentCourseId,

        ]);

        $form->addSubmit('register', 'Otevřít registrace do kurzu')
		->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');
		
		$form->onSuccess[] = [$presenter, 'closeRegisterFormHandle'];
        return $form;
	}

}