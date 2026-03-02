<?php

namespace App\Controller\Budget;

use App\Controller\AbstractTenantAwareController;
use App\Repository\Tenant\BudgetRepository;
use App\Repository\Tenant\PersonRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget', name: 'budget_')]
class BudgetController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly BudgetRepository $budgetRepository,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $searchForm = $this->createFormBuilder(null, [
            'method' => 'POST',
            'action' => $this->generateUrl('budget_patient_search'),
        ])
            ->add('identification', TextType::class, [
                'label' => 'RUT o documento',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ingrese RUT o documento',
                    'autocomplete' => 'off',
                ],
            ])
            ->getForm();

        return $this->render('budget/index.html.twig', [
            'searchForm' => $searchForm,
        ]);
    }

    #[Route('/patient/{personId}/budgets', name: 'list_for_patient', methods: ['GET'], requirements: ['personId' => '\d+'])]
    public function listForPatient(int $personId): Response
    {
        $person = $this->personRepository->find($personId);
        if ($person === null) {
            throw $this->createNotFoundException('Paciente no encontrado.');
        }

        $budgets = $this->budgetRepository
            ->createQueryBuilder('b')
            ->leftJoin('b.payer', 'payer')
            ->leftJoin('b.agreement', 'agreement')
            ->leftJoin('b.insurancePlan', 'plan')
            ->leftJoin('b.professional', 'professional')
            ->addSelect('payer', 'agreement', 'plan', 'professional')
            ->where('b.person = :person')
            ->setParameter('person', $person)
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $today = new \DateTimeImmutable('today');

        return $this->render('budget/partials/_budget_list.html.twig', [
            'person' => $person,
            'budgets' => $budgets,
            'today' => $today,
        ]);
    }

}
