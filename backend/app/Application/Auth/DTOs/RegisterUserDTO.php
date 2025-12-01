<?php

namespace App\Application\Auth\DTOs;

/**
 * DTO para registrar un nuevo usuario
 * 
 * PROPÓSITO:
 * Transferir datos desde la capa de presentación (Controller)
 * hacia la capa de aplicación (Service).
 * 
 * CARACTERÍSTICAS:
 * - Inmutable (readonly)
 * - Solo datos, sin lógica
 * - Validación de tipos en el constructor
 * 
 * USO:
 * $dto = new RegisterUserDTO(
 *     name: 'Juan Pérez',
 *     email: 'juan@example.com',
 *     password: 'password123'
 * );
 */
final readonly class RegisterUserDTO
{
    /**
     * Constructor
     * 
     * @param string $name Nombre completo del usuario
     * @param string $email Email del usuario
     * @param string $password Contraseña en texto plano
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {
        // Validación básica (solo tipos y vacíos)
        // La validación de REGLAS DE NEGOCIO va en el dominio
        
        if (empty(trim($this->name))) {
            throw new \InvalidArgumentException('El nombre no puede estar vacío');
        }
        
        if (empty(trim($this->email))) {
            throw new \InvalidArgumentException('El email no puede estar vacío');
        }
        
        if (empty($this->password)) {
            throw new \InvalidArgumentException('La contraseña no puede estar vacía');
        }
    }

    /**
     * Crear DTO desde un array
     * 
     * Útil cuando recibes datos de un Request HTTP
     * 
     * @param array $data Array con los datos
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
        );
    }

    /**
     * Convertir a array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}