<?php

declare(strict_types=1);

namespace App\Admin;

use App\Client\Presentation\ClientProjectCrudController;
use App\Client\Presentation\LeadCrudController;
use App\User\Presentation\UserCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Override;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    #[Override]
    public function index(): Response
    {
        return $this->render('dashboard.html.twig');
    }

    #[Override]
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Bisort Console');
    }

    #[Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Administration');
        yield MenuItem::linkTo(UserCrudController::class, 'Users', 'fa fa-users');

        yield MenuItem::section('Clients');
        yield MenuItem::linkTo(LeadCrudController::class, 'Leads', 'fa-solid fa-user-plus');
        yield MenuItem::linkTo(ClientProjectCrudController::class, 'Client Projects', 'fa-solid fa-briefcase');
    }
}
