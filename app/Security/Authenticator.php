<?php

namespace App\Security;


use Nette;
use Nette\Security as NS;

class Authenticator implements Nette\Security\IAuthenticator
{
    public $database;

    function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function authenticate(array $credentials)
    {
        list($username,$password) = $credentials;
        $row = $this->database->table('users')
        ->where('email', $username)->fetch();

        if (!$row) {
            throw new NS\AuthenticationException('Uživatel nenalezen!');
        }

        if (!NS\Passwords::verify($password, $row->heslo)) {
            throw new NS\AuthenticationException('Neplatné heslo!');
        }

        return new NS\Identity($row->ID,$row->opravneni, $row);
    }
}
