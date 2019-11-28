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
    
    private $database;
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }


	public function beforeRender()
	{
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
        return $grid;
    }
}