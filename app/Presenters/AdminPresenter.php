<?php

declare(strict_types=1);

namespace App\Presenters;


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

    /** @var \App\Model\DataGridModel @inject */
    public $dataGridModel;

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

        $grid->addAction('delete', '', 'confirm!')
            ->setIcon('trash')
            ->setTitle('Smazat')
            ->setClass('btn btn-xs btn-danger ajax');

        $all_passwords = $this->database->query("SELECT password FROM user")->fetchAll();
        $replacement = [];
        foreach($all_passwords as $password)
        {
            $replacement[$password->password] = 'SKRYTO';
        }

        $grid->addColumnText('password', 'Heslo')
            ->setReplacement($replacement);
        
        $grid->addInlineEdit()
            ->onControlAdd[] = function (Nette\Forms\Container $container): void {
            $container->addText('email', '')->addRule(Form::EMAIL, 'Email není platný.');
            $container->addText('first_name', '')->setRequired("Tohle pole je povinné");
            $container->addText('surname', '')->setRequired("Tohle pole je povinné");
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
            $container->addText('password', '');
        };

        $grid->getInlineEdit()->onSetDefaults[] = function (Nette\Forms\Container $container, $item): void {

            $container->setDefaults([
                'email' => $item->email,
                'first_name' => $item->first_name,
                'surname' => $item->surname,
                'phone' => $item->phone,
                'rank' => $item->rank,
                'active' => $item->active,
                'password' => '',
            ]);
        };

        $grid->getInlineEdit()->onSubmit[] = function ($id, Nette\Utils\ArrayHash $values): void {

            $result = $this->database->table("user")->where("id_user", $id)
                ->update([
                    'email' => $values->email,
                    'first_name' => $values->first_name,
                    'surname' => $values->surname,
                    'phone' => $values->phone,
                    'rank' => $values->rank,
                    'active' => $values->active,
                ]);

            if($values->password != '')
            {
                $this->database->table("user")->where("id_user", $id)
                ->update([
                    'password' => password_hash($values->password, PASSWORD_BCRYPT)
                ]);

                if($result == 0)
                {
                    $this->template->success_password;
                    $this->redrawControl('password_snippet');
                }
            }
            else
            {
                $this->template->success_edit;
                $this->redrawControl('edit_snippet');
            }

        };

        $grid->addInlineAdd()
            ->onControlAdd[] = function(Nette\Forms\Container $container) {
                $container->addText('email', '')->addRule(Form::EMAIL, 'Email není platný.');
                $container->addText('first_name', '')->setRequired("Tohle pole je povinné");
                $container->addText('surname', '')->setRequired("Tohle pole je povinné");
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
            $container->addText('password', '')->setRequired("Tohle pole je povinné");
        };

        $grid->getInlineAdd()->onSubmit[] = function(Nette\Utils\ArrayHash $values): void {
            try 
            {
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
                if ($this->isAjax()) 
                {
                    $this->redrawControl("content_snippet");
                }
            } 
            catch (Nette\Database\UniqueConstraintViolationException $e) 
            {
                $this->template->error_notify = true;
                if ($this->isAjax()) 
                {
                    $this->redrawControl("content_snippet");
                }
            }
        };

        $grid->setTranslator($this->dataGridModel->dataGridTranslator);


        return $grid;
    }

    public function handleConfirm($id_user)
    {
        $this->template->delete_confirm = true;
        $this->template->delete_user_id = $id_user;
        $this->template->delete_user = $this->database->query("SELECT first_name, surname FROM user WHERE id_user = ?", $id_user)->fetch();
        $this->template->delete_user = $this->template->delete_user->first_name . " " . $this->template->delete_user->surname;
        $this->redrawControl('confirm_snippet');
    }

    public function handleDelete($id_user)
    {
        $this->database->table("user")->where("id_user", $id_user)->delete();
        $this->template->success_notify = true;
        if ($this->isAjax()) 
        {
            $this->redrawControl('notify');
        } 
        else 
        {
            $this->redirect('this');
        }
    }
}
