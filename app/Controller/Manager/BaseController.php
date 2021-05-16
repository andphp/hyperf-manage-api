<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: DaXiong
 * Date: 2019/12/9
 * Time: 1:03 AM
 */

namespace App\Controller\Manager;

use App\Controller\AbstractController;
use Hyperf\Contract\SessionInterface;
use Hyperf\Di\Annotation\Inject;

class BaseController extends AbstractController
{

    /**
     * @Inject()
     * @var SessionInterface
     */
    protected $session;

    /**
     * @param $data
     * @param string $message
     * @return array
     * Author Da Xiong
     * Date 2020/7/11 11:16
     */
    protected function success(array $data = [],$message = "success")
    {
        return [
            'code' => 0,
            'message' => $message,
            'result' => $this->camelCase($data),
        ];
    }
}
