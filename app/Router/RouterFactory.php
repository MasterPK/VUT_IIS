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
		
		$pages = ['informace', 'kroky'];

		$router[] = new Route('<page>', [
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
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}
