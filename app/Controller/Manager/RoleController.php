<?php


namespace App\Controller\Manager;

use App\Service\ManagerRoleService;
use Hyperf\Di\Annotation\Inject;

class RoleController extends BaseController
{

    /**
     * @Inject()
     * @var ManagerRoleService
     */
    protected $manageRoleService;

    /**
     * 角色管理list
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 18:01
     */
    public function getRoleList()
    {
        $userId = $this->session->get('admin_id');

        $roleName = (string)$this->request->input('roleName');
        $page = (int)$this->request->input('page', 1);
        $limit = (int)$this->request->input('limit', 10);

        $result = $this->manageRoleService->getRoleList($userId, $roleName, $limit, $page);

        return $this->success($result);
    }


    /**
     * 获取角色信息
     * @param $id
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 18:13
     */
    public function getRoleInfo()
    {
        $id = (int)$this->request->input('roleId', 0);
        $result = $this->manageRoleService->getRoleInfoById($id);

        if ($result) {
            return $this->success($result);
        } else {
            return $this->error('获取失败');
        }
    }

    /**
     * 新增角色
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 18:16
     */
    public function saveRole()
    {
        $userId = $this->session->get('admin_id');

        $roleName = (string)$this->request->input('roleName');
        $remark = (string)$this->request->input('remark');
        $menuIdList = $this->request->input('menuIdList');

        $result = $this->manageRoleService->saveRole($userId, $roleName, $remark, $menuIdList);
        if ($result) {
            return $this->success();
        } else {
            return $this->error('保存失败');
        }
    }


    /**
     * 更新角色管理
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 18:19
     */
    public function updateRole()
    {

        $userId = $this->session->get('admin_id');

        $roleId = (int)$this->request->input('roleId');
        $roleName = (string)$this->request->input('roleName');
        $remark = (string)$this->request->input('remark');
        $menuIdList = $this->request->input('menuIdList');

        $result = $this->manageRoleService->saveRole($userId, $roleName, $remark, $menuIdList,'update',$roleId);

        if ($result) {
            return $this->success();
        } else {
            return $this->error('保存失败');
        }
    }

    /**
     * 删除角色
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 18:20
     */
    public function deleteRole()
    {

        $userId = $this->session->get('admin_id');

        $params = $this->request->post();

        if (!is_array($params) || empty($params)) {
            return $this->error("提交错误");
        }

        $result = $this->manageRoleService->deleteRole($params,$userId);

        if ($result) {
            return $this->success();
        } else {
            return $this->error("删除失败");
        }
    }
}
