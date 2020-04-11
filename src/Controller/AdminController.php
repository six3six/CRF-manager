<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class   AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/user_list", name="admin_user_list")
     */
    public function user_list()
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $all_users = $userRepo->findAll();
        $ret = array();
        foreach ($all_users as $user) {
            array_push($ret, array(
                "name" => $user->getFormattedName(),
                "username" => $user->getUsername()
            ));
        }
        return new JsonResponse($ret);
    }

    /**
     * @Route("/admin/user_planning/{username}", name="admin_user_planning")
     * @param $username
     * @return JsonResponse
     */
    public function user_planning($username)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->findOneBy(["username" => $username]);
        if ($user == null) throw new NotFoundHttpException("Utilisateur non trouvé");

        $ret = array();

        foreach ($user->getAvailabilities() as $av) {
            array_push($ret, array(
                "type" => "availability",
                "start" => $av->getStart()->format(\DateTime::ISO8601),
                "stop" => $av->getStop()->format(\DateTime::ISO8601),
                "id" => $av->getId()
            ));
        }
        foreach ($user->getEvents() as $ev) {
            array_push($ret, array(
                "type" => "event",
                "start" => $ev->getStart()->format(\DateTime::ISO8601),
                "stop" => $ev->getStop()->format(\DateTime::ISO8601),
                "id\" => $av->getId()"
            ));
        }

        return new JsonResponse($ret);
    }
}
