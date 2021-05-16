<?php


namespace App\Controller\Manager;

use App\Constants\ErrorCode;
use App\Service\ManagerMenuService;
use Hyperf\Di\Annotation\Inject;

class MenuController extends BaseController
{

    /**
     * @Inject
     * @var ManagerMenuService
     */
    protected $managerMenuService;
    /**
     * 用户菜单导航
     */
    public function getMenuNav()
    {

        $userId = $this->session->get('admin_id');

        [$menuList, $permissions] = $this->managerMenuService->getMenuNav($userId);

        return $this->success([
            'menuList' => $menuList,
            'permissions' => $permissions
        ]);
    }

    /**
     * 角色分配权限列表
     * Author Da Xiong
     * Date 2020/7/29 11:38
     */
    public function getMenuRoleSelectList()
    {
        $userId = $this->session->get('admin_id');

        $result = $this->managerMenuService->getMenuRoleSelectList($userId);

        return $this->success($result);
    }

    /**
     * 获取Menu列表根据用户的权限
     * @return mixed
     * Author Da Xiong
     * Date 2020/7/26 17:42
     */
    public function getMenuList()
    {

        $userId = $this->session->get('admin_id');

        $result = $this->managerMenuService->getMenuList($userId);

        return $this->success($result);
    }

    /**
     * 获取选择Menu列表
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 17:47
     */
    public function getMenuSelectList()
    {

        $result = $this->managerMenuService->getMenuSelectList();

        return $this->success($result);
    }


    /**
     * 保存Menu
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 17:53
     */
    public function saveMenu()
    {
        $userId = $this->session->get('admin_id');
        $parentId = (int)$this->request->input('parentId',0);
        $type = $this->request->input('type');
        $orderNum = $this->request->input('orderNum');
        $url = (string)$this->request->input('url');
        $perms = (string)$this->request->input('perms');
        $name = (string)$this->request->input('name');
        $icon = (string)$this->request->input('icon');
        $redirect = (string)$this->request->input('redirect');

        $params = [
            'parent_id' => $parentId,
            'name' => $name,
            'url' => $url,
            'perms' => $perms,
            'type' => $type,
            'icon' => $icon,
            'redirect' => $redirect,
            'order_num' => $orderNum
        ];

        $result = $this->managerMenuService->saveMenu($params);

        if ($result) {
            $app_name = env('APP_NAME');
            redis()->del($app_name . "_menu_nav:" . $userId);
            return $this->success();
        } else {
            return $this->error('保存失败');
        }
    }

    /**
     * 更新菜单
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 17:54
     */
    public function updateMenu()
    {

        $userId = $this->session->get('admin_id');
        $menuId = (int)$this->request->input('menuId');
        $parentId = (int)$this->request->input('parentId');
        $type = (int)$this->request->input('type');
        $orderNum = (int)$this->request->input('orderNum');
        $url = (string)$this->request->input('url');
        $perms = (string)$this->request->input('perms');
        $name = (string)$this->request->input('name');
        $icon = (string)$this->request->input('icon');
        $redirect = (string)$this->request->input('redirect');

        $params = [
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'name' => $name,
            'url' => $url,
            'perms' => $perms,
            'type' => $type,
            'icon' => $icon,
            'redirect' => $redirect,
            'order_num' => $orderNum
        ];

        $result = $this->managerMenuService->updateMenu($params);

        if ($result) {
            $app_name = env('APP_NAME');
            redis()->del($app_name . "_menu_nav:" . $userId);
            return $this->success();
        } else {
            return $this->error('更新失败');
        }
    }

    /**
     * 删除菜单
     * @param $id
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 17:56
     */
    public function deleteMenu()
    {
        $id = (int)$this->request->input('id');
        $result = $this->managerMenuService->deleteMenu($id);
        if ($result === true) {
            return $this->success();
        }
        if ($result === false) {
            return $this->error('删除失败');
        } else {
            return $this->error('存在下级菜单不能直接删除', ErrorCode::USER_OPERATION_ERROR);
        }
    }
}
