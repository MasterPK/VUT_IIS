<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI;


final class LoginPresenter extends Nette\Application\UI\Presenter
{

    public function renderDefault()
    {
        $this->getUser()->isLoggedIn() ? $this->redirect("Homepage:"):"";
    }

    public function renderLogout()
    {
        $this->getUser()->logout();

    }

    protected function createComponentLoginForm(): UI\Form
    {
        $form = new UI\Form;
        $form->addText('email', 'Email:')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired('Zadejte, prosím, email')
        ->setHtmlAttribute('placeholder', 'Emailová adresa');

        $form->addPassword('password', 'Heslo:')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired('Zadejte, prosím, heslo')
        ->setHtmlAttribute('placeholder', 'Heslo');

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
