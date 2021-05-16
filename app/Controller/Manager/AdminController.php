<?php
namespace App\Controller\Manager;

use App\Constants\Constants;
use App\Model\ManagerUser;
use App\Service\ManagerAdminService;
use Hyperf\Di\Annotation\Inject;

class AdminController extends BaseController
{

    /**
     * @Inject()
     * @var ManagerAdminService
     */
    protected $managerAdminService;

    /**
     * @Inject()
     * @var ManagerUser
     */
    protected $manageUserModel;

    /**
     * 获取用户登录信息
     * @return array
     * Author Da Xiong
     * Date 2020/7/11 19:08
     */
    public function getInfoByLoginUserId()
    {

        $userId = $this->session->get('admin_id');

        $model = $this->managerAdminService->getUserData($userId);

        $format = $model->toArray();

        return $this->success([
            'user' => $format
        ]);
    }

    /**
     * 管理员list
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 17:13
     */
    public function getUserList()
    {
        $userId = $this->session->get('admin_id');

        $username = (string)$this->request->input('username');
        $page = (int)$this->request->input('page', 1);
        $limit = (int)$this->request->input('limit', 10);

        $result = $this->managerAdminService->getUserList($userId, $username, $limit, $page);

        return $this->success($result);
    }

    /**
     * @return array
     * Author Da Xiong
     * Date 2020/7/31 18:58
     */
    public function getUserRoleList() {
        $userId = (string)$this->request->input('userId');

        $result = $this->managerAdminService->getUserRoleList($userId);

        return $this->success($result);
    }

    /**
     * 保存管理员
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 17:25
     */
    public function saveUser()
    {

        $createUserId = $this->session->get('admin_id');

        $username = (string)$this->request->input('username');
        $mobile = $this->request->input('mobile');
        $email = (string)$this->request->input('email');
        $roleIdList = $this->request->input('roleIdList'); //组数
        $status = (int)$this->request->input('status', 1);

        $result = $this->managerAdminService->saveUser($username, $email, $mobile, $roleIdList, $status, $createUserId);

        if ($result) {
            return $this->success();
        } else {
            return $this->error("保存失败");
        }

    }

    /**
     * 更新 管理员信息
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 17:27
     */
    public function updateUser()
    {

        $currentLoginUserId = $this->session->get('admin_id');

        $username = (string)$this->request->input('username');
        $mobile = $this->request->input('mobile');
        $email = (string)$this->request->input('email');
        $roleIdList = $this->request->input('roleIdList'); //组数
        $status = (int)$this->request->input('status', 1);
        $userId = (int)$this->request->input('userId');

        $result = $this->managerAdminService->saveUser($username, $email, $mobile, $roleIdList, $status, $currentLoginUserId, $userId);

        if ($result == false && $status == 0 && ($currentLoginUserId == $userId)) {
            return $this->error("不能禁用当前登录用户");
        }

        if ($result) {
            return $this->success();
        } else {
            return $this->error("修改失败");
        }
    }

    /**
     * 删除管理员
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 17:29
     */
    public function deleteUser()
    {

        $userId = $this->session->get('admin_id');

        $params = $this->request->post();

        if (!is_array($params) || empty($params)) {
            return $this->error("提交错误");
        }

        if (in_array(Constants::SYS_ADMIN_ID, $params)) {
            return $this->error("超级管理员不能删除");
        }

        $result = $this->managerAdminService->deleteUser($params, $userId);

        if ($result) {
            return $this->success();
        } else {
            return $this->error("删除失败");
        }
    }

    /**
     * 禁用 用户
     * @return array
     * Author Da Xiong
     * Date 2020/7/31 19:39
     */
    public function destroyUser()
    {
        $userId = $this->session->get('admin_id');

        $params = $this->request->post();

        if (!is_array($params) || empty($params)) {
            return $this->error("提交错误");
        }

        if (in_array(Constants::SYS_ADMIN_ID, $params)) {
            return $this->error("超级管理员不能禁用");
        }

        $result = $this->managerAdminService->destroyUser($params, $userId);

        if ($result) {
            return $this->success();
        } else {
            return $this->error("禁止失败");
        }
    }

    /**
     * 修改密码
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 17:33
     */
    public function updatePassword()
    {

        $userId = $this->session->get('admin_id');
        $sysUser = $this->manageUserModel->first($userId);

        $params = $this->request->post();

        if (!is_array($params) || empty($params)) {
            return $this->error("提交错误");
        }

//        if(env('APP_ENV', 'env') === 'env' && Constants::SYS_ADMIN_ID == $userId){
//            return $this->error("测试环境Admin密码不允许修改");
//        }

        $format = $sysUser->toArray();

        if (!password_verify($params['password'], $format['password'])) {
            return $this->error("原密码错误");
        }

        $result = $this->managerAdminService->saveUser($format['username'], trim($params['newPassword']), $format['email'], $format['mobile'], [], "", $format['status'], null, $userId);

        if ($result) {
            return $this->success();
        } else {
            return $this->error("修改失败");
        }
    }

    /**
     * 批量修改密码
     * @return array
     * Author Da Xiong
     * Date 2020/8/1 9:15
     */
    public function batchUpdatePassword()
    {
        $userId = $this->session->get('admin_id');
        $params = $this->request->post();
        if (empty($params) || !is_array($params) ) {
            return $this->error("提交错误");
        }
        if (in_array($userId,$params)) {
            return $this->error("批量重置密码不能包含超级管理员");
        }
        $result = $this->managerAdminService->batchUpdatePassword($params);

        if ($result) {
            return $this->success();
        } else {
            return $this->error("修改失败");
        }
    }
}
