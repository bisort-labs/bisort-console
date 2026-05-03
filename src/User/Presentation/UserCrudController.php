<?php

declare(strict_types=1);

namespace App\User\Presentation;

use App\Shared\Presentation\AbstractResourceCrudController;
use App\User\Application\UserProcessor;
use App\User\Domain\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends AbstractResourceCrudController<User>
 */
class UserCrudController extends AbstractResourceCrudController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UserProcessor $createUser,
    ) {
        parent::__construct($this->requestStack);
    }

    /**
     * @return class-string<User>
     */
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username');
        yield TextField::new('password')
            ->setFormType(PasswordType::class)
            ->onlyWhenCreating();

        yield BooleanField::new('isActive')->renderAsSwitch();
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->createUser->process($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }
}
