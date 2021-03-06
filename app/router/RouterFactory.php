<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
//Route::$defaultFlags = Route::SECURED;
        $router[] = new Route('service/<action>[/<id>]', 'Service:default');
        $router[] = new Route('<action>[/<id>]', 'Homepage:default');



        return $router;
	}

}
