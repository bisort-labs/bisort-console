<?php

declare(strict_types=1);

namespace App\Shared\Presentation;

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
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NullFilter;
use Override;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @template TEntity of AbstractResource
 *
 * @extends AbstractCrudController<TEntity>
 */
abstract class AbstractResourceCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @return class-string<TEntity>
     */
    #[Override]
    abstract public static function getEntityFqcn(): string;

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
        $restore = Action::new('restore', 'Restore', 'fa fa-undo')
            ->linkToCrudAction('restore')
            ->displayIf(static fn (object $entity): bool => $entity instanceof AbstractResource)
            ->askConfirmation('Restore this item?');

        return $actions
            ->add(Crud::PAGE_INDEX, $restore)
            ->add(Crud::PAGE_DETAIL, $restore);
    }

    #[Override]
    public function deleteEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $entityInstance->markAsDeleted();
        $entityManager->flush();
    }

    private function addDeletedAtFilter(QueryBuilder $queryBuilder, string $alias): QueryBuilder
    {
        if ($this->hasFilterFor('deletedAt')) {
            return $queryBuilder;
        }

        return $queryBuilder->andWhere(sprintf('%s.deletedAt IS NULL', $alias));
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

    private function hasFilterFor(string $propertyName): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return false;
        }

        return array_key_exists($propertyName, $request->query->all('filters'));
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
