<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\User as UserResource;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Notifications\UserVerifyNotification;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Register
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(RegisterRequest $request)
    {

        try {
            DB::beginTransaction();

            // Create user data
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $credentials = $request->only('email', 'password');


            //  Generate token
            $token = $token = auth()->attempt($credentials);

            // Transform user data
            $data = new UserResource($user);

            // Validate if user needs to verify their account
            if(config('url.account_verify')){
                // Email Verification
                $user->notify(new UserVerifyNotification($token));

                return response()->json(compact('data'));
            }

            DB::commit();
        }
        catch (\Throwable $exception){
            dd($exception);
        }

        return response()->json(compact('token', 'data'));

    }
}
