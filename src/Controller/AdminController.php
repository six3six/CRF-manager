<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Skill;
use App\Entity\User;
use App\Form\EventType;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class   AdminController extends AbstractController
{
    /**
     * @Route("/admin/timeline", name="admin_timeline")
     */
    public function index()
    {
        return $this->render('admin/timeline.html.twig');
    }

    /**
     * @Route("/admin/users/json", name="admin_user_list_api")
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
     * @Route("/admin/users", name="admin_user_list")
     */
    public function user_list()
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $all_users = $userRepo->findAll();
        return $this->render('admin/user/user_list.html.twig', [
            'controller_name' => 'AdminController',
            "users" => $all_users,
        ]);
    }

    /**
     * @Route("/admin/user/{username}/edit", name="admin_user_edit", methods={"GET"})
     * @param $username
     * @param string $error
     * @return Response
     */
    public function user_edit($username, $error = "")
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->findOneBy(["username" => $username]);
        if ($user == null) throw new NotFoundHttpException("Utilisateur non trouvé");
        return $this->render('admin/user/user_edit.html.twig', [
            'controller_name' => 'AdminController',
            "user" => $user,
            "error" => $error
        ]);
    }

    /**
     * @Route("/admin/user/{username}/edit", name="admin_user_edit_save", methods={"POST"})
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
     * @Route("/admin/user/{username}/planning", name="admin_user_planning")
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
                "start" => $av->getStart()->format(DateTime::ISO8601),
                "stop" => $av->getStop()->format(DateTime::ISO8601),
                "id" => $av->getId(),
                "backgroundColor" => "blue",
            ));
        }
        foreach ($user->getEvents() as $ev) {
            array_push($ret, array(
                "type" => "event",
                "start" => $ev->getStart()->format(DateTime::ISO8601),
                "stop" => $ev->getStop()->format(DateTime::ISO8601),
                "id" => $ev->getId(),
                "title" => $ev->getName(),
                "backgroundColor" => "red",
            ));
        }

        return new JsonResponse($ret);
    }

    /**
     * @Route("/admin/event", name="admin_events_view")
     */
    public function events_view()
    {
        return $this->render('admin/event/event.html.twig', [
        ]);
    }

    /**
     * @Route("/admin/event/list", name="admin_event_list")
     */
    public function event_list()
    {
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $all_events = $eventRepo->findAll();
        $ret = array();
        foreach ($all_events as $event) {
            array_push($ret, array(
                "title" => $event->getName(),
                "start" => $event->getStart()->format(PlanningController::PLANNING_FORMAT),
                "end" => $event->getStop()->format(PlanningController::PLANNING_FORMAT),
                "url" => $this->generateUrl("admin_event_edit", ["id" => $event->getId()])
            ));
        }
        return new JsonResponse($ret);
    }

    /**
     * @Route("/admin/event/new/{start}/{stop}", name="admin_event_new_start_stop")
     * @param Request $request
     * @param string $start
     * @param string $stop
     * @return Response
     * @throws Exception
     */
    public function event_start_stop(Request $request, $start, $stop)
    {
        $event = new Event();
        $event->setStart(new DateTime($start));
        $event->setStop(new DateTime($stop));
        return $this->event_new($request, $event);
    }

    /**
     * @Route("/planning/event/new/", name="admin_event_new")
     * @param Request $request
     * @param Event $event
     * @return Response
     */
    public function event_new(Request $request, $event = null)
    {
        if ($event == null) $event = new Event();

        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();
            $event->setCreatedBy($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('admin_events_view');
        }


        return $this->render('admin/event/edit.html.twig', [
            'form' => $form->createView(),
            'delete' => false,
        ]);
    }

    /**
     * @Route("/admin/event/delete/{id}", name="admin_event_delete")
     * @param $id
     * @return RedirectResponse
     */
    public function event_delete($id)
    {
        $repo = $this->getDoctrine()->getRepository(Event::class);
        $event = $repo->find($id);
        if ($event == null) throw new NotFoundHttpException("Evenement non trouvé");
        $this->getDoctrine()->getManager()->remove($event);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute("admin_events_view");
    }

    /**
     * @Route("/admin/event/{id}", name="admin_event_edit")
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function event_edit(Request $request, $id)
    {
        $repo = $this->getDoctrine()->getRepository(Event::class);
        $event = $repo->find($id);
        if ($event == null) throw new NotFoundHttpException("Disponibilité non trouvé : " . $id);
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('admin_events_view');
        }

        return $this->render('admin/event/edit.html.twig', [
            'form' => $form->createView(),
            'delete' => true,
            "id" => $event->getId(),
            "user" => $event->getCreatedBy()
        ]);
    }


    /**
     * @Route("/admin/skills", name="admin_skill_list")
     */
    public function skill_list()
    {
        $skillRepo = $this->getDoctrine()->getRepository(Skill::class);
        $all_skill = $skillRepo->findAll();
        return $this->render('admin/skill/skill_list.html.twig', [
            "skills" => $all_skill,
        ]);
    }

    /**
     * @Route("/admin/skill/insert", name="admin_skill_insert", methods={"GET"})
     * @param string $error
     * @return Response
     */
    public function skill_insert($error = "")
    {
        return $this->render('admin/skill/skill_insert.html.twig', [
            "error" => $error
        ]);
    }

    /**
     * @Route("/admin/skill/insert", name="admin_skill_insert_post", methods={"POST"})
     * @param Request $request
     * @param string $errors
     * @return Response
     */
    public function skill_insert_post(Request $request, $errors = "")
    {
        $keys = $request->request->keys();
        if (!in_array("name", $keys) || !in_array("description", $keys)) throw new BadRequestHttpException("Une clé est manquante");
        $name = $request->request->get("name");
        if ($name == "") return $this->skill_insert("Le nom ne peut pas être vide");

        $repo = $this->getDoctrine()->getRepository(Skill::class);
        $as = $repo->findOneBy(["name" => $name]);
        if ($as != null) return $this->skill_insert("Deux compétences ne peuvent pas avoir le même nom");

        $skill = new Skill();
        $skill->setName($name);
        $skill->setDescription($request->request->get("description"));

        $this->getDoctrine()->getManager()->persist($skill);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute("admin_skill_list");
    }

    /**
     * @Route("/admin/skill/modify/{id}", name="admin_skill_modify", methods={"GET"})
     * @param $id
     * @param string $error
     * @return Response
     */
    public function skill_modify($id, $error = "")
    {
        $repo = $this->getDoctrine()->getRepository(Skill::class);
        $skill = $repo->find($id);
        if ($skill == null) throw new NotFoundHttpException("Compétence non trouvée");
        return $this->render('admin/skill/skill_modify.html.twig', [
            "skill" => $skill,
            "error" => $error
        ]);
    }

    /**
     * @Route("/admin/skill/modify/{id}", name="admin_skill_modify_post", methods={"POST"})
     * @param Request $request
     * @param string $errors
     * @return Response
     */
    public function skill_modify_post(Request $request, $id, $errors = "")
    {
        $keys = $request->request->keys();
        if (!in_array("name", $keys) || !in_array("description", $keys)) throw new BadRequestHttpException("Une clé est manquante");
        $name = $request->request->get("name");
        $description = $request->request->get("description");


        $repo = $this->getDoctrine()->getRepository(Skill::class);
        $skill = $repo->find($id);
        if ($skill == null) throw new NotFoundHttpException("Compétence non trouvée");

        if ($name == "") return $this->skill_modify($id, "Le nom ne peut pas être vide");

        $as = $repo->findOneBy(["name" => $name]);
        if ($as != null and $as != $skill) return $this->skill_modify($id, "Deux compétences ne peuvent pas avoir le même nom");


        $skill->setName($name);
        $skill->setDescription($description);

        $this->getDoctrine()->getManager()->persist($skill);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute("admin_skill_list");
    }

    /**
     * @Route("/admin/skill/delete/{id}", name="admin_skill_delete")
     */
    public function skill_delete($id)
    {
        $repo = $this->getDoctrine()->getRepository(Skill::class);
        $skill = $repo->find($id);
        if ($skill == null) throw new NotFoundHttpException("Evenement non trouvé");
        $this->getDoctrine()->getManager()->remove($skill);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute("admin_skill_list");
    }
}
