<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

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

    public function renderUsersManagement()
    {
        $this->template->allUsers = $this->mainModel->getAllUsers();
    }

    private $userInfo;
    public function renderEdituser($id)
    {  
        $this->userInfo=$this->mainModel->getUserDetail($id);
        Debugger::barDump($this->userInfo, '1');
    }

    public function renderAdduser($id)
    {  
    }

    public function createComponentEditUser()
    {
        $form = new Form;
        Debugger::barDump($this->userInfo, '2');
        /*$form->addText('id_user', '')
            ->setRequired()
            ->setDisabled(true)
            ->setDefaultValue($this->userInfo->id_user);

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

        $form->addSubmit('submit', 'Potvrdit')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

        $form->onSuccess[] = [$this, 'editUserSubmit'];*/
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
            ]);

        $this->template->success_notify = true;
        if ($this->isAjax()) {
            $this->startup->mainStartUp($this);
            $this->redrawControl("body_snippet");
        }
    }

    protected function createComponentEditPassword()
    {
        $user = $this->getUser()->getIdentity();
        $form = new Form;

        $form->addHidden('id_user', '')
            ->setRequired()
            ->setDisabled(true)
            ->setDefaultValue($user->data["id_user"]);

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
                    $this->redrawControl("body_snippet");
                }
            } else {
                $this->template->error_notify = true;
                if ($this->isAjax()) {
                    $this->redrawControl("notify");
                }
            }
        }
    }


}
