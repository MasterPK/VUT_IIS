<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI;


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

        $this->startup->mainStartUp($this,0);
    }


    public function renderDefault()
    {
        $this->getUser()->isLoggedIn() ? $this->redirect("Homepage:"):"";
    }

    public function renderLogout()
    {
        $this->getUser()->logout();
        //dodělat snippet na obnovu celé stránky
        $this->redirect("Homepage:");
    }

    protected function createComponentLoginForm(): UI\Form
    {
        $form = new UI\Form;
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

    // volá se po úspěšném odeslání formuláře
    public function loginFormSucceeded(UI\Form $form): void
    {
        $values = $form->getValues();

        try
        {
        $this->getUser()->login($values->email,$values->password);
        
        $this->redirect('Homepage:');
        }
        catch (Nette\Security\AuthenticationException $e) 
        {
            $this->template->error_login=true;
        }
    }
    
}
