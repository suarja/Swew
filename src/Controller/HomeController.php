<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HomeController extends AbstractController
{
    #[Route(
        path: '/{reactRouting}',
        name: 'app_shell',
        requirements: [
            'reactRouting' => '^(?!(styles|src|build|assets|_profiler|_wdt|favicon\\.ico|login|logout|device|courses|lessons|assignments)).*$',
        ],
        defaults: ['reactRouting' => null],
        methods: ['GET']
    )]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
