<?php

namespace App\Controller;

use App\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/notification")
 */
class NotificationController extends AbstractController
{
    /**
     * @Route("/", name="notification_list")
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $notifications = $this->getDoctrine()->getRepository(Notification::class)->findAllByUser($this->getUser(), true);

        $pagination = $paginator->paginate(
            $notifications, /* query NOT result */
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('notification/list.html.twig', [
            'notifications' => $pagination
        ]);
    }

    /**
     * @Route("/view/{notification}", name="notification_view", options = { "expose" = true })
     */
    public function view(Notification $notification)
    {
        if (!$notification->getReaded()) {
            $notification->setReaded(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($notification);
            $em->flush();
        }

        return $this->render('notification/view.html.twig', [
            'notification' => $notification
        ]);
    }
}