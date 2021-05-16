<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

Router::get('/favicon.ico', function () {
    return '';
});

/**
 * ================================ 管理后台 start ======================================
 */

Router::post('/manager/login', 'App\Controller\Manager\AuthController@login');

Router::addGroup('/manager',function (){
    require BASE_PATH . '/routes/manage.php';
},['middleware' => [App\Middleware\AdminMiddleware::class]]);

/**
 * ================================ 管理后台 end ======================================
 */