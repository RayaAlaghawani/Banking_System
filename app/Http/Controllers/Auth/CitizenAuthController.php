<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthadminRequest;
use App\Http\Requests\CitizenRegisterRequest;
use App\Http\Requests\EmailVerificationRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class CitizenAuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Register a new citizen and send email verification
     */
    public function login(AuthadminRequest $request )
    {
            $data = $this->userService->login($request);

            if ($data['code'] === 200) {
                return ResponseHelper::Success($data['user'], $data['message'], $data['code']);
            } else {
                return ResponseHelper::Error($data['user'], $data['message'], $data['code']);
            }}
    /**
     * Store a newly created resource in storage.
     */
    public function logout()
    {
        $data=[];
        try{
            $data=$this->userService->logout( );
            if ($data['code'] === 200) {
                return ResponseHelper::Success([],$data['message'],$data['code']);
            } else {
                return ResponseHelper::Error([], $data['message'], $data['code']);
            }
        } catch (\Throwable $e) {
            return ResponseHelper::Error(null, "Unexpected error: " . $e->getMessage(), 500);
        }}



    public function register(CitizenRegisterRequest $request)
    {
        try {
            $result = $this->userService->register($request->validated());
            return response()->json([
                'message' => 'تم التسجيل بنجاح. يرجى التحقق من بريدك الإلكتروني.',
                'user_id' => $result['user_id'],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل التسجيل. يرجى المحاولة لاحقاً.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyEmail(EmailVerificationRequest $request, int $userId)
    {
        $code = $request->input('code');
        $user = $this->userService->verifyEmail($userId, $code);

        if (!$user) {
            return response()->json([
                'message' => 'رمز التحقق غير صالح أو انتهت صلاحيته.',
            ], 400);
        }
        return response()->json([
            'message' => 'تم تأكيد البريد الإلكتروني بنجاح.',
            'user' => $user
        ], 200);
    }

}
