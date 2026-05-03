<?php

declare(strict_types=1);

namespace App\User\Presentation;

use App\Shared\Presentation\AbstractResourceCrudController;
use App\User\Domain\User;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Override;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends AbstractResourceCrudController<User>
 */
class UserCrudController extends AbstractResourceCrudController
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
        parent::__construct($this->requestStack);
    }

    /**
     * @return class-string<User>
     */
    #[Override]
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username');
        yield TextField::new('password')
            ->setFormType(PasswordType::class)
            ->onlyWhenCreating();

        yield BooleanField::new('isActive')->renderAsSwitch();
    }
}
