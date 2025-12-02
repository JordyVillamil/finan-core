<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request: UpdateCompanyRequest
 * 
 * Valida los datos para actualizar una empresa existente.
 * 
 * NOTA:
 * - No se puede cambiar el tax_id (RFC/NIT)
 * - Por eso no está en las reglas de validación
 */
class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        // Verificar que el usuario tenga el permiso 'edit-companies'
        return $this->user()->can('edit-companies');
    }

    /**
     * Reglas de validación
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Información básica - 'sometimes' permite actualizaciones parciales
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            
            'legal_name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            
            'email' => [
                'sometimes',
                'required',
                'email:rfc',  // Sin DNS para permitir dominios de prueba
                'max:255',
            ],
            
            'phone' => [
                'nullable',
                'string',
                'min:7',
                'max:20',
            ],
            
            // Dirección
            'address_street' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            
            'address_city' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            
            'address_state' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            
            'address_country' => [
                'nullable',
                'string',
                'max:100',
            ],
            
            'address_postal_code' => [
                'nullable',
                'string',
                'min:3',
                'max:10',
            ],
            
            // Configuración fiscal
            'tax_regime' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    /**
     * Mensajes de error personalizados
     * 
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la empresa es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            
            'legal_name.required' => 'La razón social es obligatoria.',
            'legal_name.min' => 'La razón social debe tener al menos 2 caracteres.',
            'legal_name.max' => 'La razón social no puede exceder 255 caracteres.',
            
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            
            'phone.min' => 'El teléfono debe tener al menos 7 caracteres.',
            'phone.max' => 'El teléfono no puede exceder 20 caracteres.',
            
            'address_street.required' => 'La calle es obligatoria.',
            'address_city.required' => 'La ciudad es obligatoria.',
            'address_state.required' => 'El estado es obligatorio.',
            'address_postal_code.min' => 'El código postal debe tener al menos 3 caracteres.',
        ];
    }

    /**
     * Nombres de atributos personalizados
     * 
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'legal_name' => 'razón social',
            'email' => 'email',
            'phone' => 'teléfono',
            'address_street' => 'calle',
            'address_city' => 'ciudad',
            'address_state' => 'estado',
            'address_country' => 'país',
            'address_postal_code' => 'código postal',
            'tax_regime' => 'régimen fiscal',
        ];
    }

    /**
     * Preparar datos para validación
     * 
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // País por defecto
        if (!$this->has('address_country') || empty($this->address_country)) {
            $this->merge([
                'address_country' => 'México',
            ]);
        }
    }
}