<?php


namespace App\Service;


use App\Model\ManagerConfig;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;


class ManagerConfigService extends Service
{
    /**
     * @Inject()
     * @var ManagerConfig
     */
    protected $managerConfigModel;

    /**
     * 返回参数管理列表
     * @param string $paramKey
     * @param int $pageSize
     * @param int $currPage
     * @return array
     */
    public function getConfigList(string $paramKey, int $pageSize = 10, int $currPage = 1): array
    {
        $totalCount = $this->managerConfigModel->getTotalCount($paramKey);

        if ($totalCount > 0) {
            $totalPage = ceil($totalCount / $pageSize);
        } else {
            $totalPage = 0;
        }

        if ($currPage <= 0 || $currPage > $totalPage) {
            $currPage = 1;
        }

        $startCount = ($currPage - 1) * $pageSize;

        $where = " 1=1 and a.status = 1 ";

        if (!empty($paramKey)) {
            $where .= " and a.param_key like '%" . $paramKey . "%' or a.remark like '%" . $paramKey . "%'";
        }

        $sysConfigs = Db::select("SELECT * FROM manager_config a where " . $where . " order by a.id desc limit " . $startCount . "," . $pageSize);


        return [
            'totalCount' => $totalCount,
            'pageSize' => $pageSize,
            'totalPage' => $totalPage,
            'currPage' => $currPage,
            'list' => $sysConfigs
        ];
    }

    /**
     * 获取参数
     * @param $id
     * @return array|string
     */
    public function getConfigInfoById($id)
    {
        try {
            $model = $this->managerConfigModel->first($id);
            return $model->toArray();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 保存参数
     * @param string $paramKey
     * @param string $paramValue
     * @param string $remark
     * @param int $id
     * @return bool|string
     */
    public function saveConfig(string $paramKey, string $paramValue, string $remark, int $id)
    {
        $data = [
            'param_key' => $paramKey,
            'param_value' => $paramValue,
            'remark' => $remark
        ];
        if (!empty($id)) {
            $data['id'] = $id;
        }
        try {
            $this->managerConfigModel->firstOrNew($data);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 删除参数
     */
    public function deleteConfig(array $params)
    {
        return Db::table('manager_config')->whereIn("id", $params)->delete();
    }


}
