<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Tracy\Debugger;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Utils\Random;

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

    public function renderRestore()
    { }

    protected function createComponentRestoreForm()
    {
        $form = new Form;
        $form->addText('email', 'Email:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Zadejte, prosím, email');

        $form->addSubmit('restore', 'Obnovit heslo')
            ->setHtmlAttribute('class', 'btn btn-block btn-primary');

        $form->onSuccess[] = [$this, 'restoreFormSucceeded'];
        return $form;
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
    public function loginFormSucceeded(Form $form): void
    {
        $values = $form->getValues();

        try {
            $this->getUser()->login($values->email, $values->password);

            $this->redirect('Homepage:');
        } catch (Nette\Security\AuthenticationException $e) {
            $this->template->error_login = $e->getMessage();
        }
    }

    public function editProfileSubmit(Form $form)
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

    public function editPasswordSubmit(Form $form): void
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

    public function restoreFormSucceeded(Form $form)
    {
        $values = $form->getValues();

        $data = $this->database->table("user")->where("email", $values->email)->fetch();

        if (!$data) {
            $this->template->error_notify = true;
            if ($this->isAjax()) {
                $this->redrawControl("notify");
            }
        } else {
            $newPwd = Random::generate(8);

            $mail = new Message;
            $mail->setFrom('Support <support@xkrehl04.g6.cz>')
                ->addTo($values->email)
                ->setSubject('Nové heslo v IS Škola')
                ->setBody("Dobrý den,\njelikož byl zaznamenán požadavek na nové heslo u emailu $values->email, tak Vám zasíláme nové heslo:\n\n$newPwd\n\nS pozdravem");


            $mailer = new SendmailMailer;
            if ($mailer->send($mail)) {
                $this->database->table("user")->where("email", $values->email)->update([
                    'password' => password_hash($newPwd, PASSWORD_BCRYPT)
                ]);
                $this->template->success_notify = true;
                if ($this->isAjax()) {
                    $this->redrawControl("body_snippet");
                }
            } else {
                if ($this->isAjax()) {
                    $this->redrawControl("notify");
                }
            }
        }
    }
}
