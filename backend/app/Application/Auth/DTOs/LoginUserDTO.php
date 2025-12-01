<?php

namespace App\Application\Auth\DTOs;

/**
 * DTO para login de usuario
 * 
 * Transfiere datos de login desde el Controller al Service
 */
final readonly class LoginUserDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false, // "Recordarme" (checkbox)
    ) {
        if (empty(trim($this->email))) {
            throw new \InvalidArgumentException('El email no puede estar vacío');
        }
        
        if (empty($this->password)) {
            throw new \InvalidArgumentException('La contraseña no puede estar vacía');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
            remember: (bool) ($data['remember'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'remember' => $this->remember,
        ];
    }
}