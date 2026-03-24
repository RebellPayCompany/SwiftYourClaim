<?php

namespace App\Controller;

use App\Form\RegistrationDetailsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company")
 */
class CompanyController extends AbstractController
{
    /**
     * @Route("/data", name="company_data")
     */
    public function data(Request $request): Response
    {
        $user = $this->getUser();
        $company = $user->getCompanyIssuer();

        $form = $this->createForm(RegistrationDetailsType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($company);
            $entityManager->flush();

            $this->addFlash('success', 'global.data_was_saved');
        }

        return $this->render('company/data.html.twig', [
            'form' => $form->createView()
        ]);
    }
}