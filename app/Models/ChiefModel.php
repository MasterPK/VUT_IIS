<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;

class ChiefModel
{
    private $database;
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

}