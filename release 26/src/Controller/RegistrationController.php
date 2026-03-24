<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\UserManager;
use App\Form\RegistrationDetailsType;
use App\Form\RegistrationKrsType;
use App\Form\RegistrationManagersType;
use App\Form\RegistrationUserType;
use App\Form\RegistrationType;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="user_registration")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EventDispatcherInterface $dispatcher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRegisterStep(1);
            $user->setLastLogin(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // logowanie
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));
            $event = new InteractiveLoginEvent($request, $token);
            $dispatcher->dispatch("security.interactive_login", $event);

            return $this->redirectToRoute('user_registration_krs');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
            'center_area' => true,
        ]);
    }

    /**
     * @Route("/register/krs", name="user_registration_krs")
     */
    public function registerKrs(Request $request): Response
    {
        $user = $this->getUser();
        $company = $user->getCompanyIssuer();

        if (!$company) {
            $company = new Company();
            $company->addIssuer($user);
        }

        $form = $this->createForm(RegistrationKrsType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($company);

            $user->setRegisterStep(2);
            $entityManager->persist($user);

            $entityManager->flush();

            return $this->redirectToRoute('user_registration_details');
        }

        return $this->render('registration/register_krs.html.twig', [
            'form' => $form->createView(),
            'center_area' => true,
        ]);
    }

    /**
     * @Route("/register/details", name="user_registration_details")
     */
    public function registerDetails(Request $request): Response
    {
        $user = $this->getUser();
        $company = $user->getCompanyIssuer();
        $this->apiCompanyDetails($company);

        $form = $this->createForm(RegistrationDetailsType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($company);

            $user->setRegisterStep(3);
            $entityManager->persist($user);

            $entityManager->flush();

            return $this->redirectToRoute('user_registration_managers');
        }

        return $this->render('registration/register_details.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/register/managers", name="user_registration_managers")
     */
    public function registerManagers(Request $request, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        $company = $user->getCompanyIssuer();
        $managers = $company->getManager();

        if (!count($managers) && $request->isMethod('POST') !== true) {
            $this->apiManagers($company);
        }

        $form = $this->createForm(RegistrationManagersType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->checkExistEmail($form, $translator);

            if ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($company);

                foreach ($company->getManager() as $manager) {
                    $token = md5(uniqid(rand(), true));

                    $manager->setRoles(['ROLE_MANAGER']);
                    $manager->setCompanyManager($company);
                    $manager->setEnabled(false);
                    $manager->setRegistered(false);
                    $manager->setToken($token);
                }

                $user->setRegisterStep(4);
                $entityManager->persist($user);

                $entityManager->flush();

                return $this->redirectToRoute('user_registration_managers_add');
            }
        }

        return $this->render('registration/register_managers.html.twig', [
            'form' => $form->createView(),
            'action' => 'api',
            'company' => $company
        ]);
    }

    /**
     * @Route("/register/managers/add", name="user_registration_managers_add")
     */
    public function registerManagersAdd(Request $request, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        $company = $user->getCompanyIssuer();

        $form = $this->createForm(RegistrationManagersType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->checkExistEmail($form, $translator);

            if ($form->isValid()) {

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($company);

                foreach ($company->getManager() as $manager) {
                    $token = md5(uniqid(rand(), true));

                    $manager->setRoles(['ROLE_MANAGER']);
                    $manager->setCompanyManager($company);
                    $manager->setToken($token);
                }

                $user->setRegisterStep(5);
                $entityManager->persist($user);

                $entityManager->flush();

                return $this->redirectToRoute('user_registration_managers_add', [
                    'step' => 'summary'
                ]);
            }
        }

        return $this->render('registration/register_managers.html.twig', [
            'form' => $form->createView(),
            'action' => 'add',
            'company' => $company
        ]);
    }

    /**
     * @Route("/register/send-invitation", name="user_registration_send_invitation")
     */
    public function sendInvitation(\Swift_Mailer $mailer, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        $entityManager = $this->getDoctrine()->getManager();

        $company = $this->getUser()->getCompanyIssuer();
        foreach ($company->getManager() as $manager) {
            $this->sendMailInvitation($manager, $mailer, $translator);
        }

        $user->setRegisterStep(null);
        $user->setEnabled(true);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('user_registration_managers_add', [
            'step' => 'confirmation'
        ]);
    }

    public function apiCompanyDetails(Company $details): void
    {
        $client = new \GuzzleHttp\Client(['verify' => false]);
        $url = $this->getParameter('api_url') . '/krs/' . $details->getKrs() . "/"
            . $this->getParameter('api_id');

        $result = $client->request('GET', $url);
        $body = json_decode($result->getBody(), true);

        if ($body) {
            $details->setNip($body['D1R1P2.NIP']);
            $details->setRegon($body['D1R1P2.REGON']);
            $details->setName($body['D1R1P3']);

            $address = $body['D1R2P2.ul'] . " " . $body['D1R2P2.nr'];
            if ($body['D1R2P2.lok'] && $body['D1R2P2.lok'] != '---') {
                $address .= " lok. " . $body['D1R2P2.lok'];
            }
            $address .= ", " . $body['D1R2P2.kod'] . " " . $body['D1R2P2.miejsc'];

            $details->setCity($body['D1R2P2.miejsc']);
            $details->setAddress($address);
            $details->setEmail($body['D1R2P3']);
        }
    }

    public function apiManagers(Company $company): void
    {
        $client = new \GuzzleHttp\Client(['verify' => false]);
        $url = $this->getParameter('api_url') . '/krs/' . $company->getKrs() . "/" .
            $this->getParameter('api_id');


        $result = $client->request('GET', $url);
        $body = json_decode($result->getBody(), true);

        $array = [];
        $array2 = [];

        if ($body) {

            foreach ($body as $label => $value) {

                // zarząd
                if (substr($label, 0, 1) == "S" && substr($label, 2, 8) ==
                    'D2R1PR1P') {
                    $key = substr($label, 1, 1);
                    $nr = substr($label, 10, 1);

                    $array[$key][$nr] = $value;
                }

                // rada nadzorcza
                if (substr($label, 0, 1) == "S" && substr($label, 2, 1) == "S"
                    && substr($label, 4, 8) == 'D2R2PR1P') {
                    $key = substr($label, 3, 1);
                    $nr = substr($label, 12, 1);

                    $array2[$key][$nr] = $value;
                }
            }

            $type = mb_strtolower($body['D1R1P1'], 'UTF-8');

            // zarząd
            if ($array) {
                foreach ($array as $row) {
                    $user = new User();

                    $userManager = new UserManager();

                    if ($type != "spółdzielnia" && isset($row[5])) {
                        $userManager->setPosition($row[5]);
                    }

                    if ($type == "spółdzielnia" && isset($row[4])) {
                        $userManager->setPosition($row[4]);
                    }

                    $userManager->setPesel($row[3]);
                    $user->setManager($userManager);
                    $user->setLastName($row[1]);
                    $user->setFirstName($row[2]);

                    $company->addManager($user);
                }
            }

            // rada nadzorcza
            if ($array2) {
                foreach ($array2 as $row) {
                    $user = new User();

                    $userManager = new UserManager();

                    $userManager->setPesel($row[3]);
                    $user->setManager($userManager);
                    $user->setLastName($row[1]);
                    $user->setFirstName($row[2]);

                    $company->addManager($user);
                }
            }
        }
    }

    /**
     * @Route("/register-manager/{token}", name="user_registration_manager")
     */
    public function registerManager(string $token, Request $request, UserPasswordEncoderInterface $passwordEncoder, EventDispatcherInterface $dispatcher): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'token' => $token
        ]);
        if (!$user) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(RegistrationUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setEnabled(true);
            $user->setToken(null);
            $user->setRegistered(true);
            $user->setRoles(['ROLE_MANAGER']);
            $user->setLastLogin(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // logowanie
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));
            $event = new InteractiveLoginEvent($request, $token);
            $dispatcher->dispatch("security.interactive_login", $event);

            // ustawienie sesji z info
            $session = $request->getSession();
            $session->set('manager_register_info', true);

            return $this->redirectToRoute('report_my');
        }

        return $this->render('registration/register_user.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/register-issuer/{token}", name="user_registration_issuer")
     */
    public function registerIssuer(string $token, Request $request, UserPasswordEncoderInterface $passwordEncoder, EventDispatcherInterface $dispatcher): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'token' => $token
        ]);
        if (!$user) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(RegistrationUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setEnabled(true);
            $user->setToken(null);
            $user->setRegistered(true);
            $user->setLastLogin(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // logowanie
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));
            $event = new InteractiveLoginEvent($request, $token);
            $dispatcher->dispatch("security.interactive_login", $event);

            return $this->redirectToRoute('report_unit_list');
        }

        return $this->render('registration/register_user.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function sendMailInvitation(User $user, \Swift_Mailer $mailer, TranslatorInterface $translator)
    {
        $email = $user->getEmail();

        $message = (new \Swift_Message($translator->trans('email.invitation_title')))
            ->setFrom($this->getParameter('email_from'))
            ->setTo($email)
            ->setBody(
                $this->renderView('emails/invitation.html.twig', [
                    'user' => $user
                ]),
                'text/html'
            );

        $mailer->send($message);
    }

    private function checkExistEmail($form, TranslatorInterface $translator): void
    {
        $entityManager = $this->getDoctrine()->getManager();

        foreach ($form->get('manager') as $row) {
            $user = $row->getData();
            $check = $entityManager->getRepository(User::class)->findOneBy([
                'email' => $user->getEmail()
            ]);

            if (!$user->getId() && $check) {
                $row->get('email')->addError(new FormError($translator->trans('global.email_exist')));
            }
        }
    }

    private function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}