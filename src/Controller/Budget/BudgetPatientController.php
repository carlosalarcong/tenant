<?php

namespace App\Controller\Budget;

use App\Repository\Tenant\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget/patient')]
class BudgetPatientController extends AbstractController
{
    public function __construct(
        private PersonRepository $personRepository,
    ) {}

    #[Route('/search', name: 'app_budget_patient_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $q = $request->query->get('q', '');

        if (strlen($q) < 2) {
            return $this->render('budget/_patient_search_results.html.twig', [
                'persons' => [],
                'query'   => $q,
            ]);
        }

        $results = $this->personRepository->createQueryBuilder('p')
            ->where('p.rut LIKE :q OR p.firstName LIKE :q OR p.lastName LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('p.lastName', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->render('budget/_patient_search_results.html.twig', [
            'persons' => $results,
            'query'   => $q,
        ]);
    }
}
