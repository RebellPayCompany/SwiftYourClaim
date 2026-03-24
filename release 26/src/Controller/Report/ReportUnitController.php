<?php

namespace App\Controller\Report;

use App\Entity\Report;
use App\Entity\ReportSummary;
use App\Entity\User;
use App\Form\ReportSearchType;
use App\Service\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/report/unit")
 */
class ReportUnitController extends AbstractController
{
    /**
     * @Route("/list", name="report_unit_list")
     * @Route("/list/view/{report}", name="report_unit_list_view")
     * @Route("/list/{manager}", name="report_unit_list_manager")
     */
    public function list(Request $request, User $manager = null, Report $report = null, PaginatorInterface $paginator): Response
    {
        $company = $this->getUser()->getCompanyIssuer();

        $newReports = $this->getDoctrine()->getRepository(Report::class)->findNewByCompany($company, $manager);

        $form = $this->createForm(ReportSearchType::class);
        $form->handleRequest($request);

        $actualReports = $this->getDoctrine()->getRepository(Report::class)
            ->findRestByCompany($company, $manager, $form->getData(), true);

        $pagination = $paginator->paginate(
            $actualReports, /* query NOT result */
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('report/unit/list.html.twig', [
            'newReports' => $newReports,
            'actualReports' => $pagination,
            'manager' => $manager,
            'report' => $report,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/accept/{report}", name="report_unit_accept")
     */
    public function accept(Report $report, Notification $Notification, TranslatorInterface $translator): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $manager = $report->getManager();

        $active = $this->getDoctrine()->getRepository(Report::class)->getActiveByManager($manager);
        if ($active) {
            $active->setActive(false);
            $entityManager->persist($active);
        }

        $report->setStatus(Report::STATUS_APPROVED);
        $report->setActive(true);
        $report->setChangeKrs(false);
        $entityManager->persist($report);
        $entityManager->flush();

        // powiadomienie
        $data['title'] = $translator->trans('notification.title_report_accepted');
        $data['content'] = $this->renderView('notification/send/report_accepted.html.twig', [
            'report' => $report
        ]);
        $Notification->send($report->getManager(), $data);

        $this->addFlash('success', 'global.report_was_accepted');
        return $this->redirectToRoute('report_unit_list');
    }

    /**
     * @Route("/discard/{report}", name="report_unit_discard", options = { "expose" = true })
     */
    public function discard(Request $request, Report $report, Notification $Notification, TranslatorInterface $translator): Response
    {
        $text = $request->request->get('text');

        $report->setStatus(Report::STATUS_REJECTED);
        $report->setChangeKrs(false);
        $report->setReasonRejection($text);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($report);
        $entityManager->flush();

        // powiadomienie
        $data['title'] = $translator->trans('notification.title_report_discarded');
        $data['content'] = $this->renderView('notification/send/report_discarded.html.twig', [
            'report' => $report
        ]);
        $Notification->send($report->getManager(), $data);

        $this->addFlash('success', 'global.report_was_rejected');

        return new Response(null);
    }

    /**
     * @Route("/view/{report}", name="report_unit_view", options = { "expose" = true })
     */
    public function view(Report $report): Response
    {
        return $this->render('report/unit/view.html.twig', [
            'report' => $report
        ]);
    }
}