<?php

declare(strict_types=1);

namespace App\Client\Presentation;

use App\Client\Application\ClientProjectProcessor;
use App\Client\Domain\ClientProject;
use App\Shared\Presentation\AbstractResourceCrudController;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends AbstractResourceCrudController<ClientProject>
 */
class ClientProjectCrudController extends AbstractResourceCrudController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ClientProjectProcessor $clientProjectProcessor,
    ) {
        parent::__construct($this->requestStack);
    }

    public static function getEntityFqcn(): string
    {
        return ClientProject::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title');

        yield TextareaField::new('description')
            ->onlyOnForms();

        yield SlugField::new('slug')
            ->setTargetFieldName('slug')
            ->setDisabled()
            ->hideWhenCreating()
            ->hideWhenUpdating();

        yield BooleanField::new('isActive')->renderAsSwitch();
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->clientProjectProcessor->process($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->clientProjectProcessor->process($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }
}
