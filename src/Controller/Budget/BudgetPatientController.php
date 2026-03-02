<?php

namespace App\Controller\Budget;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Person;
use App\Repository\Tenant\PersonRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget/patient', name: 'budget_patient_')]
class BudgetPatientController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {}

    #[Route('/search', name: 'search', methods: ['POST'])]
    public function search(Request $request): Response
    {
        $identification = trim((string) $request->request->get('identification', ''));

        if ($identification === '') {
            return $this->turboStreamReplace('patient-result', $this->renderView('budget/partials/_patient_search_results.html.twig', [
                'people' => [],
                'message' => 'Debe ingresar un RUT o documento.',
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $people = $this->personRepository
            ->createQueryBuilder('p')
            ->where('p.identification = :identification')
            ->setParameter('identification', $identification)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();

        if ($people === []) {
            return $this->turboStreamReplace('patient-result', $this->renderView('budget/partials/_patient_search_results.html.twig', [
                'people' => [],
                'message' => 'Paciente no encontrado',
            ]));
        }

        if (count($people) === 1) {
            return $this->turboStreamReplace('patient-result', $this->renderView('budget/partials/_patient_card.html.twig', [
                'person' => $people[0],
            ]));
        }

        return $this->turboStreamReplace('patient-result', $this->renderView('budget/partials/_patient_search_results.html.twig', [
            'people' => $people,
            'message' => null,
        ]));
    }

    #[Route('/search-advanced', name: 'search_advanced', methods: ['POST'])]
    public function searchAdvanced(Request $request): Response
    {
        $name = trim((string) $request->request->get('name', ''));
        $lastName = trim((string) $request->request->get('lastName', ''));

        $qb = $this->personRepository->createQueryBuilder('p')
            ->orderBy('p.lastName', 'ASC')
            ->addOrderBy('p.name', 'ASC')
            ->setMaxResults(25);

        $this->applyAdvancedFilters($qb, $name, $lastName);

        $people = $qb->getQuery()->getResult();

        return $this->turboStreamReplace('patient-result', $this->renderView('budget/partials/_patient_search_results.html.twig', [
            'people' => $people,
            'message' => $people === [] ? 'Paciente no encontrado' : null,
        ]));
    }

    #[Route('/{personId}/select', name: 'select', methods: ['GET'], requirements: ['personId' => '\d+'])]
    public function select(int $personId): Response
    {
        $person = $this->personRepository->find($personId);
        if ($person === null) {
            throw $this->createNotFoundException('Paciente no encontrado.');
        }

        return $this->render('budget/partials/_patient_card.html.twig', [
            'person' => $person,
        ]);
    }

    private function applyAdvancedFilters(QueryBuilder $qb, string $name, string $lastName): void
    {
        if ($name !== '') {
            $qb->andWhere('LOWER(p.name) LIKE :name')
                ->setParameter('name', '%' . mb_strtolower($name) . '%');
        }

        if ($lastName !== '') {
            $qb->andWhere('LOWER(p.lastName) LIKE :lastName')
                ->setParameter('lastName', '%' . mb_strtolower($lastName) . '%');
        }
    }

    private function turboStreamReplace(string $target, string $html, int $status = Response::HTTP_OK): Response
    {
        $content = sprintf(
            "<turbo-stream action=\"replace\" target=\"%s\"><template>%s</template></turbo-stream>",
            htmlspecialchars($target, ENT_QUOTES),
            $html
        );

        return new Response($content, $status, ['Content-Type' => 'text/vnd.turbo-stream.html']);
    }
}
