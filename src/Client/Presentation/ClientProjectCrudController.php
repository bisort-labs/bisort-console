<?php

declare(strict_types=1);

namespace App\Client\Presentation;

use App\Client\Domain\ClientProject;
use App\Shared\Presentation\AbstractResourceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Override;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends AbstractResourceCrudController<ClientProject>
 */
class ClientProjectCrudController extends AbstractResourceCrudController
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
        parent::__construct($this->requestStack);
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return ClientProject::class;
    }

    #[Override]
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
}
