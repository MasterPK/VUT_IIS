<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI;


final class LoginPresenter extends Nette\Application\UI\Presenter
{

    public function startUp()
    {
        parent::startup();

        if ($this->getUser()->isLoggedIn()) 
        {
            
            $data = $this->database->table("user")
                ->where("id_user=?", $this->user->identity->id)
                ->fetch();

            $userData=new Nette\Security\Identity ($this->user->identity->id,$this->user->identity->rank,$data);

        
            if($userData!=$this->user->identity)
            {
                foreach($data as $key => $item)
                {
                    $this->user->identity->$key = $item;
                }
            }
            $this->template->rank=$data->rank;
            switch($data->rank)
            {
                case 1: $this->template->rank_msg="Student";break;
                case 2: $this->template->rank_msg="Lektor";break;
                case 3: $this->template->rank_msg="Garant";break;
                case 4: $this->template->rank_msg="Vedoucí";break;
                case 5: $this->template->rank_msg="Administrátor";break;
            }
        } 
        else 
        {
            $this->template->rank=0;
            $this->template->rank_msg = "Neregistrovaný návštěvník";
        }
    }

    
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
