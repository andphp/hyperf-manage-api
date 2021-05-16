<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: DaXiong
 * Date: 2019/12/9
 * Time: 1:03 AM
 */

namespace App\Controller\Manager;

use App\Controller\AbstractController;
use Hyperf\Contract\SessionInterface;
use Hyperf\Di\Annotation\Inject;

class BaseController extends AbstractController
{

    /**
     * @Inject()
     * @var SessionInterface
     */
    protected $session;

}
