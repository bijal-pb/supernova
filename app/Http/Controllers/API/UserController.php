<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOtp;
use App\Traits\ApiTrait;
use Auth;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mail;

/**
 * @OA\Info(
 *      description="",
 *     version="1.0.0",
 *      title="SuperNova",
 * )
 **/

/**
 *  @OA\SecurityScheme(
 *     securityScheme="bearer_token",
 *         type="http",
 *         scheme="bearer",
 *     ),
 **/
class UserController extends Controller
{

    use ApiTrait;
    /**
     *  @OA\Post(
     *     path="/api/register",
     *     tags={"User"},
     *     summary="Create Account",
     *     security={{"bearer_token":{}}},
     *     operationId="create account",
     *
     *     @OA\Parameter(
     *         name="name",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
     **/
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false);
        }

        try {
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->type = 2;
            $user->phone = $request->phone;
            $user->save();
            $user->assignRole([3]);
            return $this->response('', 'Registered Successully!');
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false);
        }
    }
    /**
     *  @OA\Post(
     *     path="/api/login",
     *     tags={"User"},
     *     summary="Login",
     *     security={{"bearer_token":{}}},
     *     operationId="login",
     *
     *     @OA\Parameter(
     *         name="email",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="password",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="device_type",
     *         description="android | ios",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="device_token",
     *         description="device token for push notification",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
     **/
    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required',
                'password' => 'required',
            ],
            [
                'email.required' => 'Email is required',
                'password.required' => 'Password is required',
            ]
        );

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {
            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return $this->response([], 'Enter vailid email or password', false);
            }
            $user = User::where('email', $request->email)->first();
            $user->device_type = $request->device_type;
            $user->device_token = $request->device_token;
            $user->save();
            $user->tokens()->delete();
            if ($user->status == 2) {
                return $this->response([], 'Your account is blocked, Please contact administrator!', false, 401);
            }
            $token = $user->createToken('API')->accessToken;
            $user['token'] = $token;
            return $this->response($user, 'Login Successully!');

        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }

    }

    /**
     *  @OA\Post(
     *     path="/api/profile/edit/image",
     *     tags={"User"},
     *     summary="Edit Profile Image",
     *     security={{"bearer_token":{}}},
     *     operationId="edit-profile-image",
     *
     *     @OA\RequestBody(
     *        @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *                @OA\Property(
     *                    property="photo",
     *                    description="User Profile photo",
     *                    type="array",
     *                    @OA\Items(type="file", format="binary")
     *                 ),
     *                ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
     **/
    public function edit_profile_image(Request $request)
    {
        try {
            $filename = null;
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . $file->getClientOriginalName();
                $file->move(public_path() . '/user/', $filename);
            }
            $user = User::find(Auth::id());
            if ($user) {
                if ($request->hasFile('photo')) {
                    $user->photo = $filename;
                }
                // $user->photo = $request->photo;
                $user->save();
                return $this->response($user, 'Profile image updated successfully!');
            }
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }
    /**
     *  @OA\Get(
     *     path="/api/profile",
     *     tags={"User"},
     *     security={{"bearer_token":{}}},
     *     summary="Get User Profile",
     *     operationId="profile",
     *
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
     **/
    public function me()
    {
        try {
            $user = User::find(Auth::id());
            return $this->response($user, 'Profile!');
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false);
        }

    }
    /**
     *  @OA\Post(
     *     path="/api/profile/edit",
     *     tags={"User"},
     *     summary="Edit Profile",
     *     security={{"bearer_token":{}}},
     *     operationId="edit-profile",
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
     **/
    public function edit_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'name' => 'nullable|max:255',
            'phone' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,404);
        }

        try {
            $user = User::find(Auth::id());
            if ($user) {
                $user->email = $request->email;
                $user->name = $request->name;
                if (isset($request->phone)) {
                     $user->phone = $request->phone;
                }
                $user->save();
                return $this->response($user, 'User updated successfully!');
            }
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false);
        }
    }
    /**
     *  @OA\Post(
     *     path="/api/username/check",
     *     tags={"User"},
     *     summary="Username Check available or not register time",
     *     operationId="Username-Check",
     *
     *     @OA\Parameter(
     *         name="user_name",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
     **/
    public function check_username(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false);
        }

        $user = User::where('user_name', $request->user_name)->first();
        if ($user) {
            return $this->response([], 'Already taken this username!', false);
        }
        return $this->response('', 'Username Available!');
    }
    /**
     *  @OA\Get(
     *     path="/api/logout",
     *     tags={"User"},
     *     security={{"bearer_token":{}}},
     *     summary="Logout",
     *     security={{"bearer_token":{}}},
     *     operationId="Logout",
     *
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
     **/
    public function logout()
    {
        try {
            $user = User::find(Auth::id());
            $user->tokens()->delete();
            return $this->response('', 'Logout Successfully!');
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false);
        }

    }

    /**
     *  @OA\Post(
     *     path="/api/change/password",
     *     tags={"User"},
     *     summary="Change Password",
     *     security={{"bearer_token":{}}},
     *     operationId="change-password",
     *
     *     @OA\Parameter(
     *         name="current_password",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="new_password",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password_confirmation",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
     **/
    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|same:password_confirmation',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false);
        }
        try {
            $user = User::find(Auth::id());
            if (Hash::check($request->current_password, $user->password)) {
                $user->password = bcrypt($request->new_password);
                $user->save();
                return $this->response('', 'Password changed successfully!');
            }
            return $this->response([], 'Old password is incorrect', false);
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false);
        }
    }
    /**
     *  @OA\Post(
     *     path="/api/forgot/password",
     *     tags={"User"},
     *     summary="Forgot password",
     *     operationId="forgot-password",
     *
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     * )
     **/
    public function forgot_password(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        $user = User::where('email', $request->email)->first();
        if (empty($user)) {
            return $this->response([], 'This email not registered', false);
        }

        try {
            $otp = rand(100000, 999999);
            $data = [
                'username' => $user->name,
                'otp' => $otp,
            ];
            UserOtp::where('user_id', $user->id)->delete();
            $saveOtp = new UserOtp;
            $saveOtp->user_id = $user->id;
            $saveOtp->otp = $otp;
            $saveOtp->save();
            $email = $user->email;
            $name = $user->name;
            Mail::send('mail.forgot', $data, function ($message) use ($email, $name) {
                $message->to($email, $name)->subject('Forgot Password');
            });
            return $this->response('', 'Email sent succesfully!');
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }

    }

    /**
     * @OA\Get(
     *     path="/api/employee/list",
     *     tags={"Business"},
     *     summary="Employee List",
     *     security={{"bearer_token":{}}},
     *     operationId="employee-list",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
     **/
    public function employee_list(Request $request)
    {
        $validator = Validator::make($request->all(), [

        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {
            $emp_list = User::where('type', 3)->get();
            return $this->response($emp_list, 'Employee List!');
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }

    /**
     *  @OA\Post(
     *     path="/api/employee/add/edit",
     *     tags={"User"},
     *     summary="Add / Edit Employee",
     *     security={{"bearer_token":{}}},
     *     operationId="add/employee",
     *
     *     @OA\Parameter(
     *         name="id",
     *         description="user id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *       @OA\Parameter(
     *         name="name",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="password",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *        @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *                @OA\Property(
     *                    property="photo",
     *                    description="User Profile photo",
     *                    type="array",
     *                    @OA\Items(type="file", format="binary")
     *                 ),
     *                ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
     **/
    public function employee_add_edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|exists:users,id',
            'photo' => 'nullable|image|mimes:svg,jpeg,jpg,gif,png',
            'name' => 'required|max:255',
            'password' => 'required|min:8',
            'phone' => 'required|min:10',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {

            if ($request->id != null) {
                $user = User::find($request->id);
                $message = "Employee is updated successfully.";
            } else {
                $user = new User;
                $message = "Employee is added successfully.";
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->phone = $request->phone;
            if ($request->hasFile('photo')) {
                $filename = null;
                $file = $request->file('photo');
                $filename = time() . $file->getClientOriginalName();
                $file->move(public_path() . '/user/', $filename);
                $user->photo = $filename;
            }
            $user->type = 3;
            $user->save();
            // $user->assignRole([2]);
            return $this->response($user, $message);
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }

    /**
     *  @OA\Post(
     *     path="/api/employee/delete",
     *     tags={"User"},
     *     summary="Delete Employee",
     *     security={{"bearer_token":{}}},
     *     operationId="employee-delete",
     *
     *      @OA\Parameter(
     *         name="id",
     *         description="user id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
     **/
    public function employee_delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {
            $employee = User::where('id', $request->id)->where('type', 3)->first();
            if ($employee) {
                if ($employee->delete()) {
                    return $this->response('', 'Employee deleted successfully.');
                }
            }
            return $this->response([], 'Enter valid user id!.', false);
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }

    /**
     *  @OA\Post(
     *     path="/api/update/password",
     *     tags={"User"},
     *     summary="Update password",
     *     operationId="update-password",
     *
     *     @OA\Parameter(
     *         name="otp",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="password",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     * )
     **/
    public function update_password(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {

            $userOtp = UserOtp::where('otp', $request->otp)->first();
            if ($userOtp) {
                $user = User::find($userOtp->user_id);
                $user->password = bcrypt($request->password);
                $user->save();
                $userOtp->delete();
                return $this->response('', 'Password is updated successfully');
            }
            return $this->response([], 'Enter valid otp!', false, 404);
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }

}
