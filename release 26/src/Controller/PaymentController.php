<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\User;
use App\Service\Premium;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/payment")
 */
class PaymentController extends Controller
{
    /**
     * @Route("/", name="payment_account")
     */
    public function account(Request $request, Premium $premium)
    {
        $productPrice = $this->getParameter("premium_price");
        $productName = $this->getParameter("premium_name");

        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        $paramPayU = $this->getParameter("payu");

        $secondKey = $paramPayU['signatureKey'];
        $merchantPosId = $paramPayU['merchantPosId'];
        $user = $this->getUser();

        $payU = [];
        $payU['customerIp'] = $_SERVER['REMOTE_ADDR'];
        $payU['merchantPosId'] = $merchantPosId;
        $payU['description'] = $productName;
        $payU['totalAmount'] = $productPrice * 100;
        $payU['currencyCode'] = "PLN";
        $payU['products[0].name'] = $productName;
        $payU['products[0].unitPrice'] = "1000";
        $payU['products[0].quantity'] = "1";
        $payU['extOrderId'] = uniqid()."_".$user->getId();
        if ($user) {
            $payU['buyer.email'] = $user->getEmail();
            $payU['buyer.firstName'] = $user->getFirstName();
            $payU['buyer.lastName'] = $user->getLastName();
        }

        $payU['notifyUrl'] = $baseurl.$this->generateUrl('payment_buy_notify'); 

        $payU['continueUrl'] = $baseurl.$this->generateUrl('payment_summary_buy');

        $signature = $this->generateSignature($payU, $secondKey, $merchantPosId);

        $premiumStatus = $premium->check($user);

        // faktury
        $invoices = $this->getDoctrine()->getRepository(Invoice::class)->findAllByUser($this->getUser());

        $term = $user->getPremiumStart();
        if ($term) {
            $termStart = $term->format("Y-m-d");
            $termEnd = date( 'Y-m-d', strtotime( $termStart .' +1 year' ));
        } else {
            $termStart = null;
            $termEnd = null;
        }

        return $this->render ( 'payment/index.html.twig', [
            'payU' => $payU,
            'signature' => $signature,
            'user' => $user,
            'price' => $productPrice,
            'name' => $productName,
            'premium' => $premiumStatus,
            'termStart' => $termStart,
            'termEnd' => $termEnd,
            'invoices' => $invoices,
            'user' => $user
        ] );
    }

    /**
     * @Route("/buy-summary", name="payment_summary_buy")
     *
     * @return Response
     */
    public function buySummaryAction()
    {
        $termStart = date("Y-m-d");
        $termEnd = date( 'Y-m-d', strtotime( $termStart .' +1 year' ));

        $user = $this->getUser();

        $user->setPremiumStart(new \Datetime());
        $user->setInvoiceGenerate(true);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render ('payment/buy_summary.html.twig', [
            'termStart' => $termStart,
            'termEnd' => $termEnd
        ]);
    }

    /**
     * @Route("/buy-notify", name="payment_buy_notify")
     */
    public function buyNotifyAction(Request $request)
    {
        $params = $request->request->all();
        //$params = $_POST;

        
        $extOrderId = $params['order']['extOrderId'];
        $arrayId = explode("_", $extOrderId);
        $userId = $arrayId[1];


        //$userId = 1;

        $user = $this->getDoctrine()->getRepository(User::class)->find($userId);
        //$user->setToken(json_encode($params));

        $user->setPremiumStart(new \Datetime());
        $user->setInvoiceGenerate(true);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();


        return new Response(null);
    }

    /**
     * @Route("/generate-invoice", name="payment_generate_invoice")
     */
    public function generateInvoiceAction(Request $request)
    {
        $user = $this->getUser();
        $user->setInvoiceGenerate(null);

        $invoiceData = $user->getInvoiceData();

        $invoicesCount = $this->getDoctrine()->getRepository(Invoice::class)->countByUser($this->getUser());
        $nr = $invoicesCount + 1;
        $nr = sprintf("%02d", $nr);
        $nr = $nr."/".date("Y");

        $baseDir = $this->get('kernel')->getRootDir() . '/../public' . $request->getBasePath();

        $invoice = new Invoice();
        $invoice->setUser($user);
        $invoice->setNumber($nr);
        $invoice->setDate(new \Datetime());

        $productPrice = $this->getParameter("premium_price");
        $productName = $this->getParameter("premium_name");

        $vat = 23;

        $netto = $productPrice / (1 + $vat / 100);
        $netto = round($netto, 2);

        $amountVat = $productPrice - $netto;
        $amountVat = round($amountVat, 2);


        $number = str_replace("/", "_", $user->getId() .'_'. $nr);

        $file =  $number. "_" . uniqid() . ".pdf";
        $invoice->setFile($file);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($invoice);
        $entityManager->persist($user);
        $entityManager->flush();

        $path = $this->get('kernel')->getProjectDir() . '/public/uploads/invoices/';

        $snappy = $this->get('knp_snappy.pdf');

        $snappy->generateFromHtml(
            $this->renderView('payment/pdf.html.twig', [
                'nr' => $nr,
                'invoiceData' => $invoiceData,
                'base_dir' => $baseDir,
                'invoice' => $invoice,
                'productName' => $productName,
                'brutto' => $productPrice,
                'netto' => $netto,
                'vat' => $vat,
                'amountVat' => $amountVat,
                'productPrice' => $productPrice,
            ]),
            $path . $file, [
                'encoding' => 'utf-8'
            ]
        );

        $this->addFlash('success', 'global.invoice_was_generated');
        return $this->redirectToRoute('payment_account');
    }

    public function generateSignature($params, $secondKey, $merchantPosId)
    {
        ksort($params);

        $content = null;
        foreach ($params as $key => $value) {
            $content = $content.$key.'='.urlencode($value).'&';
        }

        $content .= $secondKey;

        $result = 'sender='.$merchantPosId;
        $result .= ';algorithm=SHA-256;';
        $result .= 'signature='.hash('sha256', $content);

        return $result;
    }
}
