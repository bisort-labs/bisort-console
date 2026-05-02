<?php

declare(strict_types=1);

namespace App\User\Presentation;

use App\User\Domain\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * @extends AbstractCrudController<User>
 */
class UserCrudController extends AbstractCrudController
{
    /**
     * @return class-string<User>
     */
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
}
