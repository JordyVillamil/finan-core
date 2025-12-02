<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: CreateCompanyRequest
 * 
 * Valida los datos para crear una nueva empresa.
 * 
 * RESPONSABILIDADES:
 * - Validar formato de datos
 * - Validar reglas de negocio (RFC único, etc.)
 * - Mensajes de error personalizados
 */
class CreateCompanyRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para hacer esta petición
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        // Verificar que el usuario tenga el permiso 'create-companies'
        return $this->user()->can('create-companies');
    }

    /**
     * Reglas de validación
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Información básica
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            
            'legal_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            
            'tax_id' => [
                'required',
                'string',
                'min:10',
                'max:50',
                // Verificar que sea único en la tabla companies
                Rule::unique('companies', 'tax_id')->whereNull('deleted_at'),
            ],
            
            'email' => [
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
                'required',
                'string',
                'max:255',
            ],
            
            'address_city' => [
                'required',
                'string',
                'max:100',
            ],
            
            'address_state' => [
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
            
            'invoice_series' => [
                'nullable',
                'string',
                'regex:/^[A-Z0-9]{1,10}$/',
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
            // Nombre
            'name.required' => 'El nombre de la empresa es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            
            // Razón social
            'legal_name.required' => 'La razón social es obligatoria.',
            'legal_name.min' => 'La razón social debe tener al menos 2 caracteres.',
            'legal_name.max' => 'La razón social no puede exceder 255 caracteres.',
            
            // RFC/NIT
            'tax_id.required' => 'El RFC/NIT es obligatorio.',
            'tax_id.unique' => 'El RFC/NIT ya está registrado en el sistema.',
            'tax_id.min' => 'El RFC/NIT debe tener al menos 10 caracteres.',
            'tax_id.max' => 'El RFC/NIT no puede exceder 50 caracteres.',
            
            // Email
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            
            // Teléfono
            'phone.min' => 'El teléfono debe tener al menos 7 caracteres.',
            'phone.max' => 'El teléfono no puede exceder 20 caracteres.',
            
            // Dirección
            'address_street.required' => 'La calle es obligatoria.',
            'address_city.required' => 'La ciudad es obligatoria.',
            'address_state.required' => 'El estado es obligatorio.',
            'address_postal_code.min' => 'El código postal debe tener al menos 3 caracteres.',
            
            // Serie de facturación
            'invoice_series.regex' => 'La serie de facturación debe ser alfanumérica mayúscula (1-10 caracteres).',
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
            'tax_id' => 'RFC/NIT',
            'email' => 'email',
            'phone' => 'teléfono',
            'address_street' => 'calle',
            'address_city' => 'ciudad',
            'address_state' => 'estado',
            'address_country' => 'país',
            'address_postal_code' => 'código postal',
            'tax_regime' => 'régimen fiscal',
            'invoice_series' => 'serie de facturación',
        ];
    }

    /**
     * Preparar datos para validación
     * 
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Normalizar RFC/NIT: uppercase y sin espacios
        if ($this->has('tax_id')) {
            $this->merge([
                'tax_id' => strtoupper(str_replace(' ', '', $this->tax_id)),
            ]);
        }

        // Serie de facturación por defecto
        if (!$this->has('invoice_series') || empty($this->invoice_series)) {
            $this->merge([
                'invoice_series' => 'A',
            ]);
        }

        // País por defecto
        if (!$this->has('address_country') || empty($this->address_country)) {
            $this->merge([
                'address_country' => 'México',
            ]);
        }
    }
}