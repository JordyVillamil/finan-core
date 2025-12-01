<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Form Request para validar el registro de usuarios
 * 
 * PROPÓSITO:
 * Validar los datos ANTES de que lleguen al Controller.
 * Si la validación falla, Laravel automáticamente retorna error 422.
 * 
 * VENTAJAS:
 * - Separación de responsabilidades
 * - Reutilizable
 * - Mensajes de error personalizados
 * - Validación automática
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Aquí puedes verificar permisos antes de validar.
     * Por ejemplo: "Solo admins pueden registrar usuarios"
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        // Para registro público, siempre true
        // Si fuera solo para admins: return $this->user()?->isAdmin();
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * REGLAS DE VALIDACIÓN:
     * https://laravel.com/docs/11.x/validation#available-validation-rules
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // ============================================
            // NOMBRE
            // ============================================
            'name' => [
                'required',        // Obligatorio
                'string',          // Debe ser string
                'min:2',           // Mínimo 2 caracteres
                'max:255',         // Máximo 255 caracteres
                'regex:/^[\pL\s]+$/u', // Solo letras y espacios (Unicode)
            ],

            // ============================================
            // EMAIL
            // ============================================
            'email' => [
                'required',        // Obligatorio
                'string',          // Debe ser string
                'email:rfc',       // Validar formato email (sin DNS en desarrollo)
                'max:255',         // Máximo 255 caracteres
                'unique:users,email', // Único en tabla users, columna email
            ],

            // ============================================
            // PASSWORD
            // ============================================
            'password' => [
                'required',        // Obligatorio
                'string',          // Debe ser string
                'min:8',           // Mínimo 8 caracteres
                'max:100',         // Máximo 100 caracteres
                'confirmed',       // Debe venir con password_confirmation
                // Opcional: Reglas más estrictas
                // 'regex:/[a-z]/',      // Al menos una minúscula
                // 'regex:/[A-Z]/',      // Al menos una mayúscula
                // 'regex:/[0-9]/',      // Al menos un número
                // 'regex:/[@$!%*#?&]/', // Al menos un símbolo
            ],

            // ============================================
            // PASSWORD CONFIRMATION (automático)
            // ============================================
            // No necesitamos validar password_confirmation
            // Laravel lo hace automáticamente con 'confirmed'
        ];
    }

    /**
     * Get custom messages for validator errors.
     * 
     * Mensajes personalizados en español.
     * 
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Mensajes para NAME
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede tener más de :max caracteres.',
            'name.regex' => 'El nombre solo puede contener letras y espacios.',

            // Mensajes para EMAIL
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.max' => 'El email no puede tener más de :max caracteres.',
            'email.unique' => 'Este email ya está registrado.',

            // Mensajes para PASSWORD
            'password.required' => 'La contraseña es obligatoria.',
            'password.string' => 'La contraseña debe ser texto.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.max' => 'La contraseña no puede tener más de :max caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     * 
     * Nombres amigables para los campos.
     * 
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
        ];
    }

    /**
     * Handle a failed validation attempt.
     * 
     * Personalizar la respuesta cuando la validación falla.
     * Por defecto Laravel retorna 422 con los errores.
     * 
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        // Formato de respuesta personalizado
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}