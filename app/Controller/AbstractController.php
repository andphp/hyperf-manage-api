<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use App\Constants\ErrorCode;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var ResponseInterface
     */
    protected $response;


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

    /**
     * @param string $message
     * @param int $code
     * @return array
     * Author Da Xiong
     * Date 2020/7/18 16:06
     */
    protected function error($message = "error",$code = ErrorCode::SERVER_ERROR)
    {
        return [
            'code' => $code,
            'message' => ErrorCode::getMessage(ErrorCode::SERVER_ERROR),
            'result' => [
                'error' => $message
            ],
        ];
    }

    /**
     * 将下划线命名数组转换为驼峰式命名数组
     * @param $arr 原数组
     * @param $ucfirst 首字母大小写，false 小写，TRUE 大写
     */
    protected function camelCase($arr,$ucfirst = FALSE)
    {
        if (!is_array($arr) && !is_object($arr))
        {   //如果非数组原样返回
            return $arr?:"";
        }
        $temp = [];
        if(is_object($arr) && count((array)$arr) > 0) {
            $arr = (array)$arr;
        }
        if(is_array($arr)){
            foreach ($arr as $key => $value)
            {
                $key1 = self::convertUnderline($key,FALSE);
                $value1 = self::camelCase($value);
                $temp[$key1] = $value1;
            }
        }

        return $temp;
    }


    //将下划线命名转换为驼峰式命名
    protected function convertUnderline($str, $ucfirst = true)
    {
        $str = ucwords(str_replace('_', ' ', $str));
        $str = str_replace(' ', '', lcfirst($str));
        return $ucfirst ? ucfirst($str) : $str;
    }
}
