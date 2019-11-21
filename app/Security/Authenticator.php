<?php

namespace App\Security;


use Nette;
use Nette\Security as NS;

class Authenticator implements Nette\Security\IAuthenticator
{
    /** @var Nette\Security\Passwords @inject */
	private $passwords;

    /** @var Nette\Database\Context @inject */
	private $database;

    public function authenticate(array $credentials):Nette\Security\IIdentity
    {
        [$username, $password] = $credentials;
        $row = $this->database->table('users')
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
