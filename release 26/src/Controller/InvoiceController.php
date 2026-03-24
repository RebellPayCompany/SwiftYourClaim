<?php

namespace App\Controller;

use App\Entity\InvoiceData;
use App\Form\InvoiceDataType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/invoice")
 */
class InvoiceController extends AbstractController
{
    /**
     * @Route("/data", name="invoice_data")
     */
    public function data(Request $request): Response
    {
        $user = $this->getUser();
        $invoiceData = $user->getInvoiceData();
        if (!$invoiceData) {
            $invoiceData = new InvoiceData();
            $invoiceData->setUser($user);
        }

        $form = $this->createForm(InvoiceDataType::class, $invoiceData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($invoiceData);
            $entityManager->flush();

            $this->addFlash('success', 'global.data_was_saved');

            return $this->redirectToRoute('payment_account');
        }

        return $this->render('invoice/data.html.twig', [
            'form' => $form->createView()
        ]);
    }
}