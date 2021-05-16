<?php


namespace App\Service;


use App\Constants\Constants;
use App\Model\ManagerMenu;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\ApplicationContext;

class ManagerMenuService extends Service
{
    /**
     * 菜单导航,权限信息
     * @param int $user_id
     * @return array
     */
    public function getMenuNav(int $user_id): array
    {
        $container = ApplicationContext::getContainer();
        $redis = $container->get(\Redis::class);

        $app_name = env('APP_NAME');
        $cacheMenuNav = $redis->get($app_name . "_menu_nav:" . $user_id);

        if (!empty($cacheMenuNav)) {
            return json_decode($cacheMenuNav, true);
        }

        if ($user_id != Constants::SYS_ADMIN_ID) {
            $role_ids = Db::table('manager_user_role')->where("user_id", $user_id)->pluck('role_id');
            $role_ids = $role_ids->toArray();
            $datas = Db::select("SELECT * FROM manager_role_menu where role_id in (" . implode(',', $role_ids) . ");");
        } else {
            $datas = Db::select('SELECT * FROM manager_menu;');
        }
        $menu_ids = array_column($datas, 'menu_id');

        $result = $this->getUserMenusPermissions($menu_ids);

        $redis->set($app_name . "_menu_nav:" . $user_id, json_encode($result), 60); //暂时设置60秒
        return $result;
    }

    /**
     * 获取菜单和权限
     * @param $menu_ids
     * @return array
     */
    private function getUserMenusPermissions($menu_ids)
    {
        $menu_category = Db::select('SELECT * FROM manager_menu where  parent_id = 0 and type = 0 and menu_id in (' . implode(',', $menu_ids) . ') order by order_num asc;');

        $menuList = [];
        foreach ($menu_category as $key => $value) {

            $ManagerMenuModel = ManagerMenu::query()->where("menu_id", $value->menu_id)->first();

            $format = $ManagerMenuModel->toArray();

            $format['list'] = self::getUserMenuList($value->menu_id,$menu_ids);

            $menuList[] = $format;
        }

        $permissionArrs = Db::select('SELECT * FROM manager_menu where  menu_id in (' . implode(',', $menu_ids) . ') order by order_num asc;');
        $permissionArrs = array_column($permissionArrs, 'perms');

        $permissions = [];
        foreach ($permissionArrs as $perms) {
            if (!empty($perms)) {
                if (explode(',', $perms) > 0) {
                    if (!empty($permissions)) {
                        $permissions = array_merge($permissions, explode(',', $perms));
                    } else {
                        $permissions = explode(',', $perms);
                    }
                } else {
                    $permissions [] = $perms;
                }
            }
        }

        $permissions = array_unique($permissions);

        $permArrays = [];
        foreach ($permissions as $key => $val) {
            $permArrays[] = $val;
        }

        // 默认存在的权限
        $allowPermissions = [
            'manager:menu:nav',
            'manager:user:info',
            'manager:user:password'
        ];
        $permArrays = array_merge($permArrays, $allowPermissions);

        return [$menuList, $permArrays];
    }


    /**
     * Author Da Xiong
     * Date 2020/7/20 19:54
     */
    private function getUserMenuList($menu_id,$menu_ids)
    {
        $menus =ManagerMenu::query()->whereRaw('parent_id = ' . $menu_id. ' and menu_id in (' . implode(',', $menu_ids) . ') and `type` in (1,3)')->orderBy('order_num','asc')->get();
//        $menus = Db::select('SELECT * FROM manager_menu where  parent_id = ' . $menu_id. ' and menu_id in (' . implode(',', $menu_ids) . ') and type = 1 order by order_num asc;');
        $arr = [];
        $menus = $menus->toArray();
        foreach ($menus as $v) {
            $countMenu = Db::table('manager_menu')->whereRaw('parent_id = ' . $v['menu_id']. ' and menu_id in (' . implode(',', $menu_ids) . ') and `type` in (1,3)')->count();
            if($countMenu > 0) {
                $v['list'] =  self::getUserMenuList($v['menu_id'],$menu_ids);
            }
            $arr[] = $v;
        }
        return $arr;
    }

