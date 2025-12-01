<?php

namespace App\Domain\Company\ValueObjects;

/**
 * Value Object: TaxId
 * 
 * Representa el identificador fiscal de una empresa (RFC o NIT).
 * 
 * PROPÓSITO:
 * - Validar formato de RFC (México) o NIT (Colombia)
 * - Normalizar formato (uppercase, sin espacios)
 * - Type safety
 * 
 * FORMATOS SOPORTADOS:
 * - RFC Persona Moral (México): 12 caracteres (ej: AAA010101AAA)
 * - RFC Persona Física (México): 13 caracteres (ej: AAAA010101AAA)
 * - NIT (Colombia): 9-10 dígitos + dígito de verificación (ej: 900123456-7)
 */
final class TaxId
{
    private string $value;
    private string $type; // 'RFC' o 'NIT'

    /**
     * Constructor privado
     */
    private function __construct(string $value, string $type)
    {
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * Crear desde un string
     * 
     * @param string $value RFC o NIT
     * @return self
     * @throws \InvalidArgumentException Si no es válido
     */
    public static function fromString(string $value): self
    {
        // Normalizar: uppercase y sin espacios
        $normalized = strtoupper(trim($value));
        $normalized = str_replace([' ', '-'], '', $normalized);

        // Intentar validar como RFC
        if (self::isValidRFC($normalized)) {
            return new self($normalized, 'RFC');
        }

        // Intentar validar como NIT (con guión original)
        $nitWithDash = strtoupper(trim($value));
        if (self::isValidNIT($nitWithDash)) {
            return new self($nitWithDash, 'NIT');
        }

        throw new \InvalidArgumentException(
            'El identificador fiscal no es válido. Debe ser RFC (México) o NIT (Colombia)'
        );
    }

    /**
     * Obtener el valor
     * 
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Obtener el tipo (RFC o NIT)
     * 
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Verificar si es RFC
     * 
     * @return bool
     */
    public function isRFC(): bool
    {
        return $this->type === 'RFC';
    }

    /**
     * Verificar si es NIT
     * 
     * @return bool
     */
    public function isNIT(): bool
    {
        return $this->type === 'NIT';
    }

    /**
     * Comparar con otro TaxId
     * 
     * @param TaxId $other
     * @return bool
     */
    public function equals(TaxId $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Validar formato de RFC (México)
     * 
     * RFC Persona Moral: 12 caracteres
     * - 3 letras (razón social)
     * - 6 dígitos (fecha: YYMMDD)
     * - 3 caracteres (homoclave)
     * Ejemplo: AAA010101AAA
     * 
     * RFC Persona Física: 13 caracteres
     * - 4 letras (apellidos + nombre)
     * - 6 dígitos (fecha: YYMMDD)
     * - 3 caracteres (homoclave)
     * Ejemplo: AAAA010101AAA
     * 
     * @param string $value
     * @return bool
     */
    private static function isValidRFC(string $value): bool
    {
        // Longitud debe ser 12 o 13
        $length = strlen($value);
        if ($length !== 12 && $length !== 13) {
            return false;
        }

        // Patrón RFC Persona Moral (12 caracteres)
        $patternMoral = '/^[A-ZÑ&]{3}\d{6}[A-Z0-9]{3}$/';
        
        // Patrón RFC Persona Física (13 caracteres)
        $patternFisica = '/^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/';

        if ($length === 12) {
            return preg_match($patternMoral, $value) === 1;
        } else {
            return preg_match($patternFisica, $value) === 1;
        }
    }

    /**
     * Validar formato de NIT (Colombia)
     * 
     * Formato: 9-10 dígitos + guión + 1 dígito verificador
     * Ejemplo: 900123456-7
     * 
     * @param string $value
     * @return bool
     */
    private static function isValidNIT(string $value): bool
    {
        // Debe tener formato: números-dígito
        if (!preg_match('/^\d{9,10}-\d$/', $value)) {
            return false;
        }

        // Separar número base y dígito verificador
        $parts = explode('-', $value);
        $number = $parts[0];
        $checkDigit = (int) $parts[1];

        // Calcular dígito verificador
        $calculated = self::calculateNITCheckDigit($number);

        return $calculated === $checkDigit;
    }

    /**
     * Calcular dígito verificador de NIT (algoritmo colombiano)
     * 
     * @param string $number Número base del NIT
     * @return int Dígito verificador
     */
    private static function calculateNITCheckDigit(string $number): int
    {
        // Pesos para cálculo (de derecha a izquierda)
        $weights = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        
        $sum = 0;
        $length = strlen($number);
        
        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $number[$length - 1 - $i];
            $weight = $weights[$i];
            $sum += $digit * $weight;
        }
        
        $remainder = $sum % 11;
        
        if ($remainder === 0 || $remainder === 1) {
            return $remainder;
        }
        
        return 11 - $remainder;
    }

    /**
     * Formatear para mostrar
     * 
     * @return string
     */
    public function formatted(): string
    {
        if ($this->isRFC()) {
            // RFC: insertar guiones para legibilidad
            // AAA010101AAA -> AAA-010101-AAA
            if (strlen($this->value) === 12) {
                return substr($this->value, 0, 3) . '-' . 
                       substr($this->value, 3, 6) . '-' . 
                       substr($this->value, 9, 3);
            } else {
                return substr($this->value, 0, 4) . '-' . 
                       substr($this->value, 4, 6) . '-' . 
                       substr($this->value, 10, 3);
            }
        }
        
        // NIT ya tiene formato con guión
        return $this->value;
    }

    /**
     * Representación en string
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}