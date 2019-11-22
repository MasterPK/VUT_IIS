<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
	private $database;
	public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }


    public function startUp()
	{
		parent::startup();

		$this->getUser()->isLoggedIn() ? "":$this->redirect("Login:");

		$data=$this->database->table("users")
		->where("id=?",$this->user->identity->id)
		->fetch();

		/*$userData=new Nette\Security\Identity ($this->user->identity->ID,$this->user->identity->rank,$data);

		
		if($userData!=$this->user->identity)
		{
			foreach($data as $key => $item)
			{
				$this->user->identity->$key = $item;
			}
		}*/

	}


    public function renderDefault(): void
    {
        

        
    }
    
}
