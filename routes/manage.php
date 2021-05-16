<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\HttpServer\Router\Router;

Router::post('/logout', 'App\Controller\Manager\AuthController@logout'); // 退出登录

Router::addGroup('/admin', function () {
    Router::get('/info', 'App\Controller\Manager\UserController@getInfoByLoginUserId'); // 登录的用户信息 .
//    Router::get('/info/{id:\d+}', 'App\Controller\Manager\UserController@getInfoByUserId'); // 获取用户信息 .
    Router::get('/list', 'App\Controller\Manager\AdminController@getUserList'); // 管理员用户列表

});

Router::addGroup('/menu', function () {
    Router::get('/nav', 'App\Controller\Manager\MenuController@getMenuNav'); // 登录用户的菜单和权限 .
    Router::get('/list', 'App\Controller\Manager\MenuController@getMenuList'); // 获取Menu列表 .
    Router::delete('/delete', 'App\Controller\Manager\MenuController@deleteMenu'); // 删除Menu .

});

Router::addGroup('/role', function () {
    Router::get('/list', 'App\Controller\Manager\RoleController@getRoleList'); // 角色管理列表 .
    Router::get('/select', 'App\Controller\Manager\MenuController@getMenuRoleSelectList'); // 角色分配权限列表
    Router::get('/menu', 'App\Controller\Manager\RoleController@getRoleInfo'); // 获取角色信息 .
    Router::post('/save', 'App\Controller\Manager\RoleController@saveRole'); // 新增角色 .
    Router::put('/update', 'App\Controller\Manager\RoleController@updateRole'); // 更新角色 .
    Router::delete('/delete', 'App\Controller\Manager\RoleController@deleteRole'); // 删除角色 .
});

Router::addGroup('/config', function () {
    Router::get('/list', 'App\Controller\Manager\ConfigController@getConfigList'); // 参数列表 .
    Router::get('/info/{id:\d+}', 'App\Controller\Manager\ConfigController@getConfigInfo'); // 获取参数 .
    Router::post('/save', 'App\Controller\Manager\ConfigController@saveConfig'); // 新增参数 .
    Router::put('/update', 'App\Controller\Manager\ConfigController@updateConfig'); // 更新参数 .
    Router::put('/delete', 'App\Controller\Manager\ConfigController@deleteConfig'); // 删除参数 .
});
