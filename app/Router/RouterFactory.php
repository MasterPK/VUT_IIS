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
		$router->addRoute('lector', 'Lector:lector');
		$router->addRoute('create', 'Garant:create');
		//$router->addRoute('login', 'Login:login');
		//$router->addRoute('logout', 'Login:logout');
		$router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}
