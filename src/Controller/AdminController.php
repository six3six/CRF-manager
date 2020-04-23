<?php

namespace App\Controller;

use App\Entity\PlanningEntry;
use App\Entity\Skill;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\SkillType;
use App\Form\UserInfoType;
use App\Security\LoginFormAuthenticator;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @param LoggerInterface $logger
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator, LoggerInterface $logger): Response
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
            if ($form->get("is_admin")->getData()) {
                $user->setRoles(["ROLE_USER", "ROLE_ADMIN"]);
            }

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
        /**
         * @var User $user
         */
        $user = $userRepo->findOneBy(["username" => $username]);
        if ($user == null) throw new NotFoundHttpException("Utilisateur non trouvé");

        $ret = array();

        foreach ($user->getPlanningEntries() as $planningEntry) {
            /**
             * @var PlanningEntry $planningEntry
             */
            if ($planningEntry->getIsEvent()) {
                $title = "Evt:" . $planningEntry->getName() . " ";
                switch ($planningEntry->getState()) {
                    case PlanningEntry::STATE_MOD_WAITING:
                    case PlanningEntry::STATE_WAITING:
                        $class_name = "waiting";
                        $title .= "(en cours de validation)";
                        break;
                    case PlanningEntry::STATE_VALID:
                        $class_name = "valid";
                        $title .= "(validé)";
                        break;
                    default:
                        $class_name = "unknown";
                        $title .= "(inconnu)";
                        break;
                }
            } else {
                $class_name = "availability";
                $title = "Disponibilité";
            }
            array_push($ret, array(
                "type" => $title,
                "name" => $title,
                "start" => $planningEntry->getStart()->format(DateTime::ISO8601),
                "stop" => $planningEntry->getStop()->format(DateTime::ISO8601),
                "id" => $planningEntry->getId(),
                "className" => "timeline-" . $class_name,
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
        return $this->skill($request);
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
        return $this->skill($request, $skill);
    }

    public function skill(Request $request, Skill $skill = null)
    {
        $new = false;
        if ($skill == null) {
            $skill = new Skill();
            $new = true;
        }

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
            'new' => $new,
            "id" => $skill->getId()
        ]);
    }

    /**
     * @Route("/admin/entry/{id}", name="admin_entry_edit")
     * @param Request $request
     * @param LoggerInterface $logger
     * @param Integer|String $id
     * @return Response
     */
    public function planning_entry_admin(Request $request, LoggerInterface $logger, $id)
    {
        $repo = $this->getDoctrine()->getRepository(PlanningEntry::class);
        /**
         * @var PlanningEntry $planningEntry
         */
        $planningEntry = $repo->find($id);
        if ($planningEntry == null) throw new NotFoundHttpException("Disponibilité non trouvée");
        return $this->forward("App\Controller\PlanningController::planning_entry", ["planningEntry" => $planningEntry, "new" => false, "redirectRoute" => "close"]);
    }
}
