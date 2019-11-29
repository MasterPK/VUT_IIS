<?php

declare(strict_types=1);

namespace App\Presenters;
use Ublaboo\DataGrid\DataGrid;
use Nette;

use Nette\Application\UI;
use Nette\Utils\FileSystem;

class HomepagePresenter extends Nette\Application\UI\Presenter
{
	/** @var \App\Model\StartUp @inject */
	public $startup;

	/** @var \App\Model\VisitorModel @inject */
	public $visitorModel;

	/** @var \App\Model\MainModel @inject */
	public $mainModel;


	private $dataGridTranslator;
	private $database;
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
		$this->dataGridTranslator=new Ublaboo\DataGrid\Localization\SimpleTranslator([
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
			'ublaboo_datagrid.per_page_submit'=>"Aktualizovat",

			'Name' => 'Jméno',
			'Inserted' => 'Vloženo'
		]);

	}

	private $current_course_id;


	public function startUp()
	{
		parent::startup();

		$this->startup->mainStartUp($this);
	}


	public function renderDefault(): void
	{
	}

	public function createComponentSimpleGrid($name)
	{
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey('id_course');
		$grid->setDataSource($this->database->table('course'))
		->setSortable()
		->setFilterText();

		$grid->addColumnText('course_name', 'Jméno kurzu')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('course_type', 'Typ kurzu')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('course_price', 'Cena kurzu')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('tags', 'Štítky')
		->setSortable()
		->setFilterText();

		$grid->addAction("select","Detail", 'showcourse')
		->setClass("btn btn-primary");

		$grid->setTranslator($this->dataGridTranslator);

		$grid->addColumnText('', '');
		return $grid;
	}

	public function renderCourses($search, $filter): void
	{
		if ($search) {
			$this->template->courses = $this->mainModel->getAllCoursesByFilter($filter, $search);
		} else {
			//zobraz vsetky schvalene kurzy
			$this->template->courses = $this->mainModel->getAllApprovedCourses();
		}
	}

	public function renderShowcourse($id): void
	{
		$this->visitorModel->renderShowcourse($this, $id);
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



	public function handleOpen($id)
	{
		$get = $this->database->query("UPDATE course SET course_status = 2 WHERE id_course = ?", $id);

		if ($get->getRowCount() == 1) {
			$this->template->course_open_success = true;
		} else {
			$this->template->course_open_success = false;
		}

		if ($this->isAjax()) {
			$this->redrawControl('course_open_success_snippet');
		}
	}

	public function handleClose($id)
	{
		$get = $this->database->query("UPDATE course SET course_status = 3 WHERE id_course = ?", $id);

		if ($get->getRowCount() == 1) {
			$this->template->course_close_success = true;
		} else {
			$this->template->course_close_success = false;
		}

		if ($this->isAjax()) {
			$this->redrawControl('course_close_success_snippet');
		}
	}
}
