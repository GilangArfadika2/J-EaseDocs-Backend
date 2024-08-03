<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use App\Validations\AuthValidation; 
use Illuminate\Support\Facades\Cache;
use App\Models\LogAdmin;
use App\Repositories\LogAdminRepository;

class AuthController extends Controller
{
    protected $authRepository;
    protected $logAdminRepository;


    public function __construct(AuthRepository $authRepository, LogAdminRepository $logAdminRepository)
    {
        $this->authRepository = $authRepository;
        $this->logAdminRepository = $logAdminRepository;
        $this->middleware('check.role.and.cookie')->only(['getAllUser', 'getUserById', 'deleteUser', 'updateUser','updateUserGetter','registerGetter']);
    }

    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            if (!$request->hasCookie('jwt_token')) {
                return response()->json(['message' => 'Missing token cookie'], 401);
            }
            $token = $request->cookie('jwt_token');
            $user = Auth::guard('web')->setToken($token)->user();
        
            // Check if the user is authenticated
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        
    
            $validator = Validator::make($request->all(), AuthValidation::getRegisterRules());
            if ($validator->fails()) {
                return response()->json(['message' => 'Invalid', 'errors' => $validator->errors()], 400);
            }
            // If validation passes, proceed with user registration
            $this->authRepository->createUser($request->all());
            $logAdmin = new LogAdmin();
            $logAdmin->user_id = $user->id;
            $logAdmin->action = "User " . $user->name .   " with role " . $user->role .   " has registered new User " . $request->input("name") . " with role " . $request->input("role") ;
            $this->logAdminRepository->create($logAdmin->getAttributes());

            return response()->json(['message' => 'User registered successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Registration failed', 'errors' => $e->getMessage()], 500);
        }
    }
    

    /**
     * Authenticate a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), AuthValidation::getLoginRules());
    
            if ($validator->fails()) {
                return response()->json(['message' => 'input json is not validated', 'errors' => $validator->errors()], 400);
            }
            $token = $this->authRepository->login($request->only('email', 'password'));
            //Auth::attempt(['email', 'password']);
            return response()->json(['message' => 'Login successful'])->cookie(
                'jwt_token', // Cookie name
                $token,      // Token value
                60,          // Cookie expiration time in minutes
                '/',         // Path
                null,        // Domain (null for any domain)
                true,        // Secure (set to true if using HTTPS)
                true,        // HTTP-only flag
                false,        // Encrypt (set to true to enable encryption of the cookie value, which is the default behavior)
                'None'       // SameSite attribute set to 'None'
            );
        } catch (ValidationException $e) {
            error_log("error : " . $e);
            return response()->json(['message' => 'Login failed', 'errors' => $e->errors()], 400);
        }
    }

    /**
     * Logout the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            if ($request->hasCookie('jwt_token')) {
                $token = $request->cookie('jwt_token');
    
                Auth::guard('web')->setToken($token)->invalidate();
    
                // Clear the JWT token cookie from the client side by sending an expired cookie
                return response()->json(['message' => 'Logged out successfully'])->cookie(
                    'jwt_token',
                    '', // Empty token value
                    time() - 3600, // Expired time in the past
                    '/',
                    null,        // Domain (null for any domain)
                    true,        // Secure (set to true if using HTTPS)
                   true,        // HTTP-only flag
                   false,        // Encrypt (set to true to enable encryption of the cookie value, which is the default behavior)
                   'None'       // SameSite attribute set to 'None'
                );
            } else {
                // No token cookie found, user is already logged out
                return response()->json(['message' => 'No token cookie found, user is already logged out'],200);
            }
        } catch (Exception $e) {
            error_log("error : " . $e);
            return response()->json(['message' => 'Logout failed ' . $e], 500);
        }
    }

    public function isLogin(Request $request) {
        try {
            // Check if the result is cached
            
            $cachedResult = Cache::get('isLoginResult_' . $request->cookie('jwt_token'));
            // error_log("test2: "+$request->cookie('jwt_token'));
            error_log($cachedResult);
            if ($cachedResult) {
                return $cachedResult;
            }
    
            if (!$request->hasCookie('jwt_token')) {
                $response = response()->json(['message' => 'Missing token cookie', 'log_in' => 'false'], 400);
            } else {
                $token = $request->cookie('jwt_token');
                $user = Auth::guard('web')->setToken($token)->user();
    
                $response = response()->json([
                    'message' => 'User is already logged in',
                    'log_in' => 'true',
                    'id' => $user->id,
                    'role' => $user->role,
                    'email' => $user->email
                ], 200);
            }
    
            // Cache the result for future use
            Cache::put('isLoginResult_' . $request->cookie('jwt_token'), $response, now()->addMinutes(10)); // Adjust expiry time as needed
    
            return $response;
        } catch (Exception $e) {
            return response()->json(['message' => 'User is not logged in', 'log_in' => 'false'], 500);
        }
    }

    /**
     * Clear the JWT token cookie.
     *
     * @return void
     */
    protected function clearTokenCookie()
    {
        // Set an empty cookie with the same name and past expiration time
        return Cookie::forget('jwt_token');
    }


    public function updateName(Request $request)
    {
        // Retrieve the JWT token from the cookie
        if (!$token=$request->hasCookie('jwt_token')) {
            return response()->json(['message' => 'Missing token cookie'], 401);
        }
        // Authenticate the user using the token
        $user = Auth::guard('web')->setToken($token)->user();
    
        // Check if the user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
    
        // Update the user's name
        $this->authRepository->updateName($user, $request->name);
    
        return response()->json(['message' => 'Name updated successfully', 'user' => $user]);
    }
    
   
    public function updatePassword(Request $request)
    {
        // Retrieve the JWT token from the cookie
        $token = $request->cookie('jwt_token');
    
        // Authenticate the user using the token
        $user = Auth::guard('web')->setToken($token)->user();
    
        // Check if the user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        // Validate the request data
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|max:255',
            "new_confirm" => 'required|string|min:8|max:255',
        ]);
    
        // Update the user's password
        $this->authRepository->updatePassword($user->id, $request->new_password, $request->current_password, $request->new_confirm);
    
        return AuthController::logout($request);
    }

    
    public function getAllUser(Request $request) {
        try {

            if (!$request->hasCookie('jwt_token')) {
                return response()->json(['message' => 'Missing token cookie'], 400);
            }

            $token = $request->cookie('jwt_token');

            $user = Auth::guard('web')->setToken($token)->user();

            $listUsers = $this->authRepository->getAllUser();

        return response()->json([ 'message' => 'User Fetched succesfully','user'=> $user, 'data' => $listUsers]);
        }
        catch (Exception $e) {
            error_log($e.getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function getAllJabatan(){
        try{
            

            $listJabatan = $this->authRepository->getAllJabatan();

        return response()->json([ 'message' => 'Jabatan Fetched succesfully', 'data' => $listJabatan]);
        }
            
        catch(Exception $e){
            error_log($e.getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getUserById(Request $request){
        try {
            
            $request->merge(['id' => $request->route('id')]);
            $validator = Validator::make($request->all(), AuthValidation::getUserIDRules());
            
            if ($validator->fails()) {
                return response()->json(['message' => 'input json is not validated', 'errors' => $validator->errors()], 400);
            }

            // Retrieve email and OTP from the request
            $id = $request->input('id');

            $user = $this->authRepository->getUserById($id);

            if(!$user){
                return response()->json(['message' => 'User not found'],400);
            } else {
                return response()->json(['message' => 'User Fetched Successfully', 'data' => $user],200);
            }
        }   
        catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteUser(Request $request){
        
        $request->merge(['id' => $request->route('id')]);
        $validator = Validator::make($request->all(), AuthValidation::getUserIDRules());
    
        if ($validator->fails()) {
            return response()->json(['message' => 'input json is not validated', 'errors' => $validator->errors()], 400);
        }

        if (!$request->hasCookie('jwt_token')) {
            return response()->json(['message' => 'Missing token cookie'], 400);
        }

        $token = $request->cookie('jwt_token');

        $admin = Auth::user();
        $admin = Auth::guard('web')->setToken($token)->user();
        if (!$admin)  {
            return response()->json(['message' => 'User is unauthorized'], 400);
        }
        else{
            if(in_array($admin->role, ['admin'])){
                return response()->json(['user' => 'admin'], 403);
            }
            else{
                $id = $request->input('id');
        
            $user = $this->authRepository->getUserById($id);
            error_log("user yang akan dihapus" . $user);
            switch($user['role']){
                case "admin":
                    if($admin->role!=="superadmin")return response()->json(['message' => 'User is unauthorized'], 403);
                case "superadmin":
                    if($admin->role!=="superadmin")return response()->json(['message' => 'User is unauthorized'], 403);
                default:
                break;
                // Retrieve email and OTP from the request
            }

            $logAdmin = new LogAdmin();
            $logAdmin->user_id = $admin->id;
            $logAdmin->action = "User " . $admin->name  .   " with role " . $admin->role .   " has updated User " . $user->name . " with role " . $user->role ;
            $this->logAdminRepository->create($logAdmin->getAttributes());

            $this->authRepository->deleteUser($id);
            error_log("check");
            

            return response()->json(['message' => 'User Deleted succesfully']);

        }

        
        

        }


    }

    //filter out admin vs superadmin privilege
    



    public function updateUser(Request $request){
        // $request->merge(['id' => $request->route('id')]);
        error_log($request->role);
        $validator = Validator::make($request->all(), AuthValidation::getUpdateRules());
        if ($validator->fails()) {
            return response()->json(['message' => 'input json is not validated', 'errors' => $validator->errors()], 400);
        }
        $token = $request->cookie('jwt_token');
        $user = Auth::guard('web')->setToken($token)->user();
        
            // Check if the user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $id = $request->input('id');

        $this->authRepository->updateUser($id, $request->all());
        $logAdmin = new LogAdmin();
        $logAdmin->user_id = $user->id;
        $logAdmin->action = "User " . $user->name .   " with role " . $user->role .   " has updated User " . $request->input("name") . " with role " . $request->input("role") ;
        $this->logAdminRepository->create($logAdmin->getAttributes());

        return response()->json(['message' => 'User Updated succesfully']);
    }   
}
