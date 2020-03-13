<?php

namespace App\Http\Controllers;

use App\Models\ErrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller as BaseController;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;
use Symfony\Component\HttpKernel\Exception\HttpException as HException;

abstract class Controller extends BaseController
{
    use ProvidesConvenienceMethods;

    /**
     * @var array 自定义字段名称（用于验证失败时提示）
     */
    protected $customAttributes = [];

    /**
     * 字段校验
     *
     * @param Request $request
     * @param array $rules
     * @param array $customAttributes
     * @param array $messages
     * @return array
     * @throws ValidationException
     */
    public function verify(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $customAttributes = array_merge($this->customAttributes, $customAttributes);

        return $this->validate($request, $rules, $messages, $customAttributes);
    }

    /**
     * 抛出字段校验异常
     *
     * @param string $key
     * @param string $msg
     * @return void
     * @throws ValidationException
     */
    protected function verifyError(string $key, string $msg)
    {
        $validator = Validator::make([], []);
        $response = response()->json([$key => [$msg]], ErrCode::VERIFICATION_FAILED);

        throw new ValidationException($validator, $response);
    }

    /**
     * 错误返回
     *
     * @param string $msg
     * @param int $code
     * @throws HException
     */
    protected function error(string $msg, int $code = ErrCode::SERVER_ERROR)
    {
        throw new HException($code, $msg);
    }

}
