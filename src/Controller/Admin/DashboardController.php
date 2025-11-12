<?php

namespace App\Controller\Admin;

use App\Entity\Assignment;
use App\Entity\Course;
use App\Entity\Lesson;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(CourseCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('SWE Wannabe · Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Overview', 'fa fa-home');
        yield MenuItem::section('Curriculum');
        yield MenuItem::linkToCrud('Courses', 'fa-solid fa-book', Course::class);
        yield MenuItem::linkToCrud('Lessons', 'fa-solid fa-layer-group', Lesson::class);
        yield MenuItem::linkToCrud('Assignments', 'fa-solid fa-terminal', Assignment::class);
    }
}
