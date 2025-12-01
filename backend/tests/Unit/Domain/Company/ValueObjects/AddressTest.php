<?php

namespace Tests\Unit\Domain\Company\ValueObjects;

use PHPUnit\Framework\TestCase;
use App\Domain\Company\ValueObjects\Address;

/**
 * Tests para Address Value Object
 * 
 * PROPÓSITO:
 * Verificar que Address:
 * - Se crea correctamente desde componentes y array
 * - Valida campos obligatorios
 * - Formatea direcciones correctamente
 * - Es inmutable (with methods crean nuevas instancias)
 */
class AddressTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_address_with_all_fields(): void
    {
        // Arrange & Act
        $address = Address::create(
            'Calle Principal 123',
            'Ciudad de México',
            'CDMX',
            'México',
            '01000'
        );

        // Assert
        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals('Calle Principal 123', $address->street());
        $this->assertEquals('Ciudad de México', $address->city());
        $this->assertEquals('CDMX', $address->state());
        $this->assertEquals('México', $address->country());
        $this->assertEquals('01000', $address->postalCode());
    }

    /**
     * @test
     */
    public function it_creates_address_with_default_country(): void
    {
        // Arrange & Act
        $address = Address::create(
            'Calle 50 #20-30',
            'Bogotá',
            'Cundinamarca'
        );

        // Assert
        $this->assertEquals('México', $address->country());
    }

    /**
     * @test
     */
    public function it_creates_address_from_array(): void
    {
        // Arrange
        $data = [
            'street' => 'Av. Reforma 500',
            'city' => 'Guadalajara',
            'state' => 'Jalisco',
            'country' => 'México',
            'postal_code' => '44100',
        ];

        // Act
        $address = Address::fromArray($data);

        // Assert
        $this->assertEquals('Av. Reforma 500', $address->street());
        $this->assertEquals('Guadalajara', $address->city());
        $this->assertEquals('Jalisco', $address->state());
        $this->assertEquals('44100', $address->postalCode());
    }

    /**
     * @test
     */
    public function it_trims_whitespace(): void
    {
        // Arrange & Act
        $address = Address::create(
            '  Calle 123  ',
            '  Ciudad  ',
            '  Estado  ',
            '  País  ',
            '  12345  '
        );

        // Assert
        $this->assertEquals('Calle 123', $address->street());
        $this->assertEquals('Ciudad', $address->city());
        $this->assertEquals('Estado', $address->state());
        $this->assertEquals('País', $address->country());
        $this->assertEquals('12345', $address->postalCode());
    }

    /**
     * @test
     */
    public function it_rejects_empty_street(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La calle es obligatoria');

        // Act
        Address::create('', 'Ciudad', 'Estado');
    }

    /**
     * @test
     */
    public function it_rejects_empty_city(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La ciudad es obligatoria');

        // Act
        Address::create('Calle 123', '', 'Estado');
    }

    /**
     * @test
     */
    public function it_rejects_empty_state(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El estado es obligatorio');

        // Act
        Address::create('Calle 123', 'Ciudad', '');
    }

    /**
     * @test
     */
    public function it_rejects_empty_country(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El país es obligatorio');

        // Act
        Address::create('Calle 123', 'Ciudad', 'Estado', '');
    }

    /**
     * @test
     */
    public function it_accepts_empty_postal_code(): void
    {
        // Arrange & Act
        $address = Address::create(
            'Calle 123',
            'Ciudad',
            'Estado',
            'País',
            '' // Código postal vacío
        );

        // Assert
        $this->assertEquals('', $address->postalCode());
    }

    /**
     * @test
     */
    public function it_rejects_invalid_postal_code(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El código postal debe tener entre 3 y 10 caracteres alfanuméricos');

        // Act
        Address::create('Calle 123', 'Ciudad', 'Estado', 'País', 'AB'); // Muy corto
    }

    /**
     * @test
     */
    public function it_formats_address_in_one_line(): void
    {
        // Arrange
        $address = Address::create(
            'Calle Principal 123',
            'CDMX',
            'CDMX',
            'México',
            '01000'
        );

        // Act
        $oneLine = $address->oneLine();

        // Assert
        $this->assertEquals(
            'Calle Principal 123, CDMX, CDMX, CP 01000, México',
            $oneLine
        );
    }

    /**
     * @test
     */
    public function it_formats_address_in_one_line_without_postal_code(): void
    {
        // Arrange
        $address = Address::create(
            'Calle Principal 123',
            'CDMX',
            'CDMX',
            'México',
            ''
        );

        // Act
        $oneLine = $address->oneLine();

        // Assert
        $this->assertEquals(
            'Calle Principal 123, CDMX, CDMX, México',
            $oneLine
        );
    }

    /**
     * @test
     */
    public function it_formats_address_in_multiple_lines(): void
    {
        // Arrange
        $address = Address::create(
            'Calle Principal 123',
            'CDMX',
            'CDMX',
            'México',
            '01000'
        );

        // Act
        $multiLine = $address->multiLine();

        // Assert
        $expected = "Calle Principal 123\nCDMX, CDMX 01000\nMéxico";
        $this->assertEquals($expected, $multiLine);
    }

    /**
     * @test
     */
    public function it_converts_to_array(): void
    {
        // Arrange
        $address = Address::create(
            'Calle 123',
            'Ciudad',
            'Estado',
            'País',
            '12345'
        );

        // Act
        $array = $address->toArray();

        // Assert
        $this->assertEquals([
            'street' => 'Calle 123',
            'city' => 'Ciudad',
            'state' => 'Estado',
            'country' => 'País',
            'postal_code' => '12345',
        ], $array);
    }

    /**
     * @test
     */
    public function it_compares_equal_addresses(): void
    {
        // Arrange
        $address1 = Address::create('Calle 123', 'Ciudad', 'Estado', 'País', '12345');
        $address2 = Address::create('Calle 123', 'Ciudad', 'Estado', 'País', '12345');

        // Act & Assert
        $this->assertTrue($address1->equals($address2));
    }

    /**
     * @test
     */
    public function it_compares_different_addresses(): void
    {
        // Arrange
        $address1 = Address::create('Calle 123', 'Ciudad', 'Estado', 'País', '12345');
        $address2 = Address::create('Calle 456', 'Ciudad', 'Estado', 'País', '12345');

        // Act & Assert
        $this->assertFalse($address1->equals($address2));
    }

    /**
     * @test
     */
    public function it_creates_new_instance_with_different_street(): void
    {
        // Arrange
        $original = Address::create('Calle 123', 'Ciudad', 'Estado', 'País', '12345');

        // Act
        $modified = $original->withStreet('Calle 456');

        // Assert
        $this->assertEquals('Calle 123', $original->street()); // Original sin cambios
        $this->assertEquals('Calle 456', $modified->street()); // Nueva instancia modificada
        $this->assertNotSame($original, $modified); // Son instancias diferentes
    }

    /**
     * @test
     */
    public function it_creates_new_instance_with_different_city(): void
    {
        // Arrange
        $original = Address::create('Calle 123', 'Ciudad A', 'Estado', 'País', '12345');

        // Act
        $modified = $original->withCity('Ciudad B');

        // Assert
        $this->assertEquals('Ciudad A', $original->city());
        $this->assertEquals('Ciudad B', $modified->city());
        $this->assertNotSame($original, $modified);
    }

    /**
     * @test
     */
    public function it_creates_new_instance_with_different_state(): void
    {
        // Arrange
        $original = Address::create('Calle 123', 'Ciudad', 'Estado A', 'País', '12345');

        // Act
        $modified = $original->withState('Estado B');

        // Assert
        $this->assertEquals('Estado A', $original->state());
        $this->assertEquals('Estado B', $modified->state());
        $this->assertNotSame($original, $modified);
    }

    /**
     * @test
     */
    public function it_converts_to_string(): void
    {
        // Arrange
        $address = Address::create('Calle 123', 'Ciudad', 'Estado', 'País', '12345');

        // Act & Assert
        $this->assertEquals($address->oneLine(), (string) $address);
    }
}