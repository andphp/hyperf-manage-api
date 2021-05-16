<?php

declare(strict_types=1);

namespace App\Service\Instance;

use App\Constants\ErrorCode;
use App\Exception\Admin\TokenException;
use Firebase\JWT\JWT;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\Traits\StaticInstance;

class JwtInstance
{
    use StaticInstance;

    const KEY = 'AndPHP.hyperf-admin';


    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;


    /**
     * @param $user_id
     * @return string
     * Author Da Xiong
     * Date 2020/7/11 21:25
     */
    public function encode($user_id)
    {
        return JWT::encode([
            'iss' => 'szlzpt.com', //签发者 可选
            'iat' => time(), //签发时间
            'exp' => time() + config("sys_token_exp"),
            'id' => $user_id
        ], self::KEY);
    }

    /**
     * @param string $token
     * @return array
     * Author Da Xiong
     * Date 2020/7/11 21:25
     */
    public function decode(string $token)
    {
        try {
            $decoded = (array)JWT::decode($token, self::KEY, ['HS256']);
            return $decoded;

        } catch(\Firebase\JWT\SignatureInvalidException $e) {  //签名不正确
            $this->logger->error($e->getMessage());
            throw new TokenException(ErrorCode::TOKEN_INVALID, $e->getMessage());
        }catch(\Firebase\JWT\ExpiredException $e) {  // token过期
            $this->logger->error($e->getMessage());
            throw new TokenException(ErrorCode::TOKEN_INVALID, $e->getMessage());
        }catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            throw new TokenException(ErrorCode::SERVER_ERROR, $e->getMessage());
            //return $this;
        }
    }


}
