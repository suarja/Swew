<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route(
        path: '/{reactRouting}',
        name: 'app_shell',
        requirements: [
            'reactRouting' => '^(?!styles|src|build|assets|_profiler|_wdt|favicon\\.ico).*$',
        ],
        defaults: ['reactRouting' => null],
        methods: ['GET']
    )]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
