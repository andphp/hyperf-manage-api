<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: DaXiong
 * Date: 2019/12/10
 * Time: 1:53 AM
 */

namespace App\Exception\Admin;


use App\Constants\ErrorCode;

class QueryException extends AdminException
{
    public $code = ErrorCode::QUERY_ERROR;

    public $status = 520;

    public $message = "当前系统数据查询错误！";
}