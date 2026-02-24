<?php

namespace App\Controller\Revenue\CashRegister;

use App\Controller\AbstractTenantAwareController;
use App\Repository\Tenant\PatientAccountRepository;
use App\Repository\Tenant\PatientRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * PatientSearchController
 *
 * Provee búsqueda de pacientes para el flujo de caja:
 *   - JSON autocomplete (find)
 *   - Turbo Frame formulario de búsqueda (search)
 *   - Turbo Frame contexto del paciente seleccionado (context)
 *
 * Legacy: BuscarPacienteController / BuscarPacienteAction
 */
#[Route('/revenue/cash-register/patient', name: 'app_revenue_cash_register_patient_')]
class PatientSearchController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly PatientRepository $patientRepository,
        private readonly PatientAccountRepository $patientAccountRepository,
    ) {}

    // -------------------------------------------------------------------------
    // Formulario de búsqueda
    // -------------------------------------------------------------------------

    /**
     * Renderiza el panel de búsqueda de pacientes.
     *
     * Turbo Frame: patient-search
     * Legacy: n/a (nuevo)
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(): Response
    {
        return $this->render('revenue/cash-register/_patient_search.html.twig');
    }

    // -------------------------------------------------------------------------
    // Autocomplete JSON
    // -------------------------------------------------------------------------

    /**
     * Devuelve hasta 10 pacientes que coincidan con el parámetro ?q=
     * buscando por RUT, nombre o apellido (case-insensitive).
     *
     * Respuesta: [{id, fullName, rut, birthDate, patientAccountId, contextUrl}]
     *
     * Legacy: n/a (nuevo)
     */
    #[Route('/find', name: 'find', methods: ['GET'])]
    public function find(Request $request): JsonResponse
    {
        $query = trim($request->query->getString('q', ''));

        // Parámetros de búsqueda avanzada (§3a)
        $nombre          = trim($request->query->getString('nombre', ''));
        $apellidoPaterno = trim($request->query->getString('apellido_paterno', ''));
        $apellidoMaterno = trim($request->query->getString('apellido_materno', ''));
        $matchType       = $request->query->getString('match_type', 'exact');

        $isAdvanced = $nombre !== '' || $apellidoPaterno !== '' || $apellidoMaterno !== '';

        if ($isAdvanced) {
            $patients = $this->patientRepository->searchByAdvanced(
                $nombre, $apellidoPaterno, $apellidoMaterno, $matchType, 10
            );
        } elseif (mb_strlen($query) >= 2) {
            $patients = $this->patientRepository->searchByQuery($query, 10);
        } else {
            return $this->json([]);
        }

        $results = [];
        foreach ($patients as $patient) {
            $person = $patient->getPerson();
            if ($person === null) {
                continue;
            }

            $patientAccount = $this->patientAccountRepository->findOneBy(['patient' => $patient]);

            $results[] = [
                'id'               => $patient->getId(),
                'fullName'         => trim($person->getName() . ' ' . $person->getLastName()),
                'rut'              => $person->getIdentification(),
                'birthDate'        => $person->getBirthDateAt()?->format('d/m/Y'),
                'patientAccountId' => $patientAccount?->getId(),
                'contextUrl'       => $this->generateUrl(
                    'app_revenue_cash_register_patient_context',
                    ['id' => $patient->getId()]
                ),
            ];
        }

        return $this->json($results);
    }

    // -------------------------------------------------------------------------
    // Contexto del paciente seleccionado
    // -------------------------------------------------------------------------

    /**
     * Carga el contexto completo del paciente: resumen demográfico,
     * campos hidden con IDs necesarios para el formulario de cobro
     * y sub-frames para cuenta, financiador y garantía (Fase E).
     *
     * Turbo Frame: patient-context
     * Legacy: n/a (nuevo)
     */
    #[Route('/{id}/context', name: 'context', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function context(int $id): Response
    {
        $patient = $this->patientRepository->find($id);
        if ($patient === null) {
            throw $this->createNotFoundException('Paciente no encontrado.');
        }

        $patientAccount = $this->patientAccountRepository->findOneBy(['patient' => $patient]);

        return $this->render('revenue/cash-register/_patient_context.html.twig', [
            'patient'        => $patient,
            'patientAccount' => $patientAccount,
        ]);
    }
}
