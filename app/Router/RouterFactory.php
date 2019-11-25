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
		$router->addRoute('login[/<id>]', 'Login:login[/<id>]');
		$router->addRoute('logout[/<id>]', 'Login:logout[/<id>]');
		$router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}
