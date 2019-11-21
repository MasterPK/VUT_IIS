<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class LoginPresenter extends Nette\Application\UI\Presenter
{
    public function renderDefault(): void
    {
        $this->flashMessage('LoginPresenter');

        
    }

    protected function createComponentLoginForm(): UI\Form
    {
        $form = new UI\Form;
        $form->addText('email', 'Email:');
        $form->addPassword('password', 'Heslo:');
        $form->addSubmit('login', 'Registrovat');
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
