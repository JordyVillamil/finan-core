<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Application\Auth\Services\RegisterUserService;
use App\Application\Auth\Services\LoginUserService;
use App\Application\Auth\Services\EmailVerificationService;
use App\Application\Auth\Services\PasswordResetService;
use App\Application\Auth\DTOs\RegisterUserDTO;
use App\Application\Auth\DTOs\LoginUserDTO;
use App\Domain\Auth\ValueObjects\Email;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controller para autenticación
 * 
 * RESPONSABILIDADES:
 * 1. Recibir HTTP Request
 * 2. Validar (con FormRequest)
 * 3. Convertir a DTO
 * 4. Llamar al Service
 * 5. Retornar JSON Response
 * 
 * NO TIENE:
 * - Lógica de negocio
 * - Acceso a BD
 * - Validaciones complejas
 */
class AuthController extends Controller
{
    /**
     * Constructor - Inyección de dependencias
     * 
     * Laravel inyecta automáticamente los services
     */
    public function __construct(
        private RegisterUserService $registerService,
        private LoginUserService $loginService,
        private EmailVerificationService $emailVerificationService,
        private PasswordResetService $passwordResetService,
    ) {}

    /**
     * Registrar un nuevo usuario
     * 
     * @param RegisterRequest $request Request validado
     * @return JsonResponse
     * 
     * ENDPOINT: POST /api/auth/register
     * 
     * REQUEST BODY:
     * {
     *   "name": "Juan Pérez",
     *   "email": "juan@example.com",
     *   "password": "password123",
     *   "password_confirmation": "password123"
     * }
     * 
     * RESPONSE 201:
     * {
     *   "success": true,
     *   "message": "Usuario registrado exitosamente",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Juan Pérez",
     *       "email": "juan@example.com",
     *       "is_active": true,
     *       "is_email_verified": false,
     *       "created_at": "2024-01-15 10:30:00"
     *     }
     *   }
     * }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // 1. Convertir Request validado a DTO
            $dto = RegisterUserDTO::fromArray($request->validated());

            // 2. Ejecutar caso de uso (Service)
            $userResponse = $this->registerService->execute($dto);

            // 3. Retornar respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'user' => $userResponse->toArray(),
                ],
            ], 201); // 201 = Created

        } catch (\Exception $e) {
            // 4. Manejar errores
            return $this->handleException($e);
        }
    }

    /**
     * Login de usuario
     * 
     * @param LoginRequest $request Request validado
     * @return JsonResponse
     * 
     * ENDPOINT: POST /api/auth/login
     * 
     * REQUEST BODY:
     * {
     *   "email": "juan@example.com",
     *   "password": "password123",
     *   "remember": false
     * }
     * 
     * RESPONSE 200:
     * {
     *   "success": true,
     *   "message": "Login exitoso",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Juan Pérez",
     *       "email": "juan@example.com",
     *       ...
     *     },
     *     "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxx"
     *   }
     * }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // 1. Convertir a DTO
            $dto = LoginUserDTO::fromArray($request->validated());

            // 2. Ejecutar caso de uso
            $result = $this->loginService->execute($dto);

            // 3. Retornar respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'user' => $result['user']->toArray(),
                    'token' => $result['token'],
                ],
            ], 200);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Logout (cerrar sesión)
     * 
     * @return JsonResponse
     * 
     * ENDPOINT: POST /api/auth/logout
     * HEADERS: Authorization: Bearer {token}
     * 
     * RESPONSE 200:
     * {
     *   "success": true,
     *   "message": "Sesión cerrada exitosamente"
     * }
     */
    public function logout(): JsonResponse
    {
        try {
            // Obtener usuario autenticado
            /** @var UserModel|null $user */
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay sesión activa',
                ], 401);
            }

            // Revocar token actual (Sanctum)
            // currentAccessToken() retorna el token usado en esta petición
            /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Obtener usuario autenticado
     * 
     * @return JsonResponse
     * 
     * ENDPOINT: GET /api/auth/me
     * HEADERS: Authorization: Bearer {token}
     * 
     * RESPONSE 200:
     * {
     *   "success": true,
     *   "data": {
     *     "user": { ... }
     *   }
     * }
     */
    public function me(): JsonResponse
    {
        try {
            /** @var UserModel|null $user */
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                ],
            ], 200);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Verificar email con token
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * ENDPOINT: POST /api/auth/verify-email
     * 
     * REQUEST BODY:
     * {
     *   "token": "abc123def456..."
     * }
     * 
     * RESPONSE 200:
     * {
     *   "success": true,
     *   "message": "Email verificado exitosamente"
     * }
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'token' => 'required|string',
            ]);

            $this->emailVerificationService->verifyEmail($request->token);

            return response()->json([
                'success' => true,
                'message' => 'Email verificado exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reenviar email de verificación
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * ENDPOINT: POST /api/auth/resend-verification
     * 
     * REQUEST BODY:
     * {
     *   "email": "usuario@example.com"
     * }
     * 
     * RESPONSE 200:
     * {
     *   "success": true,
     *   "message": "Email de verificación reenviado"
     * }
     */
    public function resendVerification(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $email = Email::fromString($request->email);
            $this->emailVerificationService->resendVerificationEmail($email);

            return response()->json([
                'success' => true,
                'message' => 'Email de verificación reenviado. Revisa tu bandeja de entrada.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Solicitar reset de contraseña (forgot password)
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * ENDPOINT: POST /api/auth/forgot-password
     * 
     * REQUEST BODY:
     * {
     *   "email": "usuario@example.com"
     * }
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $email = Email::fromString($request->email);
            $this->passwordResetService->sendResetEmail($email);

            return response()->json([
                'success' => true,
                'message' => 'Si el email existe, recibirás un link para restablecer tu contraseña.',
            ], 200);

        } catch (\Exception $e) {
            // Por seguridad, NO revelamos si el email existe
            // Siempre retornamos el mismo mensaje
            return response()->json([
                'success' => true,
                'message' => 'Si el email existe, recibirás un link para restablecer tu contraseña.',
            ], 200);
        }
    }

    /**
     * Restablecer contraseña con token
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * ENDPOINT: POST /api/auth/reset-password
     * 
     * REQUEST BODY:
     * {
     *   "token": "abc123...",
     *   "email": "usuario@example.com",
     *   "password": "newpassword123",
     *   "password_confirmation": "newpassword123"
     * }
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'token' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $email = Email::fromString($request->email);
            
            $this->passwordResetService->resetPassword(
                $request->token,
                $email,
                $request->password
            );

            return response()->json([
                'success' => true,
                'message' => 'Contraseña restablecida exitosamente. Ya puedes iniciar sesión.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Manejar excepciones y convertir a respuestas HTTP
     * 
     * @param \Exception $e Excepción capturada
     * @return JsonResponse
     */
    private function handleException(\Exception $e): JsonResponse
    {
        // Log del error para debugging
        Log::error('Error en AuthController', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        // Mapear excepciones del dominio a códigos HTTP
        $statusCode = match (get_class($e)) {
            \App\Domain\Auth\Exceptions\InvalidCredentialsException::class => 401,
            \App\Domain\Auth\Exceptions\UserNotFoundException::class => 404,
            \App\Domain\Auth\Exceptions\UserNotActiveException::class => 403,
            \InvalidArgumentException::class => 400,
            default => 500,
        };

        // Mensaje personalizado según el error
        $message = $statusCode === 500 
            ? 'Error interno del servidor' 
            : $e->getMessage();

        return response()->json([
            'success' => false,
            'message' => $message,
            // Solo en desarrollo: mostrar detalles del error
            'error' => app()->environment('local') ? [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ] : null,
        ], $statusCode);
    }
}