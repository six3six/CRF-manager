<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/admin/user_list_api", name="admin_user_list_api")
     */
    public function user_list_api()
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
     * @Route("/admin/user_list", name="admin_user_list")
     */
    public function user_list()
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $all_users = $userRepo->findAll();
        return $this->render('admin/user_list.html.twig', [
            'controller_name' => 'AdminController',
            "users" => $all_users,
        ]);
    }

    /**
     * @Route("/admin/user_edit/{username}", name="admin_user_edit", methods={"GET"})
     * @param $username
     * @return Response
     */
    public function user_edit($username, $error = "")
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->findOneBy(["username" => $username]);
        if ($user == null) throw new NotFoundHttpException("Utilisateur non trouvé");
        return $this->render('admin/user_edit.html.twig', [
            'controller_name' => 'AdminController',
            "user" => $user,
            "error" => $error
        ]);
    }

    /**
     * @Route("/admin/user_edit/{username}", name="admin_user_edit_save", methods={"POST"})
     * @param Request $request
     * @param $username
     * @return Response
     */
    public function user_edit_save(Request $request, $username)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->findOneBy(["username" => $username]);
        if ($user == null) throw new NotFoundHttpException("Utilisateur non trouvé");

        $keys = $request->request->keys();
        $error = "";
        $error .= !in_array("address", $keys) ? "Il manque le champ adresse<br/>" : "";
        $error .= !in_array("email", $keys) ? "Il manque le champ email<br/>" : "";
        $error .= !in_array("first_name", $keys) ? "Il manque le champ prénom<br/>" : "";
        $error .= !in_array("last_name", $keys) ? "Il manque le champ nom de famille<br/>" : "";
        $error .= !in_array("cellphone", $keys) ? "Il manque le champ numéro de téléphone portable<br/>" : "";
        $error .= !in_array("phone", $keys) ? "Il manque le champ numéro de téléphone fixe<br/>" : "";

        $new_user_data = $request->request;

        if ($error == "") {
            $user->setAddress($new_user_data->get("address"));
            $user->setEmail($new_user_data->get("email"));
            $user->setFirstName($new_user_data->get("first_name"));
            $user->setLastname($new_user_data->get("last_name"));
            $user->setCellphone($new_user_data->get("cellphone"));
            $user->setPhone($new_user_data->get("phone"));
            if (in_array("is_admin", $keys) and $new_user_data->get("is_admin") == "on")
                $user->setRoles(["ROLE_ADMIN"]);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->user_edit($username, $error);
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
