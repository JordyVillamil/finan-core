<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\CreateCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Application\Company\Services\CreateCompanyService;
use App\Application\Company\Services\UpdateCompanyService;
use App\Application\Company\Services\ListCompaniesService;
use App\Application\Company\Services\GetCompanyService;
use App\Application\Company\Services\DeleteCompanyService;
use App\Application\Company\Services\ActivateCompanyService;
use App\Application\Company\Services\DeactivateCompanyService;
use App\Application\Company\DTOs\CreateCompanyDTO;
use App\Application\Company\DTOs\UpdateCompanyDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller: CompanyController
 * 
 * Maneja las peticiones HTTP de la API de empresas.
 * 
 * RESPONSABILIDADES:
 * - Recibir peticiones HTTP
 * - Validar datos (vía Form Requests)
 * - Delegar a Services (Application Layer)
 * - Retornar respuestas JSON
 * 
 * PATRÓN: Thin Controllers
 * El controller es delgado, toda la lógica está en los Services.
 */
class CompanyController extends Controller
{
    /**
     * Constructor
     * 
     * Inyección de dependencias de todos los servicios
     */
    public function __construct(
        private CreateCompanyService $createCompanyService,
        private UpdateCompanyService $updateCompanyService,
        private ListCompaniesService $listCompaniesService,
        private GetCompanyService $getCompanyService,
        private DeleteCompanyService $deleteCompanyService,
        private ActivateCompanyService $activateCompanyService,
        private DeactivateCompanyService $deactivateCompanyService,
    ) {}

    /**
     * GET /api/companies
     * 
     * Listar empresas con paginación
     * 
     * Query params:
     * - page: int (default: 1)
     * - per_page: int (default: 15)
     * - only_active: bool (default: null)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Obtener parámetros de query
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('per_page', 15);
            $onlyActive = $request->query('only_active');
            
            // Convertir string a boolean
            if ($onlyActive === 'true') {
                $onlyActive = true;
            } elseif ($onlyActive === 'false') {
                $onlyActive = false;
            } else {
                $onlyActive = null;
            }

            // Ejecutar servicio
            $result = $this->listCompaniesService->execute($page, $perPage, $onlyActive);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al listar empresas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/companies
     * 
     * Crear nueva empresa
     * 
     * @param CreateCompanyRequest $request
     * @return JsonResponse
     */
    public function store(CreateCompanyRequest $request): JsonResponse
    {
        try {
            // Crear DTO desde datos validados
            $dto = CreateCompanyDTO::fromArray($request->validated());

            // Ejecutar servicio
            $company = $this->createCompanyService->execute($dto);

            return response()->json([
                'message' => 'Empresa creada exitosamente',
                'data' => $company->toArray(),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Datos inválidos',
                'error' => $e->getMessage(),
            ], 400);
        } catch (\DomainException $e) {
            // Ej: DuplicateTaxIdException
            return response()->json([
                'message' => 'Error de negocio',
                'error' => $e->getMessage(),
            ], 409); // 409 Conflict
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear empresa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/companies/{id}
     * 
     * Obtener detalle de una empresa
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $company = $this->getCompanyService->execute($id);

            return response()->json([
                'data' => $company->toArray(),
            ], 200);
        } catch (\DomainException $e) {
            // CompanyNotFoundException
            return response()->json([
                'message' => 'Empresa no encontrada',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener empresa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/companies/{id}
     * 
     * Actualizar empresa
     * 
     * @param UpdateCompanyRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCompanyRequest $request, int $id): JsonResponse
    {
        try {
            // Crear DTO desde datos validados
            $data = $request->validated();
            $data['id'] = $id; // Agregar ID
            
            $dto = UpdateCompanyDTO::fromArray($data);

            // Ejecutar servicio
            $company = $this->updateCompanyService->execute($dto);

            return response()->json([
                'message' => 'Empresa actualizada exitosamente',
                'data' => $company->toArray(),
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Datos inválidos',
                'error' => $e->getMessage(),
            ], 400);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => 'Error de negocio',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar empresa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/companies/{id}
     * 
     * Eliminar empresa (soft delete)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->deleteCompanyService->execute($id);

            return response()->json([
                'message' => 'Empresa eliminada exitosamente',
            ], 200);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => 'Empresa no encontrada',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar empresa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/companies/{id}/activate
     * 
     * Activar empresa
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function activate(int $id): JsonResponse
    {
        try {
            $company = $this->activateCompanyService->execute($id);

            return response()->json([
                'message' => 'Empresa activada exitosamente',
                'data' => $company->toArray(),
            ], 200);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => 'Empresa no encontrada',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al activar empresa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/companies/{id}/deactivate
     * 
     * Desactivar empresa
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function deactivate(int $id): JsonResponse
    {
        try {
            $company = $this->deactivateCompanyService->execute($id);

            return response()->json([
                'message' => 'Empresa desactivada exitosamente',
                'data' => $company->toArray(),
            ], 200);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => 'Empresa no encontrada',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al desactivar empresa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}