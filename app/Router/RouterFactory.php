<?php

declare(strict_types=1);

namespace App\Router;

use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    public static function createRouter(): RouteList
    {
        $router = new RouteList();

        $router->addRoute('admin[/<presenter>[/<action>[/<id \d+>]]]', [
            'module' => 'Admin',
            'presenter' => 'User',
            'action' => 'default',
        ]);

        $router->addRoute('[<presenter>[/<action>[/<id \d+>]]]', [
            'module' => 'Front',
            'presenter' => 'Sign',
            'action' => 'in',
        ]);

        return $router;
    }
}
