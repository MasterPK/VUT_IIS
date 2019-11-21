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

    public function authenticate(array $credentials):NS\IIdentity
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

        return new NS\Identity($row->id,$row->rank, $row);
    }
}
