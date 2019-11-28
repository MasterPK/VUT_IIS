<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI\Form;

final class LoginPresenter extends Nette\Application\UI\Presenter
{
    /** @var \App\Model\StartUp @inject */
    public $startup;

    private $database;
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function startUp()
    {
        parent::startup();

        $this->startup->mainStartUp($this);
    }


    public function renderDefault()
    {
        $this->getUser()->isLoggedIn() ? $this->redirect("Homepage:") : "";

        $this->redirect("Login:login");
    }

    public function renderLogin($id)
    {
        $this->getUser()->isLoggedIn() ? $this->redirect("Homepage:") : "";
        if ($id == 1) {
            $this->template->error = "Byli jste odhlášeni po 5 minutách neaktivity!";
        }
    }

    public function renderLogout($id)
    {
        $this->getUser()->logout();
        if (empty($id)) {
            $this->redirect("Login:login");
        } else {
            $this->redirect("Login:login", $id);
        }
    }

    public function renderSettings()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect("Login:login");
        }
    }



    protected function createComponentLoginForm()
    {
        $form = new Form;
        $form->addText('email', 'Email:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Zadejte, prosím, email');

        $form->addPassword('password', 'Heslo:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Zadejte, prosím, heslo');

        $form->addSubmit('login', 'Přihlásit se')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary');

        $form->onSuccess[] = [$this, 'loginFormSucceeded'];
        return $form;
    }

    protected function createComponentEditProfile()
    {
        $user = $this->getUser()->getIdentity();
        $form = new Form;

        $form->addHidden('id_user', '')
            ->setRequired()
            ->setDisabled(true)
            ->setDefaultValue($user->data["id_user"]);

        $form->addText('email', 'Email:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($user->data["email"]);

        $form->addText('first_name', 'Křestní jméno:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($user->data["first_name"]);

        $form->addText('surname', 'Příjmení:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($user->data["surname"]);

        $form->addText('phone', 'Telefonní číslo:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired()
            ->setDefaultValue($user->data["phone"]);

        $form->addSubmit('submit', 'Potvrdit')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary ajax');

        $form->onSuccess[] = [$this, 'editProfileSubmit'];
        return $form;
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

    // volá se po úspěšném odeslání formuláře
    public function loginFormSucceeded(UI\Form $form): void
    {
        $values = $form->getValues();

        try {
            $this->getUser()->login($values->email, $values->password);

            $this->redirect('Homepage:');
        } catch (Nette\Security\AuthenticationException $e) {
            $this->template->error_login = true;
        }
    }

    public function editProfileSubmit(UI\Form $form)
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

    public function editPasswordSubmit(UI\Form $form): void
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
