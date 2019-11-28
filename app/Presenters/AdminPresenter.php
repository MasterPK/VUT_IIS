<?php

declare(strict_types=1);

namespace App\Presenters;

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
    public function renderEdituser($id)
    {
        $this->userInfo = $this->mainModel->getUserDetail($id);
    }

    public function renderAdduser($id)
    { }

    public function createComponentEditUser()
    {
        $form = new Form;

        $form->addHidden('id_user', '')
            ->setRequired()
            ->setDefaultValue($this->userInfo["id_user"]);

        $form->addText('id_user_show', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setDisabled()
            ->setDefaultValue($this->userInfo["id_user"]);

        $form->addText('email', 'Email:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($this->userInfo["email"]);

        $form->addText('first_name', 'Křestní jméno:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($this->userInfo["first_name"]);

        $form->addText('surname', 'Příjmení:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($this->userInfo["surname"]);

        $form->addText('phone', 'Telefonní číslo:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($this->userInfo["phone"]);

        $form->addSelect('rank', '', $this->ranks)
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($this->userInfo["rank"]);

        $form->addSelect('active', '', $this->activeStatus)
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
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
            ->setRequired()
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

        $form->addText('email', 'Email:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired();

        $form->addText('first_name', 'Křestní jméno:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired();

        $form->addText('surname', 'Příjmení:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired();

        $form->addText('phone', 'Telefonní číslo:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired();

        $form->addSelect('rank', '', $this->ranks)
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired();

        $form->addSelect('active', '', $this->activeStatus)
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired();

        $form->addPassword('password', 'Heslo:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Zadejte, prosím, heslo');

        $form->addPassword('passwordCheck', 'Heslo znovu:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Zadejte, prosím, heslo pro kontrolu');


        $form->addSubmit('submit', 'Potvrdit')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary');

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
            try{
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
            }
            catch()
            {
                $this->template->duplicate_notify = true;
            }
           
            if ($this->isAjax()) {
                $this->redrawControl("content_snippet");
            }
        }
    }
}
