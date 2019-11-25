<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		$router->addRoute('request/<id>', 'Request:request');
		$router->addRoute('lector', 'Student:lector');
		$router->addRoute('create', 'Student:create');
		$router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
		$router->addRoute('login', 'Login:login');
		$router->addRoute('logout', 'Login:logout');
		
		return $router;
	}
}
