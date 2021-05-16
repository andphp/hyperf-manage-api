<?php


namespace App\Controller\Manager;

use App\Service\ManagerConfigService;
use Hyperf\Di\Annotation\Inject;

class ConfigController extends BaseController
{

    /**
     * @Inject
     * @var ManagerConfigService
     */
    protected $managerConfigService;

    /**
     * 参数列表
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 18:33
     */
    public function getConfigList()
    {

        $paramKey = (string)$this->request->input('paramKey');
        $page = (int)$this->request->input('page');
        $limit = (int)$this->request->input('limit');

        $result = $this->managerConfigService->getConfigList($paramKey, $limit, $page);

        return $this->success([
            'page' => $result
        ]);
    }

    /**
     * 获取参数
     * @param $id
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 18:40
     */
    public function getConfigInfo($id)
    {
        $result = $this->managerConfigService->getConfigInfoById($id);
        if (is_array($result)) {
            return $this->success(['config' => $result]);
        } else {
            return $this->error($result);
        }
    }

    /**
     * 新增参数
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 18:40
     */
    public function saveConfig()
    {

        $paramKey = (string)$this->request->input('paramKey');
        $paramValue = (string)$this->request->input('paramValue');
        $remark = (string)$this->request->input('remark');

        $result = $this->managerConfigService->saveConfig($paramKey, $paramValue, $remark, 0);
        if ($result === true) {
            return $this->success();
        } else {
            return $this->error($result);
        }
    }

    /**
     * update参数
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 18:42
     */
    public function updateConfig()
    {

        $paramKey = (string)$this->request->input('paramKey');
        $paramValue = (string)$this->request->input('paramValue');
        $remark = (string)$this->request->input('remark');
        $id = (int)$this->request->input('id');

        $result = $this->managerConfigService->saveConfig($paramKey, $paramValue, $remark, $id);
        if ($result === true) {
            return $this->success();
        } else {
            return $this->error($result);
        }
    }

    /**
     * 删除参数
     * @return array
     * Author Da Xiong
     * Date 2020/7/26 18:43
     */
    public function deleteConfig()
    {

        $params = $this->request->post();
        if (!is_array($params) || empty($params)) {
            return $this->error("提交错误");
        }
        $result = $this->managerConfigService->deleteConfig($params);
        if ($result) {
            return $this->success();
        } else {
            return $this->error("删除失败");
        }
    }

}
