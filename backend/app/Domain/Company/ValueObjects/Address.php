<?php

namespace App\Domain\Company\ValueObjects;

/**
 * Value Object: Address
 * 
 * Representa una dirección completa.
 * 
 * PROPÓSITO:
 * - Encapsular toda la información de una dirección
 * - Validar que los campos obligatorios existan
 * - Formatear direcciones de forma consistente
 * 
 * INMUTABILIDAD:
 * Este es un Value Object inmutable. Una vez creado, no puede cambiar.
 * Si necesitas modificar algo, crea una nueva instancia.
 */
final class Address
{
    /**
     * Constructor
     * 
     * @param string $street Calle y número
     * @param string $city Ciudad
     * @param string $state Estado/Provincia/Departamento
     * @param string $country País
     * @param string $postalCode Código postal
     */
    private function __construct(
        private string $street,
        private string $city,
        private string $state,
        private string $country,
        private string $postalCode
    ) {
        $this->validate();
    }

    /**
     * Crear desde componentes individuales
     * 
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $country
     * @param string $postalCode
     * @return self
     */
    public static function create(
        string $street,
        string $city,
        string $state,
        string $country = 'México',
        string $postalCode = ''
    ): self {
        return new self(
            trim($street),
            trim($city),
            trim($state),
            trim($country),
            trim($postalCode)
        );
    }

    /**
     * Crear desde array
     * 
     * @param array $data Array con keys: street, city, state, country, postal_code
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            trim($data['street'] ?? ''),
            trim($data['city'] ?? ''),
            trim($data['state'] ?? ''),
            trim($data['country'] ?? 'México'),
            trim($data['postal_code'] ?? '')
        );
    }

    /**
     * Validar dirección
     * 
     * @throws \InvalidArgumentException Si falta algún campo obligatorio
     */
    private function validate(): void
    {
        if (empty($this->street)) {
            throw new \InvalidArgumentException('La calle es obligatoria');
        }

        if (empty($this->city)) {
            throw new \InvalidArgumentException('La ciudad es obligatoria');
        }

        if (empty($this->state)) {
            throw new \InvalidArgumentException('El estado es obligatorio');
        }

        if (empty($this->country)) {
            throw new \InvalidArgumentException('El país es obligatorio');
        }

        // Código postal es opcional pero si existe debe tener formato válido
        if (!empty($this->postalCode)) {
            // Validar que sea alfanumérico (permite códigos de diferentes países)
            if (!preg_match('/^[A-Z0-9\s-]{3,10}$/i', $this->postalCode)) {
                throw new \InvalidArgumentException(
                    'El código postal debe tener entre 3 y 10 caracteres alfanuméricos'
                );
            }
        }
    }

    /**
     * Obtener calle
     * 
     * @return string
     */
    public function street(): string
    {
        return $this->street;
    }

    /**
     * Obtener ciudad
     * 
     * @return string
     */
    public function city(): string
    {
        return $this->city;
    }

    /**
     * Obtener estado
     * 
     * @return string
     */
    public function state(): string
    {
        return $this->state;
    }

    /**
     * Obtener país
     * 
     * @return string
     */
    public function country(): string
    {
        return $this->country;
    }

    /**
     * Obtener código postal
     * 
     * @return string
     */
    public function postalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * Formatear dirección en una línea
     * 
     * @return string
     */
    public function oneLine(): string
    {
        $parts = [
            $this->street,
            $this->city,
            $this->state,
        ];

        if (!empty($this->postalCode)) {
            $parts[] = 'CP ' . $this->postalCode;
        }

        $parts[] = $this->country;

        return implode(', ', $parts);
    }

    /**
     * Formatear dirección en múltiples líneas
     * 
     * @return string
     */
    public function multiLine(): string
    {
        $lines = [$this->street];
        
        $cityStatePostal = $this->city . ', ' . $this->state;
        if (!empty($this->postalCode)) {
            $cityStatePostal .= ' ' . $this->postalCode;
        }
        
        $lines[] = $cityStatePostal;
        $lines[] = $this->country;

        return implode("\n", $lines);
    }

    /**
     * Convertir a array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postalCode,
        ];
    }

    /**
     * Comparar con otra dirección
     * 
     * @param Address $other
     * @return bool
     */
    public function equals(Address $other): bool
    {
        return $this->street === $other->street
            && $this->city === $other->city
            && $this->state === $other->state
            && $this->country === $other->country
            && $this->postalCode === $other->postalCode;
    }

    /**
     * Crear una nueva dirección cambiando la calle
     * 
     * @param string $newStreet
     * @return self Nueva instancia
     */
    public function withStreet(string $newStreet): self
    {
        return new self(
            $newStreet,
            $this->city,
            $this->state,
            $this->country,
            $this->postalCode
        );
    }

    /**
     * Crear una nueva dirección cambiando la ciudad
     * 
     * @param string $newCity
     * @return self Nueva instancia
     */
    public function withCity(string $newCity): self
    {
        return new self(
            $this->street,
            $newCity,
            $this->state,
            $this->country,
            $this->postalCode
        );
    }

    /**
     * Crear una nueva dirección cambiando el estado
     * 
     * @param string $newState
     * @return self Nueva instancia
     */
    public function withState(string $newState): self
    {
        return new self(
            $this->street,
            $this->city,
            $newState,
            $this->country,
            $this->postalCode
        );
    }

    /**
     * Representación en string
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->oneLine();
    }
}