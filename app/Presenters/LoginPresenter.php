<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI;


final class LoginPresenter extends Nette\Application\UI\Presenter
{
    public function renderDefault(): void
    {
        $this->flashMessage('LoginPresenter');

        
    }

    protected function createComponentLoginForm(): UI\Form
    {
        $form = new UI\Form;
        $form->addText('email', 'Email:')
        ->setHtmlAttribute('class', 'form-control')
        ->setHtmlAttribute('placeholder', 'Emailová adresa');

        $form->addPassword('password', 'Heslo:')
        ->setHtmlAttribute('class', 'form-control')
        ->setHtmlAttribute('placeholder', 'Heslo');

        $form->addSubmit('login', 'Přihlásit se')
        ->setHtmlAttribute('class', 'btn btn-block btn-primary');
        
        $form->onSuccess[] = [$this, 'loginFormSucceeded'];
        return $form;
    }

    // volá se po úspěšném odeslání formuláře
    public function loginFormSucceeded(UI\Form $form, \stdClass $values): void
    {
        // ...
        $this->flashMessage('Byl jste úspěšně registrován.');
        $this->redirect('Homepage:');
    }
    
}
