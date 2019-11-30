<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class GarantModel
{
    private $lectorModel;
    private $mainModel;
    private $database;
    private $current_course;

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
        $lectorCourses = $this->lectorModel->getLectorCourses($id_garant);
        $garantCourses = $this->database->query("SELECT id_course, course_name, course_type, course_status FROM course WHERE id_guarantor = ?",  $id_garant)->fetchAll();
            
        if($lectorCourses == NULL && $garantCourses == NULL)
        {
            return NULL;
        }
        else if($lectorCourses != NULL && $garantCourses != NULL)
        {
            return array_merge($lectorCourses,$garantCourses);
        }
        else if($garantCourses != NULL)
        {
            return $garantCourses;
        }
        else return $lectorCourses;
    }

    public function createCourseF($meno): Form
    {
        $form = new Form;

        if(count($_POST) > 0)
        {
            $this->current_course = $_POST;
        }

        $form->addHidden('old_id_course', NULL);

        $form->addText('id_course', 'Zkratka kurzu')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired("Tohle pole je povinné")
        ->addRule(Form::PATTERN, 'Zadejte 3 až 5 velkých písmen nebo čísel!', '([A-Z0-9]\s*){3,5}')
        ->addRule(Form::MAX_LENGTH, 'Zadejte 3 až 5 velkých písmen nebo čísel!', 5);

        $form->addText('course_name', 'Název kurzu')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired("Tohle pole je povinné")
        ->addRule(Form::MIN_LENGTH, 'Dĺžka jména musí být 5 až 30 znaků!', 5)
        ->addRule(Form::MAX_LENGTH, 'Dĺžka jména musí být 5 až 30 znaků!', 30);

        $form->addTextArea('course_description', 'Popis')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired("Tohle pole je povinné")
        ->addRule(Form::MIN_LENGTH, 'Dĺžka popisu musí být 5 až 500 znaků!', 5)
        ->addRule(Form::MAX_LENGTH, 'Dĺžka popisu musí být 5 až 500 znaků!', 500);

        $form->addSelect('course_type', 'Typ', [
            'P' => 'Povinný',
            'V' => 'Volitelný',
        ])
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired("Tohle pole je povinné");

        $form->addText('course_price', 'Cena (Kč)')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired("Tohle pole je povinné")
        ->addRule(Form::PATTERN, 'Zadejte číslo v rozmezí 0 - 999 999 999!', '([0-9]\s*){1,9}')
        ->addRule(Form::MAX_LENGTH, 'Zadejte číslo v rozmezí 0 - 999 999 999!', 9);

        $form->addText('tags', 'tags',)
        ->setHtmlAttribute('class', 'form-control');
        \Tracy\Debugger::barDump($this->current_course);
        if($this->current_course)
        {
            $form->setDefaults([
                'old_id_course' => $this->current_course['id_course'],
                'id_course' => $this->current_course['id_course'],
                'course_name' => $this->current_course['course_name'],
                'course_description' => $this->current_course['course_description'],
                'course_type' => $this->current_course['course_type'],
                'course_price' => $this->current_course['course_price'],
                'tags' => $this->current_course['tags'],
            ]);

            $form->addSubmit('create', 'Upravit kurz')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');
        }
        else
        {
            $form->addSubmit('create', 'Vytvořit kurz')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');
        }
        
        $form->onSuccess[] = [$meno, 'createCourseForm'];
        return $form;
    }

    private $currentCourseId;
    public function renderShowCourse($presenter,$id)
    {
        $this->lectorModel->renderShowCourse($presenter,$id);
            
        //garant sa nemoze registrovat na svoj kurz
        if($presenter->user->identity->id == $presenter->template->course->id_guarantor)
        {
            $presenter->template->userIsGuarantorInCourse=true;
        }
        else
        {
            $presenter->template->userIsGuarantorInCourse=false;
        }

        
        
        $this->currentCourseId=$id;

  
    }

    public function createComponentOpenRegisterForm($presenter)
    {
        $form = new Nette\Application\UI\Form;

        $form->addHidden('id_course');

        $form->setDefaults([
            'id_course' => $this->currentCourseId,

        ]);

        $form->addSubmit('register', 'Otevřít registrace do kurzu')
        ->setHtmlAttribute('class', 'btn btn-sm btn-primary ajax');
        
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

        $form->addSubmit('register', 'Zavřít registrace do kurzu')
        ->setHtmlAttribute('class', 'btn btn-sm btn-primary ajax');
        
        $form->onSuccess[] = [$presenter, 'closeRegisterFormHandle'];
        return $form;
    }

    public function getCurrentCourse($presenter, $id_course)
    {
        $this->current_course = $this->database->table("course")->where("id_course",$id_course)->fetch();
        if($this->current_course)
        {
            if($this->current_course->id_guarantor != $presenter->user->identity->id)
            {
                $presenter->redirect("Homepage:");
            }
        }
    }

}