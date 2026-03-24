<?php

namespace App\Controller\Report;

use App\Entity\Company;
use App\Entity\RelatedEntity;
use App\Entity\Report;
use App\Entity\ReportData;
use App\Form\ReportPeselType;
use App\Form\ReportType;
use App\Service\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/report")
 */
class ReportController extends AbstractController
{
    /**
     * @Route("/my", name="report_my", defaults={"detect": 0})
     * @Route("/my/detect-connections", name="report_detect_connections", defaults={"detect": 1})
     */
    public function my(Request $request, Notification $Notification, TranslatorInterface $translator, int $detect): Response
    {
        $user = $this->getUser();
        $manager = $user->getManager();

        $company = $user->getCompanyManager();

        $report = $this->getDoctrine()->getRepository(Report::class)->findCurrent($user);

        if ($report) {
            if (!$report->getSaved()) {
                $this->apiReport($report);
                $report->getData()->setFirstName($user->getFirstName());
                $report->getData()->setLastName($user->getLastName());
                $report->getData()->setPosition($manager->getPosition());
            }

            // wykrywanie nowych powiązań
            if ($detect) {
                $krsArray = $this->apiReport($report, true);

                if (!$request->isMethod('post')) {
                    if (count($krsArray['krs_detect']) || count($krsArray['krs_deleted'])) {
                        if (count($krsArray['krs_detect'])) {
                            $this->addFlash('success', 'global.new_connections_was_detected');
                        }
                        if (count($krsArray['krs_deleted'])) {
                            $this->addFlash('danger', 'global.connections_was_deleted_api');
                        }
                    } else {
                        $this->addFlash('info', 'global.no_new_connections_detected');
                    }
                }
            }

            $form = $this->createForm(ReportType::class, $report);
            $form->handleRequest($request);

            $entityManager = $this->getDoctrine()->getManager();

            $data = $form->getData()->getData();
            $user->setFirstName($data->getFirstName());
            $user->setLastName($data->getLastName());
            $user->getManager()->setPesel($data->getPesel());
            $user->getManager()->setPosition($data->getPosition());
            $user->getManager()->setAddress($data->getAddress());
            $entityManager->persist($user);

            if ($form->isSubmitted() && $form->isValid()) {
                // czyszczenie statusu wykrycia powiązań
                foreach ($report->getRelatedEntity() as $row) {
                    $row->setDetect(false);
                    $row->setDeleted(false);
                }

                $report->setSaved(true);

                if ($form->get('sendToIssuer')->getData()) {
                    // tworzenie nowego aktualnego raportu
                    $newReport = clone $report;
                    $entityManager->detach($newReport);
                    $entityManager->persist($newReport);
                    $entityManager->flush();

                    // pobranie wczesniejszego raportu który był nowym i ustawienie statusu na stary
                    $new = $this->getDoctrine()->getRepository(Report::class)->getNewByManager($user);
                    if ($new) {
                        $new->setNew(false);
                        $entityManager->persist($new);
                    }

                    // sprawdzenie czy sa zmiany w krs
                    $active = $this->getDoctrine()->getRepository(Report::class)->getActiveByManager($user);
                    if ($active) {
                        $checkDifferences = $this->checkDifferencesRelations($report, $active);
                        $report->setChangeKrs($checkDifferences);
                    }

                    // wysylanie poprzedniego raportu
                    $report->setCompany($company);
                    $report->setStatus(Report::STATUS_WAITING);
                    $report->setNumber($this->reportNumber());
                    $report->setDate(new \Datetime());
                    $report->setNew(true);

                    $this->sendNotification($company, $report, $Notification, $translator);

                    $this->addFlash('success', 'global.report_sent_to_emitent');
                } else {
                    $this->addFlash('success', 'global.data_was_saved');
                }

                $entityManager->persist($report);
                $entityManager->flush();

                if ($form->get('sendToIssuer')->getData()) {
                    return $this->redirectToRoute('report_old');
                } else {
                    return $this->redirectToRoute('report_my');
                }
            }

            return $this->render('report/my.html.twig', [
                'form' => $form->createView()
            ]);
        } else {
            $session = $request->getSession();
            $managerRegisterInfo = $session->get('manager_register_info');
            $session->remove('manager_register_info');

            return $this->render('report/my.html.twig', [
                'managerRegisterInfo' => $managerRegisterInfo
            ]);
        }
    }

