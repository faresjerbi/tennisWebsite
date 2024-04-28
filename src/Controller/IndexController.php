<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig', []);
    }
    #[Route('/admin', name: 'app_index_admin')]
    public function indexAdmin(): Response
    {
        return $this->render('index/indexAdmin.html.twig', []);
    }
}
