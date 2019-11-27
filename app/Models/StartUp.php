<?php

namespace App\Model;


use Nette;

class StartUp
{
    private $database;
    public function __construct(Nette\Database\Context $database)
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
            $presenter->template->username=$presenter->user->identity->data["first_name"] . " " . $presenter->user->identity->data["surname"]; 
			$presenter->template->rank=$data->rank;
			switch($data->rank)
			{
				case 1: 
                    $presenter->template->rank_msg="Student";
                    $presenter->template->rank_msg_eng="Student";
                    break;
                case 2: 
                    $presenter->template->rank_msg="Lektor";
                    $presenter->template->rank_msg_eng="Lector";
                    break;    
                case 3: 
                    $presenter->template->rank_msg="Garant";
                    $presenter->template->rank_msg_eng="Garant";
                    break;
                case 4: 
                    $presenter->template->rank_msg="Vedoucí";
                    $presenter->template->rank_msg_eng="Chief";
                    break;
                case 5: 
                    $presenter->template->rank_msg="Administrátor";
                    $presenter->template->rank_msg_eng="Admin";
                    break;
            }
    
		} 
		else
		{
			$presenter->template->rank=0;
            $presenter->template->rank_msg = "Neregistrovaný návštěvník";
            $presenter->template->rank_msg_eng = "Visitor";
     
        }
    }

    public function roleCheck($presenter,$rank)
    {
        if ($presenter->getUser()->isLoggedIn()) 
        {
            return ($presenter->user->identity->data["rank"]>=$rank);
        }
        else
        {
            if($rank==0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        
    }
}
