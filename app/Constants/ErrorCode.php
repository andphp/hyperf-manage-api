<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("Server Error！")
     */
    const SERVER_ERROR = 500;

    /**
     * @Message("Token Has Expired! ")
     */
    const TOKEN_INVALID = 401;

    /**
     * @Message("Management Unknown Error！")
     */
    const ADMIN_ERROR = 9000;

    /**
     * @Message("Parameter Error！")
     */
    const PARAMS_ERROR = 10000;


    //=========================================用户操作错误=========================================

    /**
     * 用户操作错误
     * @Message("User operation error！")
     */
    const USER_OPERATION_ERROR = 20000;

    /**
     * 用户操作错误-用户不存在
     * @Message("User operation error！")
     */
    const USER_NOT_EXIST = 20001; // 用户不存在

    /**
     * 用户操作错误-用户未登陆
     * @Message("User operation error！")
     */
    const USER_NOT_LOGGED_IN = 20002; // 用户未登陆

    /**
     * 用户操作错误-用户名或密码错误
     * @Message("User operation error！")
     */
    const USER_ACCOUNT_ERROR = 20003; // 用户名或密码错误

    /**
     * 用户操作错误-用户账户已被禁用
     * @Message("User operation error！")
     */
    const USER_ACCOUNT_FORBIDDEN = 20004; // 用户账户已被禁用

    /**
     * 用户操作错误-用户已存在
     * @Message("User operation error！")
     */
    const USER_HAS_EXIST = 20005;// 用户已存在

    //=========================================系统业务错误=========================================

    /**
     * 业务错误
     * @Message("System business problems！")
     */
    const BUSINESS_ERROR = 30001;// 系统业务错误

    //=========================================系统内部错误=========================================

    /**
     * 系统错误
     * @Message("System inner error！")
     */
    const SYSTEM_INNER_ERROR = 40001;// 用户已存在

    //=========================================数据处理错误=========================================

    /**
     * @Message("The Current Query Error! ")
     */
    const QUERY_ERROR = 50000;

    /**
     * 数据错误
     * @Message("Data processing errorr！")
     */
    const DATA_NOT_FOUND = 50001; // 数据未找到

    /**
     * 数据错误
     * @Message("Data processing errorr！")
     */
    const DATA_IS_WRONG = 50002;// 数据有误

    /**
     * 数据错误
     * @Message("Data processing errorr！")
     */
    const DATA_ALREADY_EXISTED = 50003;// 数据已存在

    //=========================================接口调用错误=========================================

    /**
     * 接口错误
     * @Message("Interface call error！")
     */
    const INTERFACE_INNER_INVOKE_ERROR = 60001; // 系统内部接口调用异常

    /**
     * 接口错误
     * @Message("Interface call error！")
     */
    const INTERFACE_OUTER_INVOKE_ERROR = 60002;// 系统外部接口调用异常

    /**
     * 接口错误
     * @Message("Interface call error！")
     */
    const INTERFACE_FORBIDDEN = 60003;// 接口禁止访问

    /**
     * 接口错误
     * @Message("Interface call error！")
     */
    const INTERFACE_ADDRESS_INVALID = 60004;// 接口地址无效

    /**
     * 接口错误
     * @Message("Interface call error！")
     */
    const INTERFACE_REQUEST_TIMEOUT = 60005;// 接口请求超时

    /**
     * 接口错误
     * @Message("Interface call error！")
     */
    const INTERFACE_EXCEED_LOAD = 60006;// 接口负载过高

    //========================================= 权限错误=========================================

    /**
     * 权限错误
     * @Message("Permission error！")
     */
    const PERMISSION_NO_ACCESS = 70001;// 没有访问权限
}
