<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserAuthenticationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     summary="Регистрация нового пользователя",
     *     tags= {"auth"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Имя пользователя",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="phone_number",
     *         in="query",
     *         description="Номер телефона пользователя. Должен быть уникальным для каждого. От 11 символов",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="Пароль пользователя. От 8 символов",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="201", description="Успешная регистрация"),
     *     @OA\Response(response="422", description="Ошибка при регистрации")
     * )
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|min:11|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $name = $request->input('name');
        $phone_number = strtolower($request->input('phone_number'));
        $password = $request->input('password');

        $user = User::create([
            'name' => $name,
            'phone_number' => $phone_number,
            'password' => Hash::make($password)
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User Account Created Successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Авторизация и генерирование Sanctum токена",
     *     tags= {"auth"},
     *     @OA\Parameter(
     *         name="phone_number",
     *         in="query",
     *         description="Номер телефона пользователя. От 11 символов",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="Пароль пользователя. От 8 символов",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Успешный вход"),
     *     @OA\Response(response="401", description="Ошибка в авторизации")
     * )
     */
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|min:11',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $phone_number = strtolower($request->input('phone_number'));
        $password = $request->input('password');

        $credentials = [
            'phone_number' => $phone_number,
            'password' => $password
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $user = User::where('phone_number', $request['phone_number'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     summary="Выход из аккаунта",
     *     tags= {"auth"},
     *     @OA\Response(response="200", description="Успешный выход из аккаунта"),
     * )
     */
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Succesfully Logged out'
        ], 200);
    }
}
