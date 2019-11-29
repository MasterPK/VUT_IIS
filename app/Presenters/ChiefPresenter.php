<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


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
		if (!$this->startup->roleCheck($this, 3)) {
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
		$this->template->courses = $this->mainModel->getAllCourses();
	}

	public function renderRooms(): void
	{
		$data = $this->database->query("SELECT * FROM room NATURAL JOIN room_address")->fetchAll();
		$this->template->rooms = $data;
	}

	public function renderRoomsEquipment($id)
	{
		$data = $this->database->query("SELECT * FROM room_equipment NATURAL JOIN room_has_equipment WHERE id_room = ?",  $id)->fetchAll();

		$this->template->equip = $data;
		$this->template->id = $id;
	}

	private $actual_room;
	public function renderAddEquipment($id)
	{
		$this->actual_room = $id;
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
		$this->template->courses = array_merge($lectorCourses, $garantCourses);
	}

	public function rendershowCourse($id)
	{
		$this->garantModel->renderShowCourse($this, $id);
	}

	public function createComponentCreateCourseForm(): Form
	{
		return $this->garantModel->createCourseF($this);
	}

	public function createCourseForm(Nette\Application\UI\Form $form): void
	{
		$values = $form->getValues();

		try {
			$data = $this->database->query("INSERT INTO course (id_course, course_name, course_description, course_type, course_price, id_guarantor, course_status) VALUES (?, ?, ?, ?, ?, ?, 0)", $values->id_course, $values->name, $values->description, $values->type, $values->price,  $this->user->identity->id);

			$this->template->success_insert = true;
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			$this->template->error_insert = true;
			$this->template->error_course = $values->id_course;
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

		if ($get->getRowCount() == 1) {
			$this->template->succes_notif = true;
		} else {
			$this->template->error_notif = false;
		}

		if ($this->isAjax()) {
			$this->redrawControl('content_snippet');
		}
	}

	public function closeRegisterFormHandle($form)
	{
		$values = $form->getValues();
		$get = $this->database->query("UPDATE course SET course_status = 3 WHERE id_course = ?", $values->id_course);

		if ($get->getRowCount() == 1) {
			$this->template->succes_notif = true;
		} else {
			$this->template->error_notif = false;
		}

		if ($this->isAjax()) {
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


	public function renderCreateRoom(): void
	{ }


	public function createComponentCreateRoom()
	{
		$form = new Form;

		$form->addText('room_id', '')
			->setHtmlAttribute('class', 'form-control')
			->setRequired();

		$form->addText('room_type', '')
			->setHtmlAttribute('class', 'form-control')
			->setRequired();

		$form->addInteger('room_capacity', '')
			->setHtmlAttribute('class', 'form-control')
			->setRequired();

		$tmp = $this->database->query("SELECT * FROM room_address")->fetchAll();

		$address = array();
		foreach ($tmp as $row) {
			$address[$row->id_room_address]= $row->room_address;
		}

		$form->addSelect('room_Adres', '', $address)
			->setHtmlAttribute('class', 'form-control')
			->setRequired();

		$form->addSubmit('submit', 'Vytvořit místnost')
			->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

		$form->onSuccess[] = [$this, 'createRoomSubmit'];
		return $form;
	}

	public function createRoomSubmit(Form $form)
	{
		$values = $form->getValues();
		Debugger::barDump($values->room_id,"values->room_id");
		Debugger::barDump($values->room_Adres,"values->room_Adres");

		$data = $this->database->table("room")
			->insert([
				'id_room' => $values->room_id,
				'room_type' => $values->room_type,
				'room_capacity' => $values->room_capacity,
				'id_room_address' => $values->room_Adres,
			]);

		$this->redirect("Chief:rooms");
	}



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
	private $current_Equip;

	private $current_room;
	public function renderChangeAdres($id)
	{
		$this->current_Adres = $this->database->table("room_address")->where("id_room_address", $id)->fetch();
	}

	public function renderChangeEquipment($id)
	{
		$this->current_Equip = $this->database->table("room_equipment")->where("id_room_equipment", $id)->fetch();
	}

	public function renderManageRoom($id)
	{
		$this->current_room = $this->database->table("room")->where("id_room", $id)->fetch();
	}

	public function createComponentUpdateRoom()
	{

		$form = new Form;

        $form->addHidden('id_room', '')
			->setDefaultValue($this->current_room["id_room"]);

		$form->addText('room_id', '')
			->setHtmlAttribute('class', 'form-control')
			->setRequired()
			->setDefaultValue($this->current_room["id_room"]);

		$form->addText('room_type', '')
			->setHtmlAttribute('class', 'form-control')
			->setRequired()
			->setDefaultValue($this->current_room["room_type"]);

		$form->addInteger('room_capacity', '')
			->setHtmlAttribute('class', 'form-control')
			->setRequired()
			->setDefaultValue($this->current_room["room_capacity"]);

		$tmp = $this->database->query("SELECT * FROM room_address")->fetchAll();

		$address = array();
		foreach ($tmp as $row) {
			$address[$row->id_room_address]= $row->room_address;
		}

		$form->addSelect('room_Adres', '', $address)
			->setHtmlAttribute('class', 'form-control')
			->setRequired()
			->setDefaultValue($this->current_room["id_room_address"]);

		$form->addSubmit('submit', 'Upravit místnost')
			->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

		$form->onSuccess[] = [$this, 'updateRoomSubmit'];
		return $form;
	}



	public function updateRoomSubmit(Form $form)
	{
		$values = $form->getValues();
		Debugger::barDump($values->room_Adres,"test");

		$data = $this->database->table("room")->where('id_room',$values->id_room)
			->update([
				'id_room' => $values->room_id,
				'room_type' => $values->room_type,
				'room_capacity' => $values->room_capacity,
				'id_room_address' => $values->room_Adres,
			]);

		$this->template->success_notify = true;
		$this->redirect("Chief:rooms");
	}


	public function createComponentDeleteAdres()
	{
		$form = new Form;

		$form->addHidden('id_room_address', '')
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

		$this->database->table("room_address")->where("id_room_address", $values->id_room_address)->delete();
		$this->redirect("Chief:manageAdres");
	}

	public function createComponentChangeAdres()
	{
		$form = new Form;

		$form->addHidden('id_room_address', '')
			->setDefaultValue($this->current_Adres);

		$form->addText('id_course_show', '')
			->setHtmlAttribute('class', 'form-control')
			->setDefaultValue($this->current_Adres["room_address"]);

		$form->addSubmit('submit', 'Potvrdit změny')
			->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

		$form->onSuccess[] = [$this, 'ChangeAdresSubmit'];
		return $form;
	}

	public function ChangeAdresSubmit(Form $form)
	{
		$values = $form->getValues();

		$data = $this->database->table("room_address")->where("id_room_address", $values->id_room_address)
			->update([
				'room_address' => $values->id_course_show,
			]);

		$this->template->success_notify = true;
		if ($this->isAjax()) {
			$this->redrawControl("notify");
		}
	}





	public function createComponentDeleteEquipment()
	{
		$form = new Form;

		$form->addHidden('id_room_equipment', '')
			->setRequired()
			->setDefaultValue($this->current_Equip);

		$form->addCheckBox("really")
			->setRequired()
			->addCondition(Form::EQUAL, true);

		$form->addSubmit('submit', 'Smazat?!')
			->setHtmlAttribute('class', 'btn btn-primary');

		$form->onSuccess[] = [$this, 'deleteEquipmentSubmit'];

		return $form;
	}

	public function deleteEquipmentSubmit(Form $form)
	{
		$values = $form->getValues();

		$this->database->table("room_equipment")->where("id_room_equipment", $values->id_room_equipment)->delete();
		$this->redirect("Chief:manageEquipment");
	}

	public function createComponentChangeEquipment()
	{
		$form = new Form;

		$form->addHidden('id_room_equipment', '')
			->setDefaultValue($this->current_Equip);

		$form->addText('id_course_show', '')
			->setHtmlAttribute('class', 'form-control')
			->setDefaultValue($this->current_Equip["room_equipment"]);

		$form->addSubmit('submit', 'Potvrdit změny')
			->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

		$form->onSuccess[] = [$this, 'ChangeEquipmentSubmit'];
		return $form;
	}

	public function ChangeEquipmentSubmit(Form $form)
	{
		$values = $form->getValues();

		$data = $this->database->table("room_equipment")->where("id_room_equipment", $values->id_room_equipment)
			->update([
				'room_equipment' => $values->id_course_show,
			]);

		$this->template->success_notify = true;
		if ($this->isAjax()) {
			$this->redrawControl("notify");
		}
	}

	public function createComponentAddEquip()
	{
		$form = new Form;

		$tmp = $this->database->query("SELECT * FROM room_equipment WHERE room_equipment.id_room_equipment NOT IN (SELECT id_room_equipment FROM room_has_equipment WHERE room_has_equipment.id_room = ? )",$this->actual_room)->fetchAll();

		$address = array();
		foreach ($tmp as $row) {
			$address[$row->id_room_equipment]= $row->room_equipment;
		}

		$form->addHidden('id_equip', '')
			->setDefaultValue($this->actual_room);

		$form->addSelect('room_Equip', '', $address)
			->setHtmlAttribute('class', 'form-control')
			->setRequired();

		$form->addSubmit('submit', 'Přidat vybavení')
			->setHtmlAttribute('class', 'btn btn-block btn-primary');

		$form->onSuccess[] = [$this, 'AddEquipSubmit'];
		return $form;
	}

	public function AddEquipSubmit(Form $form)
	{
		$values = $form->getValues();
		
		$data = $this->database->table("room_has_equipment")
			->insert([
				'id_room' => $values->id_equip,
				'id_room_equipment' => $values->room_Equip,
			]);

		$this->template->success_notify = true;
		$this->redirect("Chief:addEquipment",$values->id_equip);
	}

	function handleDelete($id,$id2) {
		$this->database->table("room_has_equipment")->where("id_room_equipment", $id)->where("id_room", $id2)->delete();
		
		$this->redirect("Chief:roomsEquipment",$id2);
	}
	
}
