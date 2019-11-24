<?php

namespace App\Model;


use Nette;

class StartUp
{
    private $database;
    public function __construct(Nette\Database\Context $database, Nette\Security\Passwords $passwords)
    {
        $this->database = $database;
    }

    public function mainStartUp($presenter)
    {
        if ($presenter->getUser()->isLoggedIn()) 
		{
			
			$data = $this->database->table("user")
				->where("id_user=?", $presenter->user->identity->id)
				->fetch();

			$userData=new Nette\Security\Identity ($presenter->user->identity->id,$presenter->user->identity->rank,$data);

		
			if($userData!=$presenter->user->identity)
			{
				foreach($data as $key => $item)
				{
					$presenter->user->identity->$key = $item;
				}
            }
            $presenter->template->username=$presenter->user->identity->data->first_name . " " . $presenter->user->identity->data->surname;

			$presenter->template->rank=$data->rank;
			switch($data->rank)
			{
				case 1: $presenter->template->rank_msg="Student";break;
				case 2: $presenter->template->rank_msg="Lektor";break;
				case 3: $presenter->template->rank_msg="Garant";break;
				case 4: $presenter->template->rank_msg="Vedoucí";break;
				case 5: $presenter->template->rank_msg="Administrátor";break;
			}
		} 
		else 
		{
			$presenter->template->rank=0;
			$presenter->template->rank_msg = "Neregistrovaný návštěvník";
		}
    }
}
