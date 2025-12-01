<?php

namespace App\Domain\Company\ValueObjects;

/**
 * Value Object: PhoneNumber
 * 
 * Representa un número telefónico.
 * 
 * PROPÓSITO:
 * - Normalizar formato de teléfonos
 * - Validar formato básico
 * - Soportar formato internacional
 * 
 * FORMATOS ACEPTADOS:
 * - +52 55 1234 5678 (internacional)
 * - 55 1234 5678 (nacional)
 * - 5512345678 (sin espacios)
 * - (55) 1234-5678 (con paréntesis y guión)
 */
final class PhoneNumber
{
    private string $value;
    private ?string $countryCode;
    private string $number;

    /**
     * Constructor privado
     */
    private function __construct(string $value, ?string $countryCode, string $number)
    {
        $this->value = $value;
        $this->countryCode = $countryCode;
        $this->number = $number;
    }

    /**
     * Crear desde string
     * 
     * @param string $value Número telefónico
     * @return self
     * @throws \InvalidArgumentException Si el formato no es válido
     */
    public static function fromString(string $value): self
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('El número telefónico no puede estar vacío');
        }

        // Limpiar: remover espacios, paréntesis, guiones
        $cleaned = preg_replace('/[\s\(\)\-]/', '', trim($value));

        // Detectar código de país
        $countryCode = null;
        $number = $cleaned;

        if (str_starts_with($cleaned, '+')) {
            // Formato internacional: +52 5512345678
            $cleaned = substr($cleaned, 1); // Remover +
            
            // Intentar extraer código de país probando diferentes longitudes
            // Priorizar códigos de 1-2 dígitos para números largos
            $countryCode = null;
            $number = $cleaned;
            
            // Probar código de 1 dígito (ej: +1 para USA)
            if (preg_match('/^(1)(\d{10})$/', $cleaned, $matches)) {
                $countryCode = $matches[1];
                $number = $matches[2];
            }
            // Probar código de 2 dígitos (ej: +52 para México, +57 para Colombia)
            elseif (preg_match('/^(\d{2})(\d{7,12})$/', $cleaned, $matches)) {
                $countryCode = $matches[1];
                $number = $matches[2];
            }
            // Probar código de 3 dígitos (ej: +502 para Guatemala)
            elseif (preg_match('/^(\d{3})(\d{7,11})$/', $cleaned, $matches)) {
                $countryCode = $matches[1];
                $number = $matches[2];
            }
            // Si no coincide ningún patrón, tratar todo como número
        }

        // Validar longitud (mínimo 7 dígitos, máximo 15)
        $length = strlen($number);
        if ($length < 7 || $length > 15) {
            throw new \InvalidArgumentException(
                'El número telefónico debe tener entre 7 y 15 dígitos'
            );
        }

        // Validar que solo contenga dígitos
        if (!ctype_digit($number)) {
            throw new \InvalidArgumentException(
                'El número telefónico solo puede contener dígitos'
            );
        }

        return new self($value, $countryCode, $number);
    }

    /**
     * Obtener valor original
     * 
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Obtener código de país
     * 
     * @return string|null
     */
    public function countryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * Obtener número sin código de país
     * 
     * @return string
     */
    public function number(): string
    {
        return $this->number;
    }

    /**
     * Formatear en formato internacional
     * 
     * @return string
     */
    public function international(): string
    {
        if ($this->countryCode) {
            return '+' . $this->countryCode . ' ' . $this->formatNumber();
        }

        // Sin código de país, asumir México (+52)
        return '+52 ' . $this->formatNumber();
    }

    /**
     * Formatear en formato nacional
     * 
     * @return string
     */
    public function national(): string
    {
        return $this->formatNumber();
    }

    /**
     * Formatear número con espacios para legibilidad
     * 
     * Para México (10 dígitos): 55 1234 5678
     * Para otros: grupos de 3-4 dígitos
     * 
     * @return string
     */
    private function formatNumber(): string
    {
        $length = strlen($this->number);

        // Formato México (10 dígitos): 55 1234 5678
        if ($length === 10) {
            return substr($this->number, 0, 2) . ' ' . 
                   substr($this->number, 2, 4) . ' ' . 
                   substr($this->number, 6, 4);
        }

        // Formato general: grupos de 3 dígitos
        return implode(' ', str_split($this->number, 3));
    }

    /**
     * Comparar con otro PhoneNumber
     * 
     * @param PhoneNumber $other
     * @return bool
     */
    public function equals(PhoneNumber $other): bool
    {
        // Comparar solo los números (sin código de país)
        return $this->number === $other->number;
    }

    /**
     * Representación en string
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->national();
    }
}