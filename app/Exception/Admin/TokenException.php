<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: DaXiong
 * Date: 2019/12/10
 * Time: 3:51 AM
 */

namespace App\Exception\Admin;

use App\Constants\ErrorCode;

class TokenException extends AdminException
{
    public $code = ErrorCode::TOKEN_INVALID;

    public $status = 401;

    public $message = "当前令牌已失效！";
}