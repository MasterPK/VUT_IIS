<?php

declare(strict_types=1);

namespace App\Presenters;
use Ublaboo;
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
		$grid->setDataSource($this->database->table('course'));

		$grid->addColumnText('id_course', 'Zkratka kurzu')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('course_name', 'Jméno kurzu')
		->setSortable()
		->setFilterText();
		

		$grid->addColumnText('course_type', 'Typ kurzu')
		->setReplacement([
			'P' => 'Povinný',
			'V' => 'Volitelný'
		])
		->setSortable();

		$grid->addFilterSelect('course_type', 'Typ kurzu:', ["P" => 'Povinný', "V" => 'Volitelný']);
		
		$grid->addColumnText('course_price', 'Cena kurzu')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('tags', 'Štítky')
		->setSortable()
		->setFilterText();

		$grid->addAction("select","Detail", 'Homepage:showcourse')
		->setIcon("fas fa-info-circle");

		$grid->setTranslator($this->dataGridTranslator);

	
		return $grid;
	}


	public function renderShowcourse($id_course): void
	{
		$this->visitorModel->renderShowcourse($this, $id_course);
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
