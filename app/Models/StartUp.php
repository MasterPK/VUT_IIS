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

    public function mainStartUp(array $credentials)
    {

    }
}
