<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\Constants;
use App\Constants\ErrorCode;
use App\Kernel\Http\Response;
use App\Service\Instance\JwtInstance;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AdminMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject()
     * @var Response
     */
    private $response;

    /**
     * @var LoggerFactory;
     */
    protected $logger;

    /**
     * @Inject()
     * @var \Hyperf\Contract\SessionInterface
     */
    private $session;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
       // $request = $this->container->get(RequestInterface::class);
        $xToken = $request->getHeaderLine(Constants::TOKEN);
        if (empty($xToken)) {
            $xToken = $request->getQueryParams()['token'];
        }
        if (empty($xToken)) {
            return $this->response->error(Constants::PERMISSION_DENIED);
        }

        list ($bearer, $token) = explode(' ',$xToken);
        if (empty($token) || !$token) {
            return $this->response->error(Constants::PERMISSION_DENIED,ErrorCode::TOKEN_INVALID);
        }

        $uri = $request->getServerParams()['request_uri'];

        $urIs = explode('/', $uri);

        $perms = null;
        if (count($urIs) >= 5) { // 权限标识
            $perms = $urIs[2] . ":" . $urIs[3] . ":" . $urIs[4];
        }

        $this->logger->notice(PHP_EOL . 'TIME:' . date("Y-m-d h:i:s") . PHP_EOL . "PERMS:" . $perms . PHP_EOL . "IP:" . $request->getServerParams()["remote_addr"]);

        // 开发下默认的id为 1
        /*if (env('APP_DEBUG', false) === true) {
            JwtInstance::instance()->id = 1;
            return $handler->handle($request);
        }*/

        try {
            $decoded = JwtInstance::instance()->decode($token);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            return $this->response->error($e->getMessage(), ErrorCode::TOKEN_INVALID);
        }

        $accessUserId = $decoded['id'];

        $this->session->set('admin_id',$accessUserId);
        $allowPermissions = [];

        if ($accessUserId != 1) {

//            [$menuList, $permissions] = $this->session->getNemuNav($accessUserId);

            if (!empty($perms)) {
                // 没有访问权限
                if (!in_array($perms, $allowPermissions) && !in_array($perms, [])) {
                    return $this->response->error(Constants::PERMISSION_DENIED);
                }
            }
        }

        return $handler->handle($request);
    }
}
