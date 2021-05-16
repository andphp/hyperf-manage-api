<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: DaXiong
 * Date: 2019/12/9
 * Time: 2:20 AM
 */

namespace App\Exception\Admin;


use App\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;

class AdminException extends ServerException
{
    /**
     * The status code to use for the response.
     *
     * @var int
     */
    public $code = ErrorCode::ADMIN_ERROR;

    public $status = 500;

    public $message = "管理后台系统未知错误！";

}