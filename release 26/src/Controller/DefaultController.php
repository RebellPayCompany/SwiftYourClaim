<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="app_default")
     */
    public function index()
    {
        return $this->redirectToRoute('app_login');
    }

    public function menu(): Response
    {
        return $this->render('menu.html.twig', [
        ]);
    }
}
