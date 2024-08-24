<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *      title="Blog",
 *     version="1.0.0",
 *     description="Blog Swagger",
 *     )
 * ),
 *   @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 * )
 */
class AuthController extends Controller
{

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
   /**
 * @OA\Post(
 *      path="/api/register",
 *      operationId="register",
 *      tags={"Authentication"},
 *      summary="Register a new user",
 *      description="Registers a new user with the provided name, email, and password.",
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(
 *                  @OA\Property(property="name", type="string", example="Ivan Maulana"),
 *                  @OA\Property(property="email", type="string", format="email", example="ivan@gmail.com"),
 *                  @OA\Property(property="password", type="string", format="password", example="ivanmaulana123"),
 *                  @OA\Property(property="password_confirmation", type="string", format="password", example="ivanmaulana123"),
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=201,
 *          description="User registered successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="success"),
 *              @OA\Property(property="message", type="string", example="User is created successfully."),
 *              @OA\Property(property="data", type="object",
 *                  @OA\Property(property="token", type="string", example="access_token_here"),
 *                  @OA\Property(property="user", type="object",
 *                      @OA\Property(property="name", type="string", example="Ivan Maulana"),
 *                      @OA\Property(property="email", type="string", example="ivan@gmail.com"),
 *                  ),
 *              ),
 *          ),
 *      )
 * )
 */
public function register(Request $request): JsonResponse
{
    $validation = $this->validateRegistration($request);

    if ($validation->fails()) {
        return $this->validationErrorResponse($validation->errors());
    }

    $user = $this->createUser($request);

    $token = $this->generateUserToken($user, $request->email);

    $response = [
        'status' => self::STATUS_SUCCESS,
        'message' => 'User is created successfully.',
        'data' => [
            'token' => $token->accessToken,
            'user' => $user,
        ],
    ];

    return response()->json($response, JsonResponse::HTTP_CREATED);
}

private function validateRegistration(Request $request): \Illuminate\Contracts\Validation\Validator
{
    return Validator::make($request->all(), [
        'name' => 'required|string|max:250',
        'email' => 'required|string|email:rfc,dns|max:250|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);
}

private function validationErrorResponse($errors): JsonResponse
{
    return response()->json([
        'status' => self::STATUS_FAILED,
        'message' => 'Validation Error!',
        'data' => $errors,
    ], JsonResponse::HTTP_FORBIDDEN);
}

private function createUser(Request $request)
{
    return User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);
}

private function generateUserToken($user, $email)
{
    return $user->createToken($email);
}
  /**
 * @OA\Post(
 *      path="/api/login",
 *      operationId="login",
 *      tags={"Authentication"},
 *      summary="Login a user",
 *      description="Logs in a user with the provided email and password.",
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(
 *                  @OA\Property(property="email", type="string", format="email", example="ivan@gmail.com"),
 *                  @OA\Property(property="password", type="string", format="password", example="ivanmaulana123"),
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="User logged in successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="success"),
 *              @OA\Property(property="message", type="string", example="User is logged in successfully."),
 *              @OA\Property(property="data", type="object",
 *                  @OA\Property(property="token", type="string", example="access_token_here"),
 *                  @OA\Property(property="user", type="object",
 *                      @OA\Property(property="name", type="string", example="Ivan Maulana"),
 *                      @OA\Property(property="email", type="string", example="ivan@gmail.com"),
 *                  ),
 *              ),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Invalid credentials",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="failed"),
 *              @OA\Property(property="message", type="string", example="Invalid credentials"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Validation Error",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="failed"),
 *              @OA\Property(property="message", type="string", example="Validation Error!"),
 *              @OA\Property(property="data", type="object", example={"email": {"The email field is required."}}),
 *          ),
 *      ),
 * )
 */
public function login(Request $request): JsonResponse
{
    $validation = $this->validateLogin($request);

    if ($validation->fails()) {
        return $this->validationErrorResponse($validation->errors());
    }

    $user = $this->getUserByEmail($request->email);

    if (!$user || !Hash::check($request->password, $user->password)) {
        return $this->invalidCredentialsResponse();
    }

    $token = $this->generateUserToken($user, $request->email);

    $response = [
        'status' => self::STATUS_SUCCESS,
        'message' => 'User is logged in successfully.',
        'data' => [
            'token' => $token->accessToken,
            'user' => $user,
        ],
    ];

    return response()->json($response, JsonResponse::HTTP_OK);
}

private function validateLogin(Request $request): \Illuminate\Contracts\Validation\Validator
{
    return Validator::make($request->all(), [
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);
}

private function getUserByEmail($email)
{
    return User::where('email', $email)->first();
}

private function invalidCredentialsResponse(): JsonResponse
{
    return response()->json([
        'status' => self::STATUS_FAILED,
        'message' => 'Invalid credentials',
    ], JsonResponse::HTTP_UNAUTHORIZED);
}

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout and revoke the access token",
     *     tags={"Authentication"},
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User is logged out successfully."),
     *         )
     *     ),
     * security={
 *         {"bearerAuth": {}}
 *     }
     * )
     */
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User is logged out successfully'
        ], 200);
    }
}