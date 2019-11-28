<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;


class AdminPresenter extends Nette\Application\UI\Presenter 
{
	/** @var \App\Model\StartUp @inject */
    public $startup;

	/** @var Nette\Database\Context @inject */
    public $database;
    
    /** @var \App\Model\ChiefModel @inject */
	public $chiefModel;

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
		if(!$this->startup->roleCheck($this,5))
		{
			$this->redirect("Homepage:default");
		}
    }

    public function renderUserManagement()
    {

    }

    public function createComponentDataGrid()
    {
        $grid = new DataGrid($this, "datagrid");

		$grid->setDataSource($this->database->table("user"));
        $grid->addColumnText('id_user', 'ID');
        
    }
}