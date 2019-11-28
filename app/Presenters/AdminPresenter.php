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

    /** @var \App\Model\MainModel @inject */
    public $mainModel;

    private $database;
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }


    public function beforeRender()
    {
        $this->startup->mainStartUp($this);
        if (!$this->startup->roleCheck($this, 5)) {
            $this->redirect("Homepage:default");
        }
    }

    public function renderUserManagement()
    {
        $data = $this->mainModel->getAllUsers();
        switch ($data->rank) {
            case 1:
                $data->rank = "Student";
                break;
            case 2:
                $data->rank = "Lektor";
                break;
            case 3:
                $data->rank = "Garant";
                break;
            case 4:
                $data->rank = "Vedoucí";
                break;
            case 5:
                $data->rank = "Administrátor";
                break;
        }
        $this->template->allUsers = $data;
    }
}
