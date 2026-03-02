<?php

namespace App\Controller\Budget;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Budget;
use App\Entity\Tenant\BudgetObservation;
use App\Entity\Tenant\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget', name: 'budget_')]
class BudgetObservationController extends AbstractTenantAwareController
{
    public function __construct(
        #[Autowire(service: 'doctrine.orm.tenant_entity_manager')]
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/{id}/observations', name: 'observations', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function observations(int $id): Response
    {
        $budget = $this->findBudget($id);
        $observations = $this->findObservations($budget);

        return $this->render('budget/partials/_observations.html.twig', [
            'budget' => $budget,
            'observations' => $observations,
        ]);
    }

    #[Route('/{id}/observations', name: 'save_observation', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function saveObservation(int $id, Request $request): Response
    {
        $budget = $this->findBudget($id);
        $text = trim((string) $request->request->get('observation', ''));

        if ($text === '') {
            return $this->renderObservationStream($budget, $this->findObservations($budget), 'La observación no puede estar vacía.');
        }

        if (mb_strlen($text) > 2000) {
            return $this->renderObservationStream($budget, $this->findObservations($budget), 'La observación no puede exceder 2000 caracteres.');
        }

        $observation = (new BudgetObservation())
            ->setBudget($budget)
            ->setMember($this->resolveCurrentMember())
            ->setObservation($text)
            ->setIsEmailSent(false)
            ->setCreatedAt(new \DateTime());

        $this->em->persist($observation);
        $this->em->flush();

        return $this->renderObservationStream($budget, $this->findObservations($budget));
    }

    private function findBudget(int $id): Budget
    {
        $budget = $this->em->getRepository(Budget::class)->find($id);
        if (!$budget instanceof Budget) {
            throw $this->createNotFoundException('Presupuesto no encontrado.');
        }

        return $budget;
    }

    /**
     * @return list<BudgetObservation>
     */
    private function findObservations(Budget $budget): array
    {
        return $this->em->getRepository(BudgetObservation::class)
            ->createQueryBuilder('bo')
            ->leftJoin('bo.member', 'member')
            ->addSelect('member')
            ->where('bo.budget = :budget')
            ->setParameter('budget', $budget)
            ->orderBy('bo.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param list<BudgetObservation> $observations
     */
    private function renderObservationStream(Budget $budget, array $observations, ?string $error = null): Response
    {
        $html = $this->renderView('budget/partials/_observations.html.twig', [
            'budget' => $budget,
            'observations' => $observations,
            'error' => $error,
        ]);

        $stream = sprintf(
            '<turbo-stream action="replace" target="budget-observations"><template>%s</template></turbo-stream>',
            $html
        );

        return new Response($stream, Response::HTTP_OK, [
            'Content-Type' => 'text/vnd.turbo-stream.html',
        ]);
    }

    private function resolveCurrentMember(): Member
    {
        $user = $this->getUser();
        if (!$user instanceof Member) {
            throw $this->createAccessDeniedException('Usuario no autenticado.');
        }

        return $user;
    }
}
