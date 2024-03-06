<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use App\Validations\AuthValidation; 


class AuthController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
        $this->middleware('check.role.and.cookie')->only(['getAllUser', 'getUserById', 'deleteUser', 'updateUser']);
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
            // Validate JSON data against the schema
    
            $validator = Validator::make($request->all(), AuthValidation::getRegisterRules());
    
            if ($validator->fails()) {
                return response()->json(['message' => 'Invalid', 'errors' => $validator->errors()], 400);
            }
            // If validation passes, proceed with user registration
            $this->authRepository->createUser($request->all());
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
                null,        // Domain
                false,       // Secure (set to true if using HTTPS)
                true         // HTTP-only flag
            );
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Login failed', 'errors' => $e->errors()], 422);
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
    
                // Perform the logout using JWTAuth
                Auth::guard('web')->setToken($token)->invalidate();
    
                // Clear the JWT token cookie from the client side by sending an expired cookie
                return response()->json(['message' => 'Logged out successfully'])->cookie(
                    'jwt_token',
                    '', // Empty token value
                    time() - 3600, // Expired time in the past
                    '/',
                    null,
                    false,
                    true
                );
            } else {
                // No token cookie found, user is already logged out
                return response()->json(['message' => 'No token cookie found, user is already logged out'],200);
            }
        } catch (\Exception $e) {
            error_log("error : " . $e);
            return response()->json(['message' => 'Logout failed ' . $e], 500);
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
        $token = $request->cookie('jwt_token');
    
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
        ]);
    
        // Update the user's password
        $this->authRepository->updatePassword($user, $request->new_password, $request->current_password);
    
        return response()->json(['message' => 'Password updated successfully']);
    }

    public function getAllUser(Request $request) {
       
        $listUsers = $this->authRepository->getAllUser();
        $_SESSION['allUser'] = $listUsers;
        return view('listAllUser');

       //return response()->json(['message' => 'User Fetched succesfully', 'data' => $listUsers]);
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
                return response()->json(['message' => 'User not found', 'data' => $id],200);
            }
            $_SESSION['user']=$user;
            return view('userDetail');
        }   
        catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }


    }

    public function deleteUser(Request $request){
        
        

        $validator = Validator::make($request->all(), AuthValidation::getUserIDRules());
    
        if ($validator->fails()) {
            return response()->json(['message' => 'input json is not validated', 'errors' => $validator->errors()], 400);
        }

        // Retrieve email and OTP from the request
        $id = $request->input('id');

        $this->authRepository->deleteUser($id);

        return response()->json(['message' => 'User Deleted succesfully']);


    }

    public function updateUser(Request $request){
        
        $validator = Validator::make($request->all(), AuthValidation::getRegisterRules());
    
        if ($validator->fails()) {
            return response()->json(['message' => 'input json is not validated', 'errors' => $validator->errors()], 400);
        }

        // Retrieve email and OTP from the request
        $id = $request->input('id');

        this->authRepository->updateUser($id, $request->all());

        return response()->json(['message' => 'User Updated succesfully']);


    }

    
}
