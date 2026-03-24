<?php


namespace App\Controller;

use App\Entity\Report;
use App\Entity\User;
use App\Entity\UserAccess;
use App\Form\IssuerType;
use App\Form\ManagerEditType;
use App\Form\ManagerType;
use App\Form\PasswordUserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/managers", name="user_managers", options = { "expose" = true })
     */
    public function managers(Request $request, PaginatorInterface $paginator): Response
    {
        $company = $this->getUser()->getCompanyIssuer();
        $managers = $this->getDoctrine()->getRepository(User::class)->getManagers($company, true);

        $pagination = $paginator->paginate(
            $managers, /* query NOT result */
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('user/managers.html.twig', [
            'managers' => $pagination
        ]);
    }

    /**
     * @Route("/issuers", name="user_issuers")
     */
    public function issuers(Request $request): Response
    {
        return $this->render('user/issuers.html.twig', [
            'issuers' => $this->getUser()->getCompanyIssuer()->getIssuer()
        ]);
    }

    /**
     * @Route("/managers/new", name="user_managers_new")
     */
    public function newManager(Request $request, \Swift_Mailer $mailer, TranslatorInterface $translator): Response
    {
        $company = $this->getUser()->getCompanyIssuer();
        $user = new User();

        $access = $this->getDoctrine()->getRepository(UserAccess::class)->findOneBy([
            'issuer' => $this->getUser()
        ]);

        $form = $this->createForm(ManagerType::class, $user, [
            'access' => $access
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = md5(uniqid(rand(), true));

            $user->setRoles(['ROLE_MANAGER']);
            $user->setCompanyManager($company);
            if ($form->has('access') && $form->get('access')->getData()) {
                $user->setEnabled(true);
                $user->setRegistered(true);
            } else {
                $user->setEnabled(false);
                $user->setRegistered(false);
                $user->setToken($token);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);

            if ($form->has('access') && $form->get('access')->getData()) {
                // tworzenie powiązanai konta managera z kontem emitenta
                $access = new UserAccess();
                $access->setIssuer($this->getUser());
                $access->setManager($user);
                $entityManager->persist($access);
            }

            $entityManager->flush();

            if (!$form->has('access') || $form->has('access') && !$form->get('access')->getData()) {
                // wysyłka maila aktywacyjnego
                $this->sendMailInvitation($user, $mailer, $translator);
            }

            $this->addFlash('success', 'global.data_was_saved');

            return $this->redirectToRoute('user_managers');
        }

        return $this->render('user/new_manager.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/issuers/new", name="user_issuers_new")
     */
    public function newIssuers(Request $request, \Swift_Mailer $mailer, TranslatorInterface $translator): Response
    {
        $company = $this->getUser()->getCompanyIssuer();
        $user = new User();
        $form = $this->createForm(IssuerType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = md5(uniqid(rand(), true));

            $user->setCompanyIssuer($company);
            $user->setEnabled(false);
            $user->setRegistered(false);
            $user->setToken($token);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->sendMailInvitation($user, $mailer, $translator);

            $this->addFlash('success', 'global.data_was_saved');

            return $this->redirectToRoute('user_issuers');
        }

        return $this->render('user/new_issuer.html.twig', [
            'form' => $form->createView()
        ]);
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

    private function sendMailInvitation(User $user, \Swift_Mailer $mailer, TranslatorInterface $translator)
    {
        $email = $user->getEmail();
        $roles = $user->getRoles();

        $message = (new \Swift_Message($translator->trans('email.invitation_title')))
            ->setFrom($this->getParameter('email_from'))
            ->setTo($email)
            ->setBody(
                $this->renderView(
                    'emails/invitation.html.twig', [
                        'user' => $user,
                        'role' => $roles[0]
                    ]
                ),
                'text/html'
            );

        $mailer->send($message);
    }

    /**
     * @Route("/managers/view/{manager}", name="user_managers_view")
     */
    public function viewManager(Request $request, User $manager): Response
    {
        $form = $this->createForm(ManagerEditType::class, $manager);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($manager);

            $data = $form->getData();

            $active = $this->getDoctrine()->getRepository(Report::class)->getActiveByManager($manager);
            if ($active) {
                $active->getData()->setFirstName($data->getFirstName());
                $active->getData()->setLastName($data->getLastName());
                $active->getData()->setPosition($data->getManager()->getPosition());
                $active->getData()->setPesel($data->getManager()->getPesel());
                $active->getData()->setAddress($data->getManager()->getAddress());
                $entityManager->persist($active);
            }

            $entityManager->flush();

            $this->addFlash('success', 'global.data_was_saved');
        }

        return $this->render('user/view_manager.html.twig', [
            'manager' => $manager,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/managers/stop/{manager}", name="user_managers_stop", options = { "expose" = true })
     */
    public function stop(Request $request, User $manager, TranslatorInterface $translator, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getUser();
        $action = $request->request->get('action');
        $password = $request->request->get('password');
        $set = $request->request->get('set');
        $error = null;

        if ($set) {
            if (!$password) {
                $error = $translator->trans("global.enter_password");
            } else {
                if (!$passwordEncoder->isPasswordValid($user, $password)) {
                    $error = $translator->trans("global.password_is_incorrect");
                } else {
                    $entityManager = $this->getDoctrine()->getManager();

                    if ($action == 'remove') {
                        $manager->setDeleted(true);
                        $manager->setEnabled(false);
                        $manager->setEmail("deleted_" . uniqid() . "_" . $manager->getEmail());

                        $this->addFlash('success', 'global.user_was_deleted');
                    }
                    if ($action == 'suspend') {
                        $manager->setEnabled(false);

                        $this->addFlash('success', 'global.user_was_suspended');
                    }
                    if ($action == 'restore') {
                        $manager->setEnabled(true);

                        $this->addFlash('success', 'global.user_was_restored');
                    }

                    $entityManager->persist($manager);
                    $entityManager->flush();

                    return new Response(null);
                }
            }
        }

        return $this->render('user/stop_account.html.twig', [
            'action' => $action,
            'manager' => $manager,
            'error' => $error
        ]);
    }

    /**
     * @Route("/my-account", name="user_my_account")
     */
    public function myAccount(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(PasswordUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encoded = $passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($encoded);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'global.password_was_changed');
        }

        return $this->render('user/my_account.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/change-role/{role}", name="user_change_role")
     */
    public function changeRole(Request $request, string $role, EventDispatcherInterface $dispatcher): Response
    {
        $user = $this->getUser();

        if ($role == 'ROLE_MANAGER') {

            $access = $this->getDoctrine()->getRepository(UserAccess::class)->findOneBy([
                'issuer' => $user
            ]);

            if ($access) {
                $manager = $access->getManager();

                // logowanie do managera
                $token = new UsernamePasswordToken($manager, null, 'main', $manager->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $dispatcher->dispatch("security.interactive_login", $event);

                return $this->redirectToRoute('report_my');
            }

        } elseif ($role == 'ROLE_ISSUER') {
            $access = $this->getDoctrine()->getRepository(UserAccess::class)->findOneBy([
                'manager' => $user
            ]);

            if ($access) {
                $issuer = $access->getIssuer();

                // logowanie do emitenta
                $token = new UsernamePasswordToken($issuer, null, 'main', $issuer->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $dispatcher->dispatch("security.interactive_login", $event);

                return $this->redirectToRoute('report_unit_list');
            }
        }

        throw new NotFoundHttpException();
    }

    public function changeRoleButton(Request $request, TranslatorInterface $translator): Response
    {
        $html = null;

        $user = $this->getUser();
        $access = $this->getDoctrine()->getRepository(UserAccess::class)->findAccessByUser($user);

        if ($access && $access->getManager() == $user) {
            $url = $this->generateUrl('user_change_role', [
                'role' => 'ROLE_ISSUER'
            ]);

            $html = '<li><a href="' . $url . '"> <i class="fa fa-share"></i><span>' . $translator->trans('global.change_to_issuer_account') . '</span> <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a></li>';
        }

        if ($access && $access->getIssuer() == $user) {
            $url = $this->generateUrl('user_change_role', [
                'role' => 'ROLE_MANAGER'
            ]);

            $html = '<li><a href="' . $url . '"> <i class="fa fa-share"></i><span>' . $translator->trans('global.change_to_manager_account') . '</span> <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a></li>';
        }

        return new Response($html);
    }
}