    /**
     * 获取 角色授权 菜单权限选择列表
     * @param int $user_id
     * @return array
     * Author Da Xiong
     * Date 2020/7/29 14:24
     */
    public function getMenuRoleSelectList(int $user_id): array
    {
        if ($user_id != Constants::SYS_ADMIN_ID) {
            $role_ids = Db::table('manager_user_role')->where("user_id", $user_id)->pluck('role_id');
            $role_ids = $role_ids->toArray();
            $datas = Db::select("SELECT * FROM manager_role_menu a inner join manager_menu b on  a.`menu_id` = b.`menu_id` where b.`parent_id` = 0 and a.`role_id` in (" . implode(',', $role_ids) . ");");
            $muenAll = Db::select("SELECT `menu_id` FROM manager_role_menu where role_id in (" . implode(',', $role_ids) . ");");
        } else {
            $datas = Db::select('SELECT * FROM manager_menu where `parent_id` = 0;');
            $muenAll = Db::select('SELECT `menu_id` FROM manager_menu;');
        }


        if (empty($datas) || empty($muenAll)) {
            return [];
        }
        $result = array();
        $menu_ids = array_unique(array_column($muenAll, 'menu_id'));
        foreach ($datas as $item) {
            $children = self::getMenuChildren($item->menu_id,$item->name,$menu_ids);
            if(empty($children)){
                continue;
            }
            $result[] = $children;
        }

        return $result;
    }

    public function getMenuChildren($menu_id,$name,$menu_ids)
    {
        $children = Db::select('SELECT `menu_id`,`parent_id`,`name` FROM manager_menu where `parent_id` = '.$menu_id.' and `menu_id` in ('.implode(',',$menu_ids).');');
        if(empty($children)){
            return [];
        }
        $result = array(
            'key' => $menu_id,
            'value' => $menu_id,
            'title' => $name
        );
        foreach ($children as $item) {
            $childrenArr = self::getMenuChildren($item->menu_id,$item->name,$menu_ids);
            if(!empty($childrenArr)){
                $result['children'][] = $childrenArr;
            }
        }
        return $result;
    }

    /**
     * 获取Menu列表
     * @param int $user_id
     * @return array
     */
    public function getMenuList(int $user_id): array
    {


        if ($user_id != Constants::SYS_ADMIN_ID) {
            $role_ids = Db::table('manager_user_role')->where("user_id", $user_id)->pluck('role_id');
            $role_ids = $role_ids->toArray();
            $datas = Db::select("SELECT * FROM manager_role_menu where role_id in (" . implode(',', $role_ids) . ");");
        } else {
            $datas = Db::select('SELECT * FROM manager_menu;');
        }

        if (empty($datas)) {
            return [];
        }

        $menu_ids = array_column($datas, 'menu_id');
        $menu_ids = array_unique($menu_ids);

        return Db::select("SELECT s1.*,s2.name as parentName FROM manager_menu s1 LEFT JOIN manager_menu s2 ON s1.parent_id = s2.menu_id where s1.menu_id in (" . implode(',', $menu_ids) . ") order by s1.order_num ASC ;");
    }

    /**
     * 获取顶级和一级的菜单
     * @return array
     */
    public function getMenuSelectList()
    {
        $datas = Db::select('SELECT * FROM manager_menu where `type` <> 2 order by order_num ASC ;');

        $menu_ids = array_column($datas, 'menu_id');
        $menu_ids = array_unique($menu_ids);

        $sys_menus = Db::select("SELECT s1.*,s2.name as parentName FROM manager_menu s1 LEFT JOIN manager_menu s2 ON s1.parent_id = s2.menu_id where s1.menu_id in (" . implode(',', $menu_ids) . ") order by s1.order_num ASC ;");

        $topMenu = [
            "menuId" => 0,
            "parentId" => -1,
            "parentName" => null,
            "name" => "一级菜单",
            "url" => null,
            "perms" => null,
            "type" => null,
            "icon" => null,
            "orderNum" => null,
            "open" => true,
            "list" => null
        ];

        $sys_menus [] = $topMenu;

        return $sys_menus;
    }


    /**
     * 保存菜单
     * @param array $params
     * @return int
     */
    public function saveMenu(array $params)
    {
        return Db::table('manager_menu')->insertGetId($params);
    }

    /**
     * 更新菜单
     * @param array $params
     * @return int
     */
    public function updateMenu(array $params)
    {
        return Db::table('manager_menu')->where("menu_id", $params['menu_id'])->update($params);
    }

    /**
     * 删除菜单
     * @param $id
     * @return int
     */
    public function deleteMenu($id)
    {
        $hasParent = Db::table('manager_menu')->where('parent_id', $id)->first();
        if (!empty($hasParent)) {
            return -1;
        }
        Db::beginTransaction();
        try {
            Db::table('manager_menu')->where('menu_id', $id)->delete();
            Db::table('manager_role_menu')->where('menu_id', $id)->delete();

            Db::commit();
            return true;

        } catch (\Throwable $ex) {
            Db::rollBack();
            return false;
        }
    }
}
