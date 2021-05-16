<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: DaXiong
 * Date: 2019/12/10
 * Time: 3:13 AM
 */

namespace App\Constants;


use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
class StatusCode extends AbstractConstants
{
    /**
     * 用户状态 正常
     * @Message("user_normal")
     */
    const USER_STATUS_NORMAL = 1;

    /**
     * 用户状态 禁用
     * @Message("user_disable")
     */
    const USER_STATUS_DISABLE = 0;
}
