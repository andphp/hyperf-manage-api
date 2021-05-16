<?php
namespace App\Exception\Handler;

use App\Constants\ErrorCode;
use Hyperf\Contract\SessionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run;
use Throwable;

/**
 * Class WhoopsExceptionHandler
 * @package App\Exception\Handler
 * Author Da Xiong
 * Date 2020/7/11 11:16
 */
class WhoopsExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    public function __construct(StdoutLoggerInterface $logger, FormatterInterface $formatter)
    {
        $this->logger = $logger;
        $this->formatter = $formatter;
    }

    protected static $preference = [
        'text/html' => PrettyPageHandler::class,
        'application/json' => JsonResponseHandler::class,
        'application/xml' => XmlResponseHandler::class,
    ];

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $whoops = new Run();
        [$handler, $contentType] = $this->negotiateHandler();

        $whoops->pushHandler($handler);
        $whoops->allowQuit(false);
        ob_start();
        $whoops->{Run::EXCEPTION_HANDLER}($throwable);
        $content = ob_get_clean();
        if(env('APP_ENV') === 'prod') {
            $contentType = 'application/json';
            $content = json_encode([
                'code' => ErrorCode::SERVER_ERROR,
                'message' => ErrorCode::getMessage(ErrorCode::PARAMS_ERROR),
                'result' => $throwable->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }else{
            $this->logger->debug($this->formatter->format($throwable));
            /** @var ServerRequestInterface $request */
            $request = Context::get(ServerRequestInterface::class);
            echo "=========getUri=========".PHP_EOL;
            var_dump($request->getUri());
        }

        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', $contentType)
            ->withBody(new SwooleStream($content));
    }

    public function isValid(Throwable $throwable): bool
    {
        return class_exists(Run::class);
    }

    private function negotiateHandler()
    {
        /** @var ServerRequestInterface $request */
        $request = Context::get(ServerRequestInterface::class);
        $accepts = $request->getHeaderLine('accept');
        foreach (self::$preference as $contentType => $handler) {
            if (Str::contains($accepts, $contentType)) {
                return [$this->setupHandler(new $handler()),  $contentType];
            }
        }
        return [new PlainTextHandler(),  'text/plain'];
    }

    private function setupHandler($handler)
    {
        if ($handler instanceof PrettyPageHandler) {
            $handler->handleUnconditionally(true);

            if (defined('BASE_PATH')) {
                $handler->setApplicationRootPath(BASE_PATH);
            }

            $request = Context::get(ServerRequestInterface::class);
            $handler->addDataTableCallback('PSR7 Query', [$request, 'getQueryParams']);
            $handler->addDataTableCallback('PSR7 Post', [$request, 'getParsedBody']);
            $handler->addDataTableCallback('PSR7 Server', [$request, 'getServerParams']);
            $handler->addDataTableCallback('PSR7 Cookie', [$request, 'getCookieParams']);
            $handler->addDataTableCallback('PSR7 File', [$request, 'getUploadedFiles']);
            $handler->addDataTableCallback('PSR7 Attribute', [$request, 'getAttributes']);

            $session = Context::get(SessionInterface::class);
            if ($session) {
                $handler->addDataTableCallback('Hyperf Session', [$session, 'all']);
            }
        }

        return $handler;
    }
}
