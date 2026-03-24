<?php

namespace App\Controller;

use App\Entity\RelatedEntity;
use App\Entity\RelatedPerson;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/document")
 */
class DocumentController extends Controller
{
    /**
     * @Route("/", name="document_list")
     */
    public function list(): Response
    {
        return $this->render('document/list.html.twig');
    }

    /**
     * @Route("/list-manager", name="document_list_manager")
     */
    public function listManager(): Response
    {
        return $this->render('document/listManager.html.twig');
    }

    /**
     * @Route("/view-pdf/{type}", name="document_view_pdf")
     */
    public function viewPdf(string $type): PdfResponse
    {
        $company = $this->getUser()->getCompanyIssuer();

        if ($type == 'manager') {
            $html = $this->renderView('document/patterns/toManager.html.twig', [
                'date' => date("d.m.Y"),
                'company' => $company->getName(),
                'manager' => 'Jan Nowak',
                'city' => $company->getCity()
            ]);
        } elseif ($type == 'person') {
            $html = $this->renderView('document/patterns/toPerson.html.twig', [
                'date' => date("d.m.Y"),
                'company' => $company->getName(),
                'manager' => 'Jan Nowak',
                'person' => '[imię/nazwisko]',
                'address' => '[adres]',
                'city' => $company->getCity()
            ]);
        } elseif ($type == 'entity') {
            $html = $this->renderView('document/patterns/toEntity.html.twig', [
                'date' => date("d.m.Y"),
                'company' => $company->getName(),
                'manager' => 'Jan Nowak',
                'entityName' => '[nazwa podmiotu]',
                'address' => '[adres]',
                'city' => $company->getCity()
            ]);
        }

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'powiadomienie_o_obowiazkach_notyfikacyjnych_manager.pdf',
            'application/pdf',
            'inline'
        );
    }

    /**
     * @Route("/download-pdf-manager", name="document_download_pdf_manager")
     */
    public function downloadPdfManager(): PdfResponse
    {
        $user = $this->getUser();
        $company = $user->getCompanyManager();

        $html = $this->renderView('document/patterns/toManager.html.twig', [
            'date' => date("d.m.Y"),
            'company' => $company->getName(),
            'manager' => $user->getFirstName() . ' ' . $user->getLastName(),
            'city' => $company->getCity()
        ]);

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'powiadomienie_o_obowiazkach_notyfikacyjnych.pdf'
        );
    }

    /**
     * @Route("/generate-to-subject-pdf/{id}", name="document_generate_to_subject_pdf", options = { "expose" = true })
     */
    public function generateToSubjectPdf(Request $request, int $id, \Swift_Mailer $mailer, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        $company = $user->getCompanyManager();

        $type = $request->request->get('type');
        if ($type == 'entity') {
            $subject = $this->getDoctrine()->getRepository(RelatedEntity::class)->find($id);

            $html = $this->renderView('document/patterns/toEntity.html.twig', [
                'date' => date("d.m.Y"),
                'company' => $company->getName(),
                'manager' => $user->getFirstName() . " " . $user->getLastName(),
                'entityName' => $subject->getName(),
                'address' => $subject->getAddress(),
                'city' => $company->getCity()
            ]);

            $file = "powiadomienie_o_obowiazkach_not_" . uniqid() . ".pdf";
        }

        if ($type == 'person') {
            $subject = $this->getDoctrine()->getRepository(RelatedPerson::class)->find($id);

            $html = $this->renderView('document/patterns/toPerson.html.twig', [
                'date' => date("d.m.Y"),
                'company' => $company->getName(),
                'manager' => $user->getFirstName() . " " . $user->getLastName(),
                'person' => $subject->getName(),
                'address' => '......................<br>[adres]',
                'city' => $company->getCity()
            ]);

            $file = "powiadomienie_o_obowiazkach_not_" . uniqid() . ".pdf";
        }

        $path = $this->get('kernel')->getProjectDir() . '/public/uploads/documents/';

        $snappy = $this->get('knp_snappy.pdf');
        $snappy->generateFromHtml(
            $html,
            $path . $file, [
                'encoding' => 'utf-8'
            ]
        );

        $subject->setDocument($file);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($subject);
        $entityManager->flush();

        // na razie nie potrzeba wysyłania maila
        //$this->sendEmail($mailer, $subject->getEmail(), $translator, $path . $file);

        return new Response($file);
    }

    private function sendEmail(\Swift_Mailer $mailer, $email, TranslatorInterface $translator, string $file)
    {
        $user = $this->getUser();
        $company = $user->getCompanyManager();

        $message = (new \Swift_Message($translator->trans('email.notification_title')))
            ->setFrom($this->getParameter('email_from'))
            ->setTo($email)
            ->setBody(
                $this->renderView('emails/notification.html.twig', [
                    'user' => $user->getFirstName() . " " . $user->getLastName(),
                    'company' => $company
                ]),
                'text/html'
            )
            ->attach(\Swift_Attachment::fromPath($file));

        $mailer->send($message);
    }
}