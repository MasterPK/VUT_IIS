<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

use Ublaboo;
use Ublaboo\DataGrid\DataGrid;


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

	private $dataGridTranslator;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
        $this->dataGridTranslator = new Ublaboo\DataGrid\Localization\SimpleTranslator([
            'ublaboo_datagrid.no_item_found_reset' => 'Žádné položky nenalezeny. Filtr můžete vynulovat',
            'ublaboo_datagrid.no_item_found' => 'Žádné položky nenalezeny.',
            'ublaboo_datagrid.here' => 'zde',
            'ublaboo_datagrid.items' => 'Položky',
            'ublaboo_datagrid.all' => 'všechny',
            'ublaboo_datagrid.from' => 'z',
            'ublaboo_datagrid.reset_filter' => 'Resetovat filtr',
            'ublaboo_datagrid.group_actions' => 'Hromadné akce',
            'ublaboo_datagrid.show_all_columns' => 'Zobrazit všechny sloupce',
            'ublaboo_datagrid.hide_column' => 'Skrýt sloupec',
            'ublaboo_datagrid.action' => 'Akce',
            'ublaboo_datagrid.previous' => 'Předchozí',
            'ublaboo_datagrid.next' => 'Další',
            'ublaboo_datagrid.choose' => 'Vyberte',
            'ublaboo_datagrid.execute' => 'Provést',
            'ublaboo_datagrid.per_page_submit' => "Aktualizovat",

            'Name' => 'Jméno',
            'Inserted' => 'Vloženo'
        ]);
    }

	private $actual_room;
	public function createComponentRoomsGrid($name)
	{
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey('id_room');
		$grid->setDataSource($this->database->table("room")->select("room.*,room_address.room_address"));

		$grid->addColumnText('id_room', 'Místnost')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('room_type', 'Typ místnosti')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('room_capacity', 'Kapacita místnosti')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('room_address', 'Adresa místnosti')
		->setSortable()
		->setFilterText();

		$grid->addAction("vybavení", "Zobrazit vybavení", 'Chief:roomsEquipment')
		->setClass("btn btn-xs btn-default btn-info");

        $grid->addAction("select", "", 'Chief:manageRoom')
            ->setIcon('edit')
            ->setClass("btn btn-xs btn-default btn-secondary");
			
        $grid->addAction('delete', '', 'confirmDeleteRoom!')
            ->setIcon('trash')
            ->setTitle('Smazat')
            ->setClass('btn btn-xs btn-danger ajax');			
			
		$grid->addToolbarButton('Chief:createRoom', '')
            ->setIcon('plus')
            ->setTitle('Přidat místnost')
			->setClass('btn btn-xs btn-primary');
			
		$grid->addToolbarButton('Chief:manageEquipment', 'Správa vybavení')
            ->setTitle('Správa vybavení')
			->setClass('btn btn-xs btn-primary');
			
		$grid->addToolbarButton('Chief:manageAdres', 'Správa adres')
            ->setTitle('Správa adres')
            ->setClass('btn btn-xs btn-primary');

		$grid->setTranslator($this->dataGridTranslator);

	
		return $grid;
	}


	public function createComponentManageEquipmentGrid($name)
	{

		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey('room_equipment');
		$grid->setDataSource($this->database->query("SELECT * FROM room_equipment NATURAL JOIN room WHERE id_room = ?", $this->actual_room)->fetchAll());

		$grid->addColumnText('room_equipment', 'Vybavení místnosti')
		->setSortable()
		->setFilterText();
			
        $grid->addAction('delete', '', 'deleteEquipRoom!')
            ->setIcon('trash')
            ->setTitle('Smazat')
            ->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new \Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation('Opravdu chcete odstranit vybavení?'));

		$grid->addToolbarButton('Chief:rooms', '')
			->setIcon('arrow-left')
            ->setTitle('Zpátky')
			->setClass('btn btn-xs btn-primary');
			
		$grid->addToolbarButton('Chief:addEquipment', 'Přidat vybavení',[$this->actual_room])
            ->setTitle('Správa adres')
			->setClass('btn btn-xs btn-primary');
			


		$grid->setTranslator($this->dataGridTranslator);

		return $grid;
	}

	public function createComponentManageAllEquipmentGrid($name)
	{

		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey('id_room_equipment');
		$grid->setDataSource($this->database->table("room_equipment"));

		$grid->addColumnText('room_equipment', 'Vybavení')
		->setSortable()
		->setFilterText();

		$grid->addAction("select", "", 'Chief:changeEquipment')
			->setIcon('edit')
			->setClass("btn btn-xs btn-default btn-secondary");
			
        $grid->addAction('delete', '', 'deleteEquip!')
            ->setIcon('trash')
            ->setTitle('Smazat')
            ->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new \Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation('Opravdu chcet smazat vybavení?'));

		$grid->addToolbarButton('Chief:rooms', '')
			->setIcon('arrow-left')
            ->setTitle('Zpátky')
			->setClass('btn btn-xs btn-primary');
			
		$grid->addToolbarButton('Chief:createEquipment', 'Přidat vybavení')
            ->setTitle('Přidat vybavení')
			->setClass('btn btn-xs btn-primary');


		$grid->setTranslator($this->dataGridTranslator);

		return $grid;
	}


	public function createComponentManageAllAdresesGrid($name)
	{

		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey('id_room_address');
		$grid->setDataSource($this->database->table("room_address"));

		$grid->addColumnText('room_address', 'Adresy')
		->setSortable()
		->setFilterText();

		$grid->addAction("select", "", 'Chief:changeAdres')
			->setIcon('edit')
			->setClass("btn btn-xs btn-default btn-secondary");
			
        $grid->addAction('delete', '', 'deleteAdres!')
            ->setIcon('trash')
            ->setTitle('Smazat')
            ->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new \Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation('Opravdu chcet smazat adresu?'));

		$grid->addToolbarButton('Chief:rooms', '')
			->setIcon('arrow-left')
            ->setTitle('Zpátky')
			->setClass('btn btn-xs btn-primary');
			
		$grid->addToolbarButton('Chief:createAdres', 'Přidat adresu')
            ->setTitle('Přidat vybavení')
			->setClass('btn btn-xs btn-primary');


		$grid->setTranslator($this->dataGridTranslator);

		return $grid;
	}


	public function handleDeleteAdres($id_room_address)
    {

		try
		{
			$this->database->table("room_address")->where("id_room_address", $id_room_address)->delete();

			$this->template->success_notify = true;
			if ($this->isAjax()) {
				$this->redrawControl('content_snipet');
			} else {
				$this->redirect('this');
			}
		}
		catch(Nette\Database\ForeignKeyConstraintViolationException $e)
		{
			$this->template->error_notify = true;

			if ($this->isAjax()) {
				$this->redrawControl('notify');
			} else {
				$this->redirect('this');
			}
		}
        

    }


	public function handleDeleteEquip($id_room_equipment)
    {

		$this->database->table("room_equipment")->where("id_room_equipment", $id_room_equipment)->delete();

        $this->template->success_notify = true;
        if ($this->isAjax()) {
            $this->redrawControl('content_snipet');
        } else {
			$this->redirect('this');
		}
    }

	public function handleDeleteEquipRoom($room_equipment,$id_room)
    {
		$data = $this->database->table("room_equipment")->where("id_room", $id_room)->where("room_equipment", $room_equipment)
		->update([
			'id_room' => NULL,
		]);


        $this->template->success_notify = true;
        if ($this->isAjax()) {
            $this->redrawControl('content_snipet');
        } else {
			$this->redirect('this');
		}
    }

    public function handleConfirmDeleteRoom($id_room)
    {
    	$this->template->id_room = $id_room;
    	$this->template->show_modal = true;
    	$this->redrawControl('show_modal_snippet');
    }

	public function handleDeleteRoom($id_room)
    {
        $this->database->table("room")->where("id_room", $id_room)->delete();
        $this->template->success_notify = true;
        if ($this->isAjax()) {
            $this->redrawControl('content_snipet');
        } else {
            $this->redirect('this');
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

	public function renderRoomsEquipment($id_room)
	{
		$data = $this->database->query("SELECT * FROM room NATURAL JOIN room_equipment WHERE id_room = ?",  $id_room)->fetchAll();

		$this->template->equip = $data;
		$this->template->id = $id_room;
		$this->actual_room = $id_room;

	}

	
	public function renderAddEquipment($id_room)
	{
		$this->actual_room = $id_room;
		$this->template->actual_room = $id_room;
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
			->setRequired("Tohle pole je povinné");

		$form->addText('room_type', '')
			->setHtmlAttribute('class', 'form-control')
			->setRequired("Tohle pole je povinné");

		$form->addInteger('room_capacity', '')
			->setHtmlAttribute('class', 'form-control')
			->setRequired("Tohle pole je povinné");

		$tmp = $this->database->query("SELECT * FROM room_address")->fetchAll();

		$address = array();
		foreach ($tmp as $row) {
			$address[$row->id_room_address]= $row->room_address;
		}

		$form->addSelect('room_Adres', '', $address)
			->setHtmlAttribute('class', 'form-control')
			->setRequired("Tohle pole je povinné");

		$form->addSubmit('submit', 'Vytvořit místnost')
			->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

		$form->onSuccess[] = [$this, 'createRoomSubmit'];
		return $form;
	}

	public function createRoomSubmit(Form $form)
	{
		$values = $form->getValues();

		$data = $this->database->table("room")
			->insert([
				'id_room' => $values->room_id,
				'room_type' => $values->room_type,
				'room_capacity' => $values->room_capacity,
				'id_room_address' => $values->room_Adres,
			]);

		$this->redirect("Chief:rooms");
	}



	public function createComponentCreateEquip()
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
	public function renderChangeAdres($id_room_address)
	{
		$this->current_Adres = $this->database->table("room_address")->where("id_room_address", $id_room_address)->fetch();
	}

	public function renderChangeEquipment($id_room_equipment)
	{
		$this->current_Equip = $this->database->table("room_equipment")->where("id_room_equipment", $id_room_equipment)->fetch();
	}

	public function renderManageRoom($id_room)
	{
		if($id_room == null)
		{
			$this->redirect("Homepage:");
		}
		$this->current_room = $this->database->table("room")->where("id_room", $id_room)->fetch();
	}

	public function createComponentUpdateRoom()
	{

		$form = new Form;

        $form->addHidden('id_room', '')
			->setDefaultValue($this->current_room["id_room"]);

		$form->addText('room_id', '')
			->setHtmlAttribute('class', 'form-control')
			->setRequired("Tohle pole je povinné")
			->setDefaultValue($this->current_room["id_room"]);

		$form->addText('room_type', '')
			->setHtmlAttribute('class', 'form-control')
			->setRequired("Tohle pole je povinné")
			->setDefaultValue($this->current_room["room_type"]);

		$form->addInteger('room_capacity', '')
			->setHtmlAttribute('class', 'form-control')
			->setRequired("Tohle pole je povinné")
			->setDefaultValue($this->current_room["room_capacity"]);

		$tmp = $this->database->query("SELECT * FROM room_address")->fetchAll();

		$address = array();
		foreach ($tmp as $row) {
			$address[$row->id_room_address]= $row->room_address;
		}

		$form->addSelect('room_Adres', '', $address)
			->setHtmlAttribute('class', 'form-control')
			->setRequired("Tohle pole je povinné")
			->setDefaultValue($this->current_room["id_room_address"]);

		$form->addSubmit('submit', 'Upravit místnost')
			->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

		$form->onSuccess[] = [$this, 'updateRoomSubmit'];
		return $form;
	}



	public function updateRoomSubmit(Form $form)
	{
		$values = $form->getValues();

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

		$tmp = $this->database->query("SELECT * FROM room_equipment WHERE id_room is NULL")->fetchAll();

		$address = array();
		foreach ($tmp as $row) {
			$address[$row->id_room_equipment]= $row->room_equipment;
		}

		$form->addHidden('id_equip', '')
			->setDefaultValue($this->actual_room);

		$form->addSelect('room_Equip', '', $address)
			->setHtmlAttribute('class', 'form-control')
			->setRequired("Tohle pole je povinné");

		$form->addSubmit('submit', 'Přidat vybavení')
			->setHtmlAttribute('class', 'btn btn-block btn-primary');

		$form->onSuccess[] = [$this, 'AddEquipSubmit'];
		return $form;
	}

	public function AddEquipSubmit(Form $form)
	{
		$values = $form->getValues();

		$data = $this->database->table("room_equipment")->where("id_room_equipment", $values->room_Equip)
			->update([
				'id_room' => $values->id_equip,
			]);

		$this->template->success_notify = true;
		$this->redirect("Chief:addEquipment",$values->id_equip);
	}

	function handleDelete($id,$id2) {

		$data = $this->database->table("room_equipment")->where("id_room_equipment", $id)->where("id_room", $id2)
		->update([
			'id_room' => NULL,
		]);
		
		$this->redirect("Chief:roomsEquipment",$id2);
	}
	
}
