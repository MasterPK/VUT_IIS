<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$pages = ['login', 'logout', 'courses'];

		$router = new RouteList;
		$router->addRoute('<page>', [
		    'presenter' => 'Homepage',
		    'action' => 'page',
		    'page' => [
		        Route::FILTER_IN => function ($page) use ($pages) {
		            if (in_array($page, $pages)) {
		                return $page;
		            }

		            return null;
		        }
		    ],
		]);
		return $router;
	}
}
