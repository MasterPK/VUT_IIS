<?php

namespace App\Security;


use Nette;
use Nette\Security as NS;

class Authenticator implements Nette\Security\IAuthenticator
{
    private $database;
    public function __construct(Nette\Database\Context $database, Nette\Security\Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    public function authenticate(array $credentials):Nette\Security\IIdentity
    {
        [$username, $password] = $credentials;
        $row = $this->database->table('user')
        ->where('email', $username)->fetch();

        if (!$row) {
            throw new NS\AuthenticationException('Uživatel nenalezen!');
        }

        if (!$this->passwords->verify($password, $row->password)) {
            throw new NS\AuthenticationException('Neplatné heslo!');
        }

        return new NS\Identity($row->id,$row->rank, $row);
    }
}
