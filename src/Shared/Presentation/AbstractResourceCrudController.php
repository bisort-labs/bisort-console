<?php

declare(strict_types=1);

namespace App\Shared\Presentation;

use App\Shared\Application\ResourceProcessorInterface;
use App\Shared\Domain\AbstractResource;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NullFilter;
use Override;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @template TEntity of AbstractResource
 *
 * @extends AbstractCrudController<TEntity>
 */
abstract class AbstractResourceCrudController extends AbstractCrudController
{
    private const string ACTION_RESTORE = 'restore';
    private const string ACTION_BATCH_RESTORE = 'batchRestore';

    /** @var iterable<ResourceProcessorInterface<AbstractResource>> */
    private iterable $resourceProcessors = [];

    /**
     * @return class-string<TEntity>
     */
    #[Override]
    abstract public static function getEntityFqcn(): string;

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @param iterable<ResourceProcessorInterface<AbstractResource>> $resourceProcessors
     */
    #[Required]
    public function setResourceProcessors(
        #[AutowireIterator('shared.application.resource_processor')] iterable $resourceProcessors,
    ): void {
        $this->resourceProcessors = $resourceProcessors;
    }

    #[Override]
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $alias = $queryBuilder->getRootAliases()[0];

        return $this->addDeletedAtFilter($queryBuilder, $alias);
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $filters
            ->add(BooleanFilter::new('isActive'))
            ->add(NullFilter::new('deletedAt')->setChoiceLabels('Not deleted', 'Deleted'));

        return parent::configureFilters($filters);
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $restore = Action::new(self::ACTION_RESTORE, 'Restore', 'fa fa-undo')
            ->linkToCrudAction(self::ACTION_RESTORE)
            ->displayIf(static fn (object $entity): bool => $entity instanceof AbstractResource)
            ->askConfirmation('Restore this item?');

        $batchRestore = Action::new(self::ACTION_BATCH_RESTORE, 'Restore', 'fa fa-undo')
            ->createAsBatchAction()
            ->linkToCrudAction(self::ACTION_BATCH_RESTORE);

        return $actions
            ->add(Crud::PAGE_INDEX, $restore)
            ->add(Crud::PAGE_DETAIL, $restore)
            ->addBatchAction($batchRestore);
    }

    #[Override]
    public function deleteEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $entityInstance->markAsDeleted();
        $entityManager->flush();
    }

    #[Override]
    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->processResource($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }

    #[Override]
    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->processResource($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function processResource(object $entityInstance): void
    {
        if (!$entityInstance instanceof AbstractResource) {
            return;
        }

        foreach ($this->resourceProcessors as $resourceProcessor) {
            if (!$resourceProcessor->supports($entityInstance)) {
                continue;
            }

            $resourceProcessor->process($entityInstance);

            return;
        }
    }

    private function addDeletedAtFilter(QueryBuilder $queryBuilder, string $alias): QueryBuilder
    {
        if ($this->hasFilterFor('deletedAt')) {
            return $queryBuilder;
        }

        return $queryBuilder->andWhere(sprintf('%s.deletedAt IS NULL', $alias));
    }

    private function hasFilterFor(string $propertyName): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return false;
        }

        return array_key_exists($propertyName, $request->query->all('filters'));
    }

    /**
     * @param AdminContext<TEntity> $context
     */
    #[AdminRoute(path: '/{entityId}/restore', name: 'restore')]
    public function restore(
        AdminContext $context,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $entity = $context->getEntity()->getInstance();

        if (!$entity instanceof AbstractResource) {
            throw $this->createNotFoundException('Resource not found.');
        }

        $entity->restore();
        $entityManager->flush();

        return $this->redirectBack();
    }

    /**
     * @param AdminContext<TEntity>   $context
     * @param BatchActionDto<TEntity> $batchActionDto
     */
    #[AdminRoute(path: '/batch-restore', name: 'batch_restore', options: ['methods' => ['POST']])]
    public function batchRestore(
        AdminContext $context,
        BatchActionDto $batchActionDto,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('ea-batch-action-'.self::ACTION_BATCH_RESTORE.'-'.$batchActionDto->getEntityFqcn(), $batchActionDto->getCsrfToken())) {
            return $this->redirectToRoute($context->getDashboardRouteName());
        }

        $repository = $entityManager->getRepository($batchActionDto->getEntityFqcn());

        foreach ($batchActionDto->getEntityIds() as $entityId) {
            $entity = $repository->find($entityId);

            if (!$entity instanceof AbstractResource) {
                continue;
            }

            $entity->restore();
        }

        $entityManager->flush();

        return $this->redirectBack();
    }

    private function redirectBack(): RedirectResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $referer = $request?->headers->get('referer');

        if ($referer !== null) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('admin');
    }
}
