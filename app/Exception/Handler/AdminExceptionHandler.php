<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: DaXiong
 * Date: 2019/12/9
 * Time: 2:20 AM
 */

namespace App\Exception\Handler;


use App\Constants\ErrorCode;
use App\Exception\Admin\AdminException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Throwable;

class AdminExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());

        /** @var \App\Exception\Admin\AdminException $throwable */
        $result = [
            'code' => $throwable->code,
            'message' => ErrorCode::getMessage($throwable->code),
            'result' => [
                'error' => $throwable->message
            ],
        ];
        return $response->withStatus($throwable->status)
            ->withHeader('content-type','application/json; charset=utf-8')
            ->withBody(new SwooleStream(json_encode($result)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof AdminException;
    }
}
