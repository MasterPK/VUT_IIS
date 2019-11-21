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
    
}
