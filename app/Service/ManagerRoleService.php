<?php


namespace App\Service;


use App\Constants\Constants;
use App\Exception\Admin\QueryException;
use App\Model\ManagerRole;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

class ManagerRoleService extends Service
{
    /**
     * @Inject()
     * @var ManagerRole
     */
    protected $managerRoleModel;

    /**
     * 角色管理list
     * @param int $user_id
     * @param string $roleName
     * @param int $pageSize
     * @param int $currPage
     * @return array
     */
    public function getRoleList(int $user_id, string $roleName, int $pageSize = 10, int $currPage = 1): array
    {
        $totalCount = $this->managerRoleModel->getTotalCount($user_id, $roleName);

        if ($totalCount > 0) {
            $totalPage = ceil($totalCount / $pageSize);
        } else {
            $totalPage = 0;
        }

        if ($currPage <= 0 || $currPage > $totalPage) {
            $currPage = 1;
        }

        $startCount = ($currPage - 1) * $pageSize;

        $where = " 1=1 ";
        if ($user_id != 1) {
            $where .= " and a.create_user_id = " . $user_id;
        }

        if (!empty($roleName)) {
            $where .= " and a.role_name like '%" . $roleName . "%'";
        }

        $sysRoles = Db::select("SELECT * FROM manager_role a where " . $where . " order by a.role_id desc limit " . $startCount . "," . $pageSize);

        return [
            'totalCount' => $totalCount,
            'pageSize' => $pageSize,
            'totalPage' => $totalPage,
            'currPage' => $currPage,
            'list' => $sysRoles
        ];
    }

    /**
     * 获取角色信息
     * @param $role_id
     * @return array
     */
    public function getRoleInfoById($role_id): array
    {
        try {
            $datas = Db::select("SELECT * FROM manager_role_menu where role_id = " . $role_id . ";");
            $menu_ids = array_column($datas, 'menu_id');
            $menu_ids = array_unique($menu_ids);

            $model = $this->managerRoleModel->first($role_id);
            $model->menuIdList = $menu_ids;
            return $model->toArray();

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 保存角色
     * @param int $userId
     * @param string $roleName
     * @param $remark
     * @param array $menuIdList
     * @param string $flag
     * @param int $roleId
     * @return bool|null
     */
    public function saveRole(int $userId, string $roleName, $remark, array $menuIdList, string $flag = 'add', $roleId = 0): ?bool
    {
        if ($userId != Constants::SYS_ADMIN_ID) {
            $role_ids = Db::table('manager_user_role')->where("user_id", $userId)->pluck('role_id');
            $role_ids = $role_ids->toArray();
            $datas = Db::select("SELECT * FROM manager_role_menu where role_id in (" . implode(',', $role_ids) . ");");
        } else {
            $datas = Db::select('SELECT * FROM manager_menu;');
        }
        $menu_ids = array_column($datas, 'menu_id');
        $menu_ids = array_unique($menu_ids);

        $menu_diffs = array_diff($menu_ids, $menuIdList);
        $menu_diffs = array_values($menu_diffs); //重建数组下标0开始

        // 保存的权限大于当前用户的权限就抛出异常
        if (!empty($menu_diffs) && in_array($menu_diffs[0], $menuIdList)) {
            throw new QueryException($this->translator->trans('messages.user_invalid'));
        }

        if ($flag == 'add') { // 新增角色
            Db::beginTransaction();
            try {
                $id = Db::table('manager_role')->insertGetId(
                    ['role_name' => $roleName, 'remark' => $remark, 'create_user_id' => $userId, 'create_time' => date("Y-m-d h:i:s")]
                );
                $role_menus = [];
                foreach ($menuIdList as $value) {
                    $role_menus[] = ['role_id' => $id, 'menu_id' => $value];
                }
                Db::table('manager_role_menu')->insert($role_menus);
                Db::commit();
                return true;

            } catch (\Throwable $ex) {
                Db::rollBack();
                return false;
            }

        } else { // 更新角色

            Db::beginTransaction();
            try {

                Db::table('manager_role')->where('role_id', $roleId)->update(['role_name' => $roleName, 'remark' => $remark]);

                if ((!empty($menu_diffs) && !in_array($menu_diffs[0], $menu_ids))) {
                    throw new QueryException($this->translator->trans('messages.user_invalid'));
                }

                // 获取当前角色的menu_id
                $currentMenuIds = Db::table('manager_role_menu')->where("role_id", $roleId)->pluck('menu_id');
                $currentMenuIds = $currentMenuIds->toArray();

                // 对比当前和提交的菜单的差集
                if (json_encode($currentMenuIds) == json_encode($menuIdList)) {
                    Db::commit();
                    return true;
                }

                Db::table('manager_role_menu')->where('role_id', $roleId)->delete();
                $role_menus = [];
                foreach ($menuIdList as $value) {
                    $role_menus[] = ['role_id' => $roleId, 'menu_id' => $value];
                }

                Db::table('manager_role_menu')->insert($role_menus);
                Db::commit();
                return true;

            } catch (\Throwable $ex) {
                Db::rollBack();
                return false;
            }
        }
    }

    /**
     * 删除角色
     * @param array $params
     * @param $userId
     * @return bool
     */
    public function deleteRole(array $params, $userId)
    {

        Db::beginTransaction();
        try {
            if ($userId == Constants::SYS_ADMIN_ID) {

                Db::table('manager_role')->whereIn("role_id", $params)->delete();
                Db::table('manager_role_menu')->whereIn("role_id", $params)->delete();

            } else {
                $role_ids = Db::table('manager_role')->whereIn("role_id", $params)->where("create_user_id", $userId)->pluck("role_id");

                Db::table('manager_role')->whereIn("role_id", $role_ids)->where("create_user_id", $userId)->delete();
                Db::table('manager_role_menu')->whereIn("role_id", $role_ids)->delete();
            }

            Db::commit();
            return true;

        } catch (\Throwable $ex) {
            Db::rollBack();
            return false;
        }
    }

}
