<?php

namespace App\Application\Company\Services;

use App\Application\Company\DTOs\CompanyResponseDTO;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\Repositories\CompanyRepositoryInterface;
use App\Domain\Company\Exceptions\CompanyNotFoundException;
use App\Domain\Shared\ValueObjects\Email;
use App\Domain\Company\ValueObjects\PhoneNumber;

/**
 * Service: UpdateCompanyService
 * 
 * Caso de uso: Actualizar información de empresa existente
 * 
 * RESPONSABILIDADES:
 * - Validar datos de entrada
 * - Delegar actualización al repositorio
 * - Retornar respuesta
 * 
 * SOPORTA:
 * - Actualización completa (todos los campos)
 * - Actualización parcial (solo campos enviados)
 * 
 * FLUJO:
 * 1. Validar Value Objects si están presentes
 * 2. Actualizar campos en repositorio
 * 3. Retornar respuesta con empresa actualizada
 */
class UpdateCompanyService
{
    /**
     * Constructor
     */
    public function __construct(
        private CompanyRepositoryInterface $companyRepository
    ) {}

    /**
     * Ejecutar caso de uso
     * 
     * @param int $id ID de la empresa a actualizar
     * @param array<string, mixed> $data Campos a actualizar (solo los enviados)
     * @return CompanyResponseDTO
     * @throws CompanyNotFoundException Si la empresa no existe
     * @throws \InvalidArgumentException Si algún dato es inválido
     */
    public function execute(int $id, array $data): CompanyResponseDTO
    {
        // 1. Validar Value Objects si están presentes
        $this->validateInputData($data);

        // 2. Actualizar campos en repositorio
        $companyId = CompanyId::fromInt($id);
        $company = $this->companyRepository->update($companyId, $data);

        // 3. Retornar respuesta
        return CompanyResponseDTO::fromEntity($company);
    }

    /**
     * Validar datos de entrada
     * 
     * Valida los Value Objects solo si están presentes en el array.
     * Esto permite actualizaciones parciales.
     * 
     * @param array<string, mixed> $data
     * @throws \InvalidArgumentException
     */
    private function validateInputData(array $data): void
    {
        // Validar email si está presente
        if (isset($data['email'])) {
            Email::fromString($data['email']);
        }

        // Validar teléfono si está presente
        if (isset($data['phone']) && $data['phone'] !== null) {
            PhoneNumber::fromString($data['phone']);
        }

        // Validar nombre
        if (isset($data['name'])) {
            $name = trim($data['name']);
            if (empty($name)) {
                throw new \InvalidArgumentException('El nombre de la empresa no puede estar vacío');
            }
            if (strlen($name) < 2 || strlen($name) > 255) {
                throw new \InvalidArgumentException('El nombre debe tener entre 2 y 255 caracteres');
            }
        }

        // Validar razón social
        if (isset($data['legal_name'])) {
            $legalName = trim($data['legal_name']);
            if (empty($legalName)) {
                throw new \InvalidArgumentException('La razón social no puede estar vacía');
            }
            if (strlen($legalName) < 2 || strlen($legalName) > 255) {
                throw new \InvalidArgumentException('La razón social debe tener entre 2 y 255 caracteres');
            }
        }
    }
}