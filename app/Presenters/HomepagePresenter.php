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

	/** @var \App\Model\DataGridModel @inject */
	public $dataGridModel;

	/** @var Nette\Database\Context @inject */
	public $database;

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

		$grid->addFilterSelect('course_type', 'Typ kurzu:', [""=>"Vše","P" => 'Povinný', "V" => 'Volitelný']);
		
		$grid->addColumnText('course_price', 'Cena kurzu (Kč)')
		->setSortable()
		->setFilterText();

		$grid->addColumnText('tags', 'Štítky')
		->setSortable()
		->setFilterText();
		$user=$this->getUser()->getIdentity();

		if($this->getUser()->isLoggedIn() && $user->data["rank"]>=1)
		{
			$grid->addAction("select","", 'Student:showcourse')
			->setIcon("info")
			->setClass("btn btn-sm btn-info");
		}
		else
		{
			$grid->addAction("select","", 'Homepage:showcourse')
			->setIcon("info")
			->setClass("btn btn-sm btn-info");
		}
		
	
		$grid->setTranslator($this->dataGridModel->dataGridTranslator);

	
		return $grid;
	}


	public function renderShowcourse($id_course): void
	{
		$this->visitorModel->renderShowcourse($this, $id_course);
	}

}
