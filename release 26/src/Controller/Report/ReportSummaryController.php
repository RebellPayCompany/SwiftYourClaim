<?php

namespace App\Controller\Report;

use App\Entity\Company;
use App\Entity\Report;
use App\Entity\ReportSummary;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/report/summary")
 */
class ReportSummaryController extends Controller
{
    /**
     * @Route("/list", name="report_summary_list")
     */
    public function list(Request $request, PaginatorInterface $paginator): Response
    {
        $company = $this->getUser()->getCompanyIssuer();

        $newReport = null;
        $reports = $this->getDoctrine()->getRepository(ReportSummary::class)->findAllByCompany($company);
        if ($reports) {
            $newReport[] = $reports[0];
            unset($reports[0]);
        }
        $pagination = $paginator->paginate(
            $reports, /* query NOT result */
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('report/summary/list.html.twig', [
            'reports' => $pagination,
            'newReport' => $newReport
        ]);
    }

    /**
     * @Route("/generate-pdf", name="report_summary_generate_pdf")
     */
    public function generatePdf(Request $request): Response
    {
        $company = $this->getUser()->getCompanyIssuer();

        $reports = $this->getDoctrine()->getRepository(Report::class)->findActualByCompany($company);

        $date = new \DateTime();
        $number = $this->reportNumber($company);
        $file = str_replace("/", "_", $number) . "_" . uniqid() . ".pdf";

        $reportSummary = new ReportSummary();
        $reportSummary->setUser($this->getUser());
        $reportSummary->setCompany($company);
        $reportSummary->setNumber($number);
        $reportSummary->setDate($date);
        $reportSummary->setFile($file);

        $em = $this->getDoctrine()->getManager();
        $em->persist($reportSummary);
        $em->flush();

        $path = $this->get('kernel')->getProjectDir() . '/public/uploads/report_summary/';

        $snappy = $this->get('knp_snappy.pdf');
        $snappy->setOption('footer-center', 'Strona [page]');

        $snappy->generateFromHtml(
            $this->renderView('report/summary/pdf.html.twig', [
                'reports' => $reports,
                'date' => $date,
                'company' => $company,
                'number' => $number
            ]),
            $path . $file, [
                'encoding' => 'utf-8'
            ]
        );

        $this->addFlash('success', 'global.report_was_generated');
        return $this->redirectToRoute('report_summary_list');
    }

    /**
     * @Route("/change-description/{report}", name="report_summary_change_description", options = { "expose" = true })
     */
    public function changeDescription(Request $request, ReportSummary $report): Response
    {
        if ($report->getCompany() != $this->getUser()->getCompanyIssuer()) {
            throw new NotFoundHttpException();
        }

        $description = $request->request->get('description');
        $report->setDescription($description);

        $em = $this->getDoctrine()->getManager();
        $em->persist($report);
        $em->flush();

        return new Response($description);
    }

    private function reportNumber(Company $company)
    {
        $count = $this->getDoctrine()->getRepository(ReportSummary::class)->countReport($company);
        $count++;

        return "MAR_ZBIORCZY_" . $count . "/" . date("Y");
    }
}