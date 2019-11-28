<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class ChiefPresenter extends Nette\Application\UI\Presenter 
{
	/** @var \App\Model\StartUp @inject */
    public $startup;

	/** @var Nette\Database\Context @inject */
	public $database;

	/** @var \App\Model\GarantModel @inject */
	public $garantModel;

	/** @var \App\Model\StudentModel @inject */
	public $studentModel;

	/** @var \App\Model\MainModel @inject */
	public $mainModel;



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
	{ }

	/**
	 * Generuje aktuálne zapsané predmety lektora
	 *
	 * @return void
	 */
	public function renderCourses(): void
	{
		$this->template->courses=$this->mainModel->getAllCourses();
	}

	public function renderRooms(): void
	{
		$data = $this->database->table("room")->fetchAll();
		$this->template->rooms = $data;
	}

	public function renderManageAdres(): void
	{
		$data = $this->database->table("room_address")->fetchAll();
		$this->template->rooms = $data;
	}

	public function renderManageEquipment(): void
	{
		$data = $this->database->table("room_equipment")->fetchAll();
		$this->template->rooms = $data;
	}

	public function renderGarantCourses()
	{
		$lectorCourses = $this->garantModel->getLectorCourses($this->user->identity->id);
		$garantCourses = $this->garantModel->getGarantCourses($this->user->identity->id);
		$this->template->courses = array_merge($lectorCourses,$garantCourses);
	}

	public function rendershowCourse($id)
	{
		$this->garantModel->renderShowCourse($this,$id);
	}
	
	public function createComponentCreateCourseForm(): Form
	{
		return $this->garantModel->createCourseF($this);
	}

	public function createCourseForm(Nette\Application\UI\Form $form): void
    {
    	$values = $form->getValues();

    	try
    	{
    		$data = $this->database->query("INSERT INTO course (id_course, course_name, course_description, course_type, course_price, id_guarantor, course_status) VALUES (?, ?, ?, ?, ?, ?, 0)", $values->id_course, $values->name, $values->description, $values->type, $values->price,  $this->user->identity->id);

    		$this->template->success_insert = true;
    	}
    	catch(Nette\Database\UniqueConstraintViolationException $e)
    	{
    		$this->template->error_insert=true;
    		$this->template->error_course=$values->id_course;
    	}
	}

	public function createComponentRegisterForm()
	{
		return $this->studentModel->createComponentRegisterForm($this);
	}

	public function createComponentUnRegisterForm()
	{
		return $this->studentModel->createComponentUnRegisterForm($this);
	}

	public function createComponentOpenRegisterForm()
	{
		return $this->garantModel->createComponentOpenRegisterForm($this);
	}

	public function createComponentCloseRegisterForm()
	{
		return $this->garantModel->createComponentCloseRegisterForm($this);
	}

	public function openRegisterFormHandle($form)
	{
		$values = $form->getValues();
		$get = $this->database->query("UPDATE course SET course_status = 2 WHERE id_course = ?", $values->id_course);

    	if($get->getRowCount() == 1)
    	{
    		$this->template->succes_notif = true;
    	}
    	else
    	{
    		$this->template->error_notif = false;
    	}	

    	if ($this->isAjax())
		{
            $this->redrawControl('content_snippet');
        }
	}

	public function closeRegisterFormHandle($form)
	{
		$values = $form->getValues();
		$get = $this->database->query("UPDATE course SET course_status = 3 WHERE id_course = ?", $values->id_course);

    	if($get->getRowCount() == 1)
    	{
    		$this->template->succes_notif = true;
    	}
    	else
    	{
    		$this->template->error_notif = false;
    	}	

    	if ($this->isAjax())
		{
            $this->redrawControl('content_snippet');
        }
	}

	public function createComponentSearchCourseForm(): Nette\Application\UI\Form
    {
        return $this->mainModel->createComponentSearchCourseForm($this);
	}
	
	public function searchCourseForm(Nette\Application\UI\Form $form): void
    {
    	$values = $form->getValues();
    	$this->redirect("Homepage:courses", $values->search, $values->filter);
	}

	/*public function createComponentCreateRoom()
    {
        $form = new Form;

        $form->addHidden('id_course', '')
			->setDefaultValue($this->current_course["id_course"]);
			
		$form->addText('id_course_show', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setDisabled()
            ->setDefaultValue($this->current_course["id_course"]);

        $form->addText('course_name', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($this->current_course["course_name"]);

        $form->addTextArea('course_description', '')
            ->setHtmlAttribute('class', 'form-control')
			->setRequired()
			->addRule(Form::MAX_LENGTH, 'Popis je příliš dlouhý', 499)
            ->setDefaultValue($this->current_course["course_description"]);

        $form->addText('course_type', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($this->current_course["course_type"]);

        $form->addInteger('course_price', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($this->current_course["course_price"]);

        $form->addSubmit('submit', 'Potvrdit změny')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

        $form->onSuccess[] = [$this, 'editCourseSubmit'];
        return $form;
	}*/
	
	public function createComponentCreateEquipment()
    {
		$form = new Form;
		
		$form->addText('equip_name', '')
            ->setHtmlAttribute('class', 'form-control');

        $form->addSubmit('submit', 'Potvrdit změny')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

        $form->onSuccess[] = [$this, 'createEquipmenttSubmit'];
        return $form;
	}

	public function createEquipmenttSubmit(Form $form)
    {
        $values = $form->getValues();

        $data = $this->database->table("room_equipment")
            ->insert([
                'room_equipment' => $values->equip_name,
            ]);

        $this->template->success_notify = true;
        if ($this->isAjax()) {
			$form->setValues([], TRUE);
            $this->redrawControl("content_snippet");
        }
	}
	
	public function createComponentCreateAdres()
    {
		$form = new Form;
		
		$form->addText('adres_name', '')
            ->setHtmlAttribute('class', 'form-control');

        $form->addSubmit('submit', 'Potvrdit změny')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

        $form->onSuccess[] = [$this, 'createAdresSubmit'];
        return $form;
	}

	public function createAdresSubmit(Form $form)
    {
        $values = $form->getValues();

        $data = $this->database->table("room_address")
            ->insert([
                'room_address' => $values->adres_name,
            ]);

        $this->template->success_notify = true;
        if ($this->isAjax()) {
			$form->setValues([], TRUE);
            $this->redrawControl("content_snippet");
        }
	}
	

	private $current_Adres;
	public function renderModifyCourse($id)
	{
		$this->current_Adres=$this->database->table("room_address")->where("id_room_address",$id)->fetch();

	}

	public function createComponentDeleteAdres()
	{
		$form = new Form;

        $form->addHidden('id_course', '')
            ->setRequired()
			->setDefaultValue($this->current_Adres);

		$form->addCheckBox("really")
		->setRequired()
		->addCondition(Form::EQUAL, true);
			
		$form->addSubmit('submit', 'Smazat?!')
			->setHtmlAttribute('class', 'btn btn-primary');
			
		$form->onSuccess[] = [$this, 'deleteAdresSubmit'];

		return $form;
	}

	public function deleteAdresSubmit(Form $form)
    {
		$values = $form->getValues();

		$this->database->table("room_address")->where("id_room_address",$values->id_room_address)->delete();
		$this->redirect("Chief:manageAdres");
	}

	public function createComponentChangeAdres()
    {
        $form = new Form;

        $form->addHidden('id_course', '')
			->setDefaultValue($this->current_Adres);
			
		$form->addText('id_course_show', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setDefaultValue($this->current_Adres["room_address"]);

        $form->addSubmit('submit', 'Potvrdit změny')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

        $form->onSuccess[] = [$this, 'ChangeAdresSubmit'];
        return $form;
	}

	public function ChangeAdresSubmit()
	{
        $values = $form->getValues();

        $data = $this->database->table("room_address")->where("id_room_address", $values->id_room_address)
            ->update([
                'room_address' => $values->room_address,
            ]);

        $this->template->success_notify = true;
        if ($this->isAjax()) {
            $this->redrawControl("notify");
        }
	}


}