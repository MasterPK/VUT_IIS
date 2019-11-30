<?php

declare(strict_types=1);

namespace App\Presenters;

use Ublaboo;
use Ublaboo\DataGrid\DataGrid;
use Tracy\Debugger;
use Nette;
use Nette\Application\UI\Form;

class AdminPresenter extends Nette\Application\UI\Presenter
{
    /** @var \App\Model\StartUp @inject */
    public $startup;

    /** @var \App\Model\MainModel @inject */
    public $mainModel;

    private $database;

    private $ranks = [
        '1' => 'Student',
        '2' => 'Lektor',
        '3'  => 'Garant',
        '4' => 'Vedoucí',
        '5'  => 'Administrátor'
    ];

    private $activeStatus = [
        '0' => 'Neaktivní',
        '1' => 'Aktivní'
    ];

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


    public function beforeRender()
    {
        $this->startup->mainStartUp($this);
        if (!$this->startup->roleCheck($this, 5)) {
            $this->redirect("Homepage:default");
        }
    }

    public function renderUsersManagement()
    {
        $this->template->allUsers = $this->mainModel->getAllUsers();
    }

    private $userInfo;
    public function renderEdituser($id_user)
    {
        $this->userInfo = $this->mainModel->getUserDetail($id_user);
    }

    public function renderAdduser($id)
    { }

    public function createComponentEditUser()
    {
        $form = new Form;

        $form->addHidden('id_user', '')
            ->setRequired("Tohle pole je povinné.")
            ->setDefaultValue($this->userInfo["id_user"]);

        $form->addText('id_user_show', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setDisabled("Tohle pole je povinné.")
            ->setDefaultValue($this->userInfo["id_user"]);

        $form->addText('email', 'Email:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired("Tohle pole je povinné.")
            ->setDefaultValue($this->userInfo["email"]);

        $form->addText('first_name', 'Křestní jméno:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired("Tohle pole je povinné.")
            ->setDefaultValue($this->userInfo["first_name"]);

        $form->addText('surname', 'Příjmení:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired("Tohle pole je povinné.")
            ->setDefaultValue($this->userInfo["surname"]);

        $form->addText('phone', 'Telefonní číslo:')
            ->setHtmlAttribute('class', 'form-control')
            ->setDefaultValue($this->userInfo["phone"]);

        $form->addSelect('rank', '', $this->ranks)
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired("Tohle pole je povinné.")
            ->setDefaultValue($this->userInfo["rank"]);

        $form->addSelect('active', '', $this->activeStatus)
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired("Tohle pole je povinné.")
            ->setValue($this->userInfo["active"]);

        $form->addSubmit('submit', 'Potvrdit')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

        $form->onSuccess[] = [$this, 'editUserSubmit'];
        return $form;
    }

    public function editUserSubmit(Form $form)
    {
        $values = $form->getValues();

        $data = $this->database->table("user")->where("id_user", $values->id_user)
            ->update([
                'email' => $values->email,
                'first_name' => $values->first_name,
                'surname' => $values->surname,
                'phone' => $values->phone,
                'rank' => $values->rank,
                'active' => $values->active,
            ]);

        $this->template->success_notify = true;
        if ($this->isAjax()) {
            $this->redrawControl("notify");
        }
    }

    protected function createComponentEditPassword()
    {
        $form = new Form;

        $form->addHidden('id_user', '')
            ->setDefaultValue($this->userInfo["id_user"]);

        $form->addPassword('password', 'Heslo:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Zadejte, prosím, heslo');

        $form->addPassword('passwordCheck', 'Heslo znovu:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Zadejte, prosím, heslo pro kontrolu');

        $form->addSubmit('submit', 'Potvrdit')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

        $form->onSuccess[] = [$this, 'editPasswordSubmit'];
        return $form;
    }

    public function editPasswordSubmit(Form $form)
    {
        $values = $form->getValues();

        if ($values->password != $values->passwordCheck) {
            $this->template->password_notify = true;
            if ($this->isAjax()) {
                $this->redrawControl("notify");
            }
        } else {
            $data = $this->database->table("user")->where("id_user", $values->id_user)
                ->update([
                    'password' => password_hash($values->password, PASSWORD_BCRYPT)
                ]);

            if ($data == 1) {
                $this->template->success_notify = true;
                if ($this->isAjax()) {
                    $this->redrawControl("content_snippet");
                }
            } else {
                $this->template->error_notify = true;
                if ($this->isAjax()) {
                    $this->redrawControl("notify");
                }
            }
        }
    }

    public function createComponentAddUser()
    {
        $form = new Form;

        $form->addEmail('email', 'Email:')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::EMAIL, 'Email není platný.')
            ->setRequired("Tohle pole je povinné.");

        $form->addText('first_name', 'Křestní jméno:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired("Tohle pole je povinné.");

        $form->addText('surname', 'Příjmení:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired("Tohle pole je povinné.");

        $form->addText('phone', 'Telefonní číslo:')
            ->setHtmlAttribute('class', 'form-control');

        $form->addSelect('rank', '', $this->ranks)
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired("Tohle pole je povinné.");

        $form->addSelect('active', '', $this->activeStatus)
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired("Tohle pole je povinné.");

        $form->addPassword('password', 'Heslo:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Zadejte, prosím, heslo');

        $form->addPassword('passwordCheck', 'Heslo znovu:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Zadejte, prosím, heslo pro kontrolu');


        $form->addSubmit('submit', 'Potvrdit')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

        $form->onSuccess[] = [$this, 'addUserSubmit'];
        return $form;
    }

    public function addUserSubmit(Form $form)
    {
        $values = $form->getValues();

        if ($values->password != $values->passwordCheck) {
            $this->template->password_notify = true;
            if ($this->isAjax()) {
                $this->redrawControl("notify");
            }
        } else {
            try {
                $this->database->table("user")
                    ->insert([
                        'email' => $values->email,
                        'first_name' => $values->first_name,
                        'surname' => $values->surname,
                        'phone' => $values->phone,
                        'active' => $values->active,
                        'rank' => $values->rank,
                        'password' => password_hash($values->password, PASSWORD_BCRYPT)
                    ]);
                $this->template->success_notify = true;
                if ($this->isAjax()) {
                    $form->setValues([], TRUE);
                    $this->redrawControl("content_snippet");
                }
            } catch (Nette\Database\UniqueConstraintViolationException $e) {
                $this->template->duplicate_notify = true;
                if ($this->isAjax()) {
                    $this->redrawControl("content_snippet");
                }
            }
        }
    }

    public function createComponentUserMng($name)
    {
        $grid = new DataGrid($this, $name);

        $grid->setPrimaryKey('id_user');
        $grid->setDataSource($this->database->table("user"));





        $grid->addColumnText('id_user', 'ID')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('email', 'Email')
            ->setSortable()
            ->setFilterText();


        $grid->addColumnText('first_name', 'Křestní jméno')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('surname', 'Příjmení')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('phone', 'Telefonní číslo')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('rank', 'Hodnost')
            ->setSortable()
            ->setReplacement([
                '1' => 'Student',
                '2' => 'Lektor',
                '3' => 'Garant',
                '4' => 'Vedoucí',
                '5' => 'Administrátor'
            ])
            ->setFilterSelect([
                '' => "Vše",
                '1' => 'Student',
                '2' => 'Lektor',
                '3' => 'Garant',
                '4' => 'Vedoucí',
                '5' => 'Administrátor'
            ]);

        $grid->addColumnText('active', 'Aktivní účet?')
            ->setSortable()
            ->setReplacement([
                '0' => 'Neaktivní',
                '1' => 'Aktivní'
            ])
            ->setFilterSelect([
                '' => "Vše",
                '0' => 'Neaktivní',
                '1' => 'Aktivní'
            ]);
        /*
        $grid->addInlineEdit()
            ->onControlAdd[] = function (Nette\Forms\Container $container): void {
            $container->addText('email', '');
            $container->addText('first_name', '');
            $container->addText('surname', '');
            $container->addText('phone', '');
            $container->addSelect('rank', '', [
                '1' => 'Student',
                '2' => 'Lektor',
                '3' => 'Garant',
                '4' => 'Vedoucí',
                '5' => 'Administrátor'
            ]);
            $container->addSelect('active', '', [
                '0' => 'Neaktivní',
                '1' => 'Aktivní'
            ]);
        };

        $grid->getInlineEdit()->onSetDefaults[] = function (Nette\Forms\Container $container, $item): void {

            $container->setDefaults([
                'email' => $item->email,
                'first_name' => $item->first_name,
                'surname' => $item->surname,
                'phone' => $item->phone,
                'rank' => $item->rank,
                'active' => $item->active
            ]);
        };

        $grid->getInlineEdit()->onSubmit[] = function ($id, Nette\Utils\ArrayHash $values): void {
            $this->database->table("user")->where("id_user", $id)
                ->update([
                    'email' => $values->email,
                    'first_name' => $values->first_name,
                    'surname' => $values->surname,
                    'phone' => $values->phone,
                    'rank' => $values->rank,
                    'active' => $values->active,
                ]);
        };*/

        $grid->addAction("select", "", 'Admin:edituser')
            ->setIcon('edit')
            ->setClass("btn btn-xs btn-default btn-secondary");


        $grid->setTranslator($this->dataGridTranslator);


        return $grid;
    }
}
