<?php
namespace App\Controller\Manager;

use App\Constants\ErrorCode;
use App\Constants\StatusCode;
use App\Model\ManagerUser;
use App\Request\ManagerLoginRequest;
use App\Service\Instance\JwtInstance;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Di\Annotation\Inject;

class AuthController extends BaseController
{
    /**
     * @Inject
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @Inject()
     * @var ManagerUser
     */
    protected $manageUserModel;

    public function login(ManagerLoginRequest $request)
    {
        // 获取通过验证的数据...
        $validated = $request->validated();

        $userModel = $this->manageUserModel->getUserByUsername($validated['username']);

        if (!password_verify($validated['password'], $userModel->password)) {
            return $this->error($this->translator->trans('messages.password_error'));
        }

        if ($userModel->status != StatusCode::USER_STATUS_NORMAL) {
            $statusMsg = StatusCode::getMessage(StatusCode::USER_STATUS_DISABLE);
            return $this->error($statusMsg,ErrorCode::USER_HAS_EXIST);
        }

        $token = JwtInstance::instance()->encode($userModel->user_id);

        $data = [
            'expire' => config("sys_token_exp"),
            'token' =>  $token
        ];
        return $this->success($data);
    }

    /**
     * @return array
     * Author Da Xiong
     * Date 2020/7/11 21:28
     */
    public function logout()
    {
        $data = [
            'token' =>  ''
        ];
        return $this->success($data);
    }
}