    /**
     * @Route("/pesel", name="report_pesel")
     */
    public function pesel(Request $request): Response
    {
        $manager = $this->getUser()->getManager();

        $report = new Report();
        $reportData = new ReportData();
        $report->setData($reportData);
        $report->setManager($this->getUser());

        $form = $this->createForm(ReportPeselType::class, $report, [
            'pesel' => $manager->getPesel()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($report);
            $entityManager->flush();

            return $this->redirectToRoute('report_my');
        }

        return $this->render('report/pesel.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/old", name="report_old")
     * @Route("/old/view/{report}", name="report_old_view")
     */
    public function old(Report $report = null, PaginatorInterface $paginator, Request $request): Response
    {
        $reports = $this->getDoctrine()->getRepository(Report::class)->findAllByUser($this->getUser(), true);

        $pagination = $paginator->paginate(
            $reports, /* query NOT result */
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('report/old.html.twig', [
            'reports' => $pagination,
            'report' => $report
        ]);
    }

    /**
     * @Route("/reason-rejection/{report}", name="report_reason_rejection", options = { "expose" = true })
     */
    public function reasonRejection(Report $report): Response
    {
        return new Response($report->getReasonRejection());
    }

    /**
     * @Route("/statistic", name="report_statistic")
     */
    public function statistic(Request $request): Response
    {
        $company = $this->getUser()->getCompanyIssuer();

        $reports = $this->getDoctrine()->getRepository(Report::class)->findBy([
            'company' => $company
        ]);
        foreach($reports as $report) {
            if (!isset($result[$report->getDate()->format("Y-m")])) {
                $result[$report->getDate()->format("Y-m")] = 1;
            }

            $result[$report->getDate()->format("Y-m")]++;
        }

        return $this->render('report/statistic.html.twig', [
            'result' => $result
        ]);
    }

    private function sendNotification(Company $company, Report $report, Notification $Notification, TranslatorInterface $translator)
    {
        $data['title'] = $translator->trans('notification.title_new_report_created');
        $data['content'] = $this->renderView('notification/send/new_report.html.twig', [
            'report' => $report
        ]);

        $issuers = $company->getIssuer();
        foreach ($issuers as $issuer) {
            $Notification->send($issuer, $data);
        }
    }

    private function reportNumber()
    {
        $count = $this->getDoctrine()->getRepository(Report::class)->countReportByManager($this->getUser());
        $count++;

        return "MAR_" . $count . "/" . date("Y");
    }

    private function apiReport(Report $report, $detect = false): array
    {
        // wykrywanie nowych powiązań
        $krsArray = [];
        $krsDetect = [];
        $krsDeleted = [];
        $krsApiArray = [];

        if ($detect) {
            foreach ($report->getRelatedEntity() as $row) {
                $krsArray[] = $row->getKrs();
            }
        }

        $data = $report->getData();
        $pesel = $data->getPesel();
        if ($pesel) {
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $url = $this->getParameter('api_url') . '/pesel/' . $pesel . "/" . $this->getParameter('api_id');

            try {
                $result = $client->request('GET', $url);
                $body = json_decode($result->getBody(), true);

            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $body = null;
            }



            if (is_array($body)) {
                foreach ($body as $krs) {
                    $url = $this->getParameter('api_url') . '/krs/' . $krs . "/" . $this->getParameter('api_id');

                    $result = $client->request('GET', $url);
                    $body = json_decode($result->getBody(), true);

                    if ($body) {
                        $relatedEntity = new RelatedEntity();
                        $relatedEntity->setName($body['D1R1P3']);

                        if ($body['D1R2P3'] != '------') {
                            $relatedEntity->setEmail($body['D1R2P3']);
                        }

                        $address = trim($body['D1R2P2.ul']) . " " . $body['D1R2P2.nr'];
                        if ($body['D1R2P2.lok'] && $body['D1R2P2.lok'] != '---') {
                            $address .= " lok. " . $body['D1R2P2.lok'];
                        }
                        $address .= ", " . $body['D1R2P2.kod'] . " " . $body['D1R2P2.miejsc'];

                        $relatedEntity->setAddress($address);
                        $relatedEntity->setBusinessAddress($address);
                        $relatedEntity->setKrs($krs);

                        if ($detect && !in_array($krs, $krsArray)) {
                            $relatedEntity->setDetect(true);
                        }

                        if (!$detect || $detect && !in_array($krs, $krsArray)) {
                            $report->addRelatedEntity($relatedEntity);
                            $krsDetect[] = $krs;
                        }

                        $krsApiArray[] = $krs;
                    }
                }

                // sprawdzenie czy jakies powiazanie zostalo usuniete
                foreach ($report->getRelatedEntity() as $row) {
                    if ($row->getKrs() && !in_array($row->getKrs(), $krsApiArray)) {
                        $row->setDeleted(true);
                        $krsDeleted[] = $row->getKrs();
                    }
                }
            }
        }

        $krsResult['krs_detect'] = $krsDetect;
        $krsResult['krs_deleted'] = $krsDeleted;

        return $krsResult;
    }

    private function checkDifferencesRelations($report, $active)
    {
        $diffrence = false;

        $reportRelatedEntityArray = [];
        $activeRelatedEntityArray = [];

        $reportRelatedPersonArray = [];
        $activeRelatedPersonArray = [];

        // podmioty powiązane aktualnego raportu
        $reportEntity = $report->getRelatedEntity();
        foreach ($reportEntity as $row) {
            $reportRelatedEntityArray[] = $row->getName();
        }

        // podmioty powiązane poprzedniego aktywnego raportu
        $activePerson = $active->getRelatedEntity();
        foreach ($activePerson as $row) {
            $activeRelatedEntityArray[] = $row->getName();
        }

        if (count($reportRelatedEntityArray) != count($activeRelatedEntityArray)) {
            $diffrence = true;
        } else {
            foreach ($reportRelatedEntityArray as $row) {
                if (!in_array($row, $activeRelatedEntityArray)) {
                    $diffrence = true;
                }
            }
        }

        // podmioty powiązane aktualnego raportu
        $reportPerson = $report->getRelatedPerson();
        foreach ($reportPerson as $row) {
            $reportRelatedPersonArray[] = $row->getName();
        }

        // podmioty powiązane poprzedniego aktywnego raportu
        $activePerson = $active->getRelatedPerson();
        foreach ($activePerson as $row) {
            $activeRelatedPersonArray[] = $row->getName();
        }

        if (count($reportRelatedPersonArray) != count($activeRelatedPersonArray)) {
            $diffrence = true;
        } else {
            foreach ($reportRelatedPersonArray as $row) {
                if (!in_array($row, $activeRelatedPersonArray)) {
                    $diffrence = true;
                }
            }
        }

        return $diffrence;
    }
}