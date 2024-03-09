<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;


class AuthRepository
{
    
    public function createUser(array $validatedData)
    {
    //    error_log("masuk 1");

        // Create a new user
        User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
        ]);

        // error_log("masuk 2");

        return true;
    }

    public function getAllUser()
    {
        return User::all();
    }

    /**
     * Get a user by ID.
     *
     * @param  int  $id
     * @return \App\Models\User|null
     */
    public function getUserById(int $id)
    {
        return User::find($id);
    }

    /**
     * Authenticate a user.
     *
     * @param  array  $credentials
    * @return string|null
     * @throws ValidationException
     */
    public function login(array $validatedCredentials)
    {
        // Print the received credentials to the PHP error log
    error_log('Received login attempt with email: ' .  $validatedCredentials['email']);

        // Attempt to log in
        if (!$token = auth()->attempt($validatedCredentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Print the received credentials to the PHP error log
    error_log('successful login attempt with email: ' . $validatedCredentials['email']);

    error_log('The Token is  ' . $token);


        return $token;
    }

    /**
     * Logout the authenticated user.
     *
     * @return bool
     */
    public function logout()
    {
        auth()->logout();
        return true;
    }

    public function updateName($id , string $name): User
    {
        $user = $this.getUserById($id);
        $user->name = $name;
        $user->save();

        return $user;

    }

    public function UpdateUser( $id, Array $validatedCredentials): User {

        $user = $this->getUserById($id);

        $user->name = $validatedCredentials['name'];
        $user->email = $validatedCredentials['email'];
        $user->password = Hash::make($validatedCredentials['password']);
        $user->role = $validatedCredentials['role'];

        $user->save();

        return $user;


    }

    /**
     * Update the user's password.
     *
     * @param  \App\Models\User  $user
     * @param  string  $newPassword
     * @param  string  $currentPassword
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updatePassword( $id, string $newPassword, string $currentPassword): void
    {
       
        $user = $this.getUserById($id);
       
        // Verify if the current password matches the user's actual password
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided current password is incorrect.'],
            ]);
        }

        // Update the password
        $user->password = Hash::make($newPassword);
        $user->save();
    }

    public function deleteUser($id): bool
    {
        $user = $this->getUserById($id);
        return $user->delete();
    }
}
