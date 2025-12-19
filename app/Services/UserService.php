<?php

namespace App\Services;

use App\DTOs\LoginDTO;
use App\Http\Requests\AuthadminRequest;
use App\Http\Requests\EmployeeRequest;
use App\Mail\EmailVerificationMail;
use App\Models\EmailVerification;
use App\Models\User;
use App\Repositories\userRepository;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Mockery\Exception;

class UserService
{
    protected $repository;

    public function __construct(userRepository $repository)
    {
        $this->repository = $repository;
    }

    public function login(AuthadminRequest $request)
    {
        $email = $request->email;
        $key = 'login-attempts:' . $email;

        $user = $this->repository->getByEmail($email);
        if ($user)
            if (!$user || !Auth::attempt($request->only(['email', 'password']))) {
                return [
                    'user' => null,
                    'message' => 'Invalid credentials',
                    'code' => 401
                ];
            }
        if (is_null($user->email_verified_at)) {
            return [
                'user' => null,
                'message' => 'البريد الإلكتروني غير مُفعل. يرجى إتمام عملية التحقق.',
                'code' => 403
            ];
        }

        $key = "login:attempts:" . ($user->id ?? $email);
        RateLimiter::clear($key);

        $user = $this->appendRolesAndPermission($user);

        $user['token'] = $user->createToken('token')->plainTextToken;

        return [
            'user' => $user,
            'message' => 'Login successful',
            'code' => 200
        ];
    }

    public function logout()
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $user->delete();
            $message = 'user Logged out  Successfully';
            $code = 200;
        } else {
            $message = 'invaild token';
            $code = 404;
        }
        return (['user' => $user, 'message' => $message, 'code' => $code]);
    }

    private function appendRolesAndPermission($user)
    {

        $roles = [];
        foreach ($user->roles as $role) {
            $roles[] = $role->name;

        }
        unset($user['roles']);
        $user['roles'] = $roles;
        $permissions = [];
        foreach ($user->permissions as $permission) {
            $permissions[] = $permission->name;
        }
        unset($user['permissions']);
        $user['permissions'] = $permissions;
        return $user;
    }

    /////////////////////
    public function register(array $data)
    {
        DB::beginTransaction();
        try {
            // 1. إنشاء المستخدم
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'national_id' => $data['national_id'],
                'password' => Hash::make($data['password']), // استخدام Hash::make أفضل من bcrypt
            ]);

            $employeeRole = $this->repository->getByName('citizen');
            $user->assignRole($employeeRole);
            if ($user->has('permissions')) {
                //  $employeeRole->syncPermissions($request->permissions);
                $user->syncPermissions($user->permissions);
            }
            $user->load('permissions', 'roles');
            $user = $this->repository->getById($user->id);
            $user = $this->appendRolesAndPermission($user);

            // 2. توليد وحفظ رمز التحقق (OTP)
            $verificationCode = random_int(100000, 999999);

            EmailVerification::create([
                'user_id' => $user->id,
                'code' => (string)$verificationCode,
                'expires_at' => now()->addMinutes(10), // رمز التحقق صالح لمدة 10 دقائق
            ]);

            // 3. إرسال الإيميل (يجب أن يكون لديك الكلاس App\Mail\EmailVerificationMail جاهزاً)
            Mail::to($user->email)->send(new EmailVerificationMail($verificationCode));

            DB::commit();

            return [
                'user_id' => $user->id,
                'message' => 'Registration successful. Verification code sent to email.'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            // يمكنك تسجيل الخطأ هنا
            throw new Exception('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * التحقق من البريد الإلكتروني باستخدام الرمز (OTP).
     * @param int $userId معرف المستخدم الذي يحاول التحقق
     * @param string $code رمز التحقق المرسل
     * @return User|null المستخدم بعد التحقق
     */
    public function verifyEmail(int $userId, string $code)
    {
        DB::beginTransaction();
        try {
            // 1. البحث عن رمز التحقق النشط المرتبط بهذا المستخدم
            $verification = EmailVerification::where('user_id', $userId)
                ->where('code', $code)
                ->where('expires_at', '>', now())
                ->first();

            if (!$verification) {
                DB::rollBack();
                return null; // رمز غير صالح أو منتهي الصلاحية
            }

            // 2. تحديث المستخدم كـ verified
            $user = $verification->user;
            $user->email_verified_at = now();
            $user->save();

            // 3. حذف رمز التحقق
            $verification->delete();

            DB::commit();
            return $user->load('roles');

        } catch (Exception $e) {
            DB::rollBack();
            // يمكنك تسجيل الخطأ هنا
            throw new Exception('Email verification failed.');
        }
    }

    public function getAllEmployee( )
    {
        $client=$this->repository->getByName('employee');
        return $client;

    }



    public function getAlldirector( )
    {
        $client=$this->repository->getByName('director');
        return $client;
    }




    public function getallClient( )
    {
        $client=$this->repository->getByName('client');
        return $client;

    }

//
    public function add_client(EmployeeRequest $request)
    {if (optional(Auth::user())->hasRole('client')) {
            $client = $this->repository->create($request->all());
            $employeeRole = $this->repository->getByName('client');
            $client->assignRole($employeeRole);
            if ($request->has('permissions')) {
                $client->syncPermissions($request->permissions);
            }
            $client->load('permissions', 'roles');
            $client = $this->repository->getById($client->id);
            $client = $this->appendRolesAndPermission($client);
            $message = 'Employee added successfully';
            $code = 200;
        }else{
            $client = null;
            $message = 'You do not have permission to perform this action';
            $code = 403;
        }
        return [
            'user' => $client,
            'message' => $message,
            'code' => $code
        ];
    }
//اضافة مدير
    public function add_director(EmployeeRequest $request)
    {
        if (optional(Auth::user())->hasRole('director')) {
            $director = $this->repository->create($request->all());
            $employeeRole = $this->repository->getByName('director');
            $director->assignRole($employeeRole);
            if ($request->has('permissions')) {
                $director->syncPermissions($request->permissions);
            }
            $director->load('permissions', 'roles');
            $director = $this->repository->getById($director->id);
            $director = $this->appendRolesAndPermission($director);
            $message = 'Employee added successfully';
            $code = 200;
        }else{
            $director = null;
            $message = 'You do not have permission to perform this action';
            $code = 403;

        }

        return [
            'user' => $director,
            'message' => $message,
            'code' => $code
        ];
    }
    //اضافة موظف
    public function add_employee(EmployeeRequest $request)
    {
        if (optional(Auth::user())->hasRole('employee')) {
            $employee = $this->repository->create($request->all());
            $employeeRole = $this->repository->getByName('director');
            $employee->assignRole($employeeRole);
            if ($request->has('permissions')) {
                $employee->syncPermissions($request->permissions);
            }
            $employee->load('permissions', 'roles');
            $employee = $this->repository->getById($employee->id);
            $employee = $this->appendRolesAndPermission($employee);
            $message = 'Employee added successfully';
            $code = 200;
        }else{
            $employee = null;
            $message = 'You do not have permission to perform this action';
            $code = 403;

        }

        return [
            'user' => $employee,
            'message' => $message,
            'code' => $code
        ];
    }

}
