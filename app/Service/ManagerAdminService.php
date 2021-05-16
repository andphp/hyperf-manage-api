<?php
namespace App\Service;

use App\Constants\Constants;
use App\Constants\StatusCode;
use App\Model\ManagerUser;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

class ManagerAdminService extends Service
{
    /**
     * @Inject()
     * @var ManagerUser
     */
    protected $managerUserModel;

    public function getUserByUsername(string $username)
    {
        return $this->managerUserModel->getUserByUsername($username);
    }


    /**
     * 获取系统用户信息
     * @param $userId
     * @return mixed
     */
    public function getUserData($userId)
    {
        $model =  $this->managerUserModel->where('user_id', $userId)->first();

        $role_ids = Db::table('manager_user_role')->where("user_id", $userId)->pluck('role_id');
        $model->roleIdList = $role_ids->toArray();

        return $model;
    }

    /**
     * 管理员管理list
     * @param int $user_id
     * @param string $username
     * @param int $pageSize
     * @param int $currPage
     * @return array
     */
    public function getUserList(int $user_id, string $username, int $pageSize = 10, int $currPage = 1): array
    {
        $totalCount = $this->managerUserModel->getTotalCount($user_id, $username);

        if ($totalCount > 0) {
            $totalPage = intval(ceil($totalCount / $pageSize));
        } else {
            $totalPage = 0;
        }

        if ($currPage <= 0 || $currPage > $totalPage) {
            $currPage = 1;
        }

        $startCount = ($currPage - 1) * $pageSize;
        $where = ' 1=1 ';
        if ($user_id != Constants::SYS_ADMIN_ID) {
            $where .= " and a.create_user_id = " . $user_id;
        }

        if (!empty($username)) {
            $where .= " and ( a.username like '%" . $username . "%' or a.mobile like '".$username."%')";
        }

        $sysUsers = Db::select("SELECT * FROM manager_user a where " . $where . " order by a.user_id desc limit " . $startCount . "," . $pageSize);

        return [
            'totalCount' => $totalCount,
            'pageSize' => $pageSize,
            'totalPage' => $totalPage,
            'currPage' => $currPage,
            'list' => $sysUsers
        ];
    }

    /**
     * 获取 用户角色
     * @param int $userId
     * @return array
     * Author Da Xiong
     * Date 2020/7/31 19:02
     */
    public function getUserRoleList(int $userId)
    {
        $userRoleList = Db::select("SELECT * FROM `manager_user_role` where `user_id` = ".$userId);
        return array_column($userRoleList,'role_id');
    }

    public function batchUpdatePassword(array $userId)
    {
       return Db::table('manager_user')->whereIn('user_id', $userId)->update(['password' => password_hash(Constants::DEFAULT_PASSWORD, PASSWORD_BCRYPT, ["cost" => 12])]);
    }
    /**
     * 保存用户信息
     * @param string $username
     * @param string $email
     * @param string $mobile
     * @param array $roleIdList
     * @param int $status
     * @param int $createUserId
     * @param int $updateUserId
     * @return bool
     */
    public function saveUser(string $username, string $email, string $mobile, array $roleIdList, int $status, ?int $createUserId, int $updateUserId = 0): bool
    {

        if ($updateUserId == 0) {

            // 添加管理员

            $user_id = Db::table('manager_user')->insertGetId([
                'username' => $username,
                'password' => password_hash(Constants::DEFAULT_PASSWORD, PASSWORD_BCRYPT, ["cost" => 12]),
                'email' => $email,
                'mobile' => $mobile,
                'status' => $status,
                'create_user_id' => $createUserId,
                'create_time' => date("Y-m-d h:i:s")
            ]);

            $roles = [];
            if (!empty($roleIdList) && !empty($user_id)) {
                foreach ($roleIdList as $value) {
                    $roles[] = [
                        'user_id' => $user_id,
                        'role_id' => $value
                    ];
                }
            }

            if (!empty($roles)) {
                Db::table('manager_user_role')->insert($roles);
            }

            return !empty($user_id) ? true : false;

        } else {

            // 更新管理员

            if ($createUserId == $updateUserId && $status == 0) {
                return false;
            }

            $update = [
                'username' => $username,
                'password' => password_hash(Constants::DEFAULT_PASSWORD, PASSWORD_BCRYPT, ["cost" => 12]),
                'email' => $email,
                'mobile' => $mobile,
                'status' => $status
            ];

            Db::table('manager_user')->where("user_id", $updateUserId)->update($update);

            $roles = [];
            if (!empty($roleIdList) && !empty($updateUserId)) {

                Db::table('manager_user_role')->where("user_id", $updateUserId)->delete();

                foreach ($roleIdList as $value) {
                    $roles[] = [
                        'user_id' => $updateUserId,
                        'role_id' => $value
                    ];
                }
            }

            if (!empty($roles)) {
                Db::table('manager_user_role')->insert($roles);
            }

            return true;
        }
    }


    /**
     * 管理员删除
     * @param array $params
     * @param $userId
     * @return bool|null
     */
    public function deleteUser(array $params, $userId): ?bool
    {
        Db::beginTransaction();
        try {
            if ($userId == Constants::SYS_ADMIN_ID) {

                Db::table('manager_user')->whereIn("user_id", $params)->delete();
                Db::table('manager_user_role')->whereIn("user_id", $params)->delete();

            } else {

                $user_ids = Db::table('manager_user')->whereIn("user_id", $params)->where("create_user_id", $userId)->pluck("user_id");

                Db::table('manager_user')->whereIn("user_id", $user_ids)->where("create_user_id", $userId)->delete();
                Db::table('manager_user_role')->whereIn("user_id", $user_ids)->delete();
            }


            Db::commit();
            return true;

        } catch (\Throwable $ex) {
            Db::rollBack();
            return false;
        }

    }

    /**
     * 管理员禁用
     * @param array $params
     * @param $userId
     * @return bool|null
     * Author Da Xiong
     * Date 2020/7/31 19:41
     */
    public function destroyUser(array $params, $userId): ?bool
    {
        Db::beginTransaction();
        try {
            if ($userId == Constants::SYS_ADMIN_ID) {

                Db::table('manager_user')->whereIn("user_id", $params)->update(['status' => StatusCode::USER_STATUS_DISABLE]);


            } else {

                $user_ids = Db::table('manager_user')->whereIn("user_id", $params)->where("create_user_id", $userId)->update(['status' => StatusCode::USER_STATUS_DISABLE]);

            }


            Db::commit();
            return true;

        } catch (\Throwable $ex) {
            Db::rollBack();
            return false;
        }
    }

}
