<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Skill;
use App\Entity\User;
use App\Form\EventType;
use App\Form\RegistrationFormType;
use App\Form\SkillType;
use App\Form\UserInfoType;
use App\Security\LoginFormAuthenticator;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class   AdminController extends AbstractController
{
    /**
     * @Route("/admin/timeline", name="admin_timeline")
     */
    public function index()
    {
        $repo = $this->getDoctrine()->getRepository(Skill::class);
        $skills = $repo->findAll();
        return $this->render('admin/timeline.html.twig', ["skills" => $skills]);
    }

    /**
     * @Route("/admin/user/register", name="admin_register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $authenticator
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute("admin_user_view");
        }

        return $this->render('admin/user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/users/json", name="admin_user_list_api")
     */
    public function user_list_api()
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $users = $userRepo->findAll();
        $ret = array();
        foreach ($users as $user) {
            /**
             * @var User $user
             */
            $skills = array();
            foreach ($user->getSkills() as $skill) {
                $skills[$skill->getId()] = true;
            }
            array_push($ret, array(
                "name" => $user->getDisplayName(),
                "username" => $user->getUsername(),
                "skills" => $skills
            ));
        }
        return new JsonResponse($ret);
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
     * @Route("/admin/user", name="admin_user_view")
     */
    public function user_view()
    {
        $repo = $this->getDoctrine()->getRepository(User::class);
        $users = $repo->findAll();
        return $this->render('admin/user/user_list.html.twig', [
            "users" => $users,
        ]);
    }

    /**
     * @Route("/admin/user/delete/{username}", name="admin_user_delete")
     */
    public function user_delete($username)
    {
        $repo = $this->getDoctrine()->getRepository(User::class);
        $user = $repo->find($username);
        if ($user == null) throw new NotFoundHttpException("Utilisateur non trouvé");
        $this->getDoctrine()->getManager()->remove($user);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute("admin_user_view");
    }

    /**
     * @Route("/admin/user/{username}", name="admin_user_edit")
     * @param Request $request
     * @param $username
     * @return Response
     */
    public function user_edit(Request $request, $username)
    {
        $repo = $this->getDoctrine()->getRepository(User::class);
        $user = $repo->findOneBy(["username" => $username]);
        if ($user == null) throw new NotFoundHttpException("Utilisateur non trouvé : " . $username);
        $form = $this->createForm(UserInfoType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_user_view');
        }

        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView(),
            'delete' => true,
            "user" => $user
        ]);
    }

    /**
     * @Route("/admin/event", name="admin_events_view")
     */
    public function events_view()
    {
        return $this->render('admin/event/event.html.twig');
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
     * @Route("/admin/event/new/", name="admin_event_new")
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
     * @Route("/admin/skill", name="admin_skill_view")
     */
    public function skill_list()
    {
        $skillRepo = $this->getDoctrine()->getRepository(Skill::class);
        $all_skill = $skillRepo->findAll();
        return $this->render('admin/skill/list.html.twig', [
            "skills" => $all_skill,
        ]);
    }

    /**
     * @Route("/admin/skill/new", name="admin_skill_new")
     * @param Request $request
     * @return Response
     */
    public function skill_new(Request $request)
    {
        $skill = new Skill();

        $form = $this->createForm(SkillType::class, $skill);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $skill = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($skill);
            $entityManager->flush();

            return $this->redirectToRoute('admin_skill_view');
        }


        return $this->render('admin/skill/edit.html.twig', [
            'form' => $form->createView(),
            'delete' => false,
        ]);
    }

    /**
     * @Route("/admin/skill/delete/{id}", name="admin_skill_delete")
     */
    public function skill_delete($id)
    {
        $repo = $this->getDoctrine()->getRepository(Skill::class);
        $skill = $repo->find($id);
        if ($skill == null) throw new NotFoundHttpException("Compétence non trouvée");
        $this->getDoctrine()->getManager()->remove($skill);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute("admin_skill_view");
    }

    /**
     * @Route("/admin/skill/{id}", name="admin_skill_edit")
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function skill_edit(Request $request, $id)
    {
        $repo = $this->getDoctrine()->getRepository(Skill::class);
        $skill = $repo->find($id);
        if ($skill == null) throw new NotFoundHttpException("Compétence non trouvée : " . $id);

        $form = $this->createForm(SkillType::class, $skill);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $skill = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($skill);
            $entityManager->flush();

            return $this->redirectToRoute('admin_skill_view');
        }

        return $this->render('admin/skill/edit.html.twig', [
            'form' => $form->createView(),
            'delete' => true,
            "id" => $skill->getId()
        ]);
    }
}
