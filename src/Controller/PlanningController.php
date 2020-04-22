<?php

namespace App\Controller;


use App\Entity\PlanningEntry;
use App\Form\PlanningEntryType;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PlanningController extends AbstractController
{
    const PLANNING_FORMAT = "Y-m-d\TH:i:s";
    const SHOW_FORMAT = "d/m/Y H:i";
    const SQL_FORMAT = "Y/m/d H:i:s";

    /**
     * @Route("/planning", name="planning")
     */
    public function index()
    {
        return $this->render('planning/index.html.twig');
    }

    /**
     * @Route("/planning/source", name="planning_source")
     * @return JsonResponse
     * @throws Exception
     */
    public function source()
    {
        $calendar = array();
        $user = $this->getUser();

        $planningEntries = $user->getPlanningEntries();
        foreach ($planningEntries as $planningEntry) {
            /**
             * @var PlanningEntry $planningEntry
             */
            $backgnd = "";
            $title = "Disponibilité ";
            switch ($planningEntry->getState()) {
                case PlanningEntry::STATE_MOD_WAITING or PlanningEntry::STATE_WAITING:
                    $backgnd = "orange";
                    $title .= "(en cours de validation)";
                    break;
                case PlanningEntry::STATE_VALIDATE:
                    $backgnd = "green";
                    $title .= "(validé)";
                    break;
                default:
                    $backgnd = "grey";
                    $title .= "(inconnu)";
                    break;
            }
            $f_event = array(
                "title" => $title,
                "start" => $planningEntry->getStart()->format(PlanningController::PLANNING_FORMAT),
                "end" => $planningEntry->getStop()->format(PlanningController::PLANNING_FORMAT),
                "url" => $this->generateUrl("planning_entry_edit", ["id" => $planningEntry->getId()]),
                "backgroundColor" => $backgnd,
            );
            array_push($calendar, $f_event);
        }
        return new JsonResponse($calendar);
    }


    /**
     * @Route("/planning/delete/{id}", methods={"GET"}, name="planning_delete")
     * @param $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        $repo = $this->getDoctrine()->getRepository(PlanningEntry::class);
        $availability = $repo->find($id);
        if (!$availability) throw new NotFoundHttpException("La disponibilité n'existe pas");
        if ($availability->getUser() !== $this->getUser() && !$this->getUser()->isAdmin()) throw new UnauthorizedHttpException("", "Vous n'avez pas le droit de modifier cette disponibilité");
        $this->getDoctrine()->getManager()->remove($availability);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute("planning");
    }

    private function dateTimePick2php($text): DateTime
    {
        $zones = explode(" ", $text);

        $date_z = $zones[0];
        $date_d = explode("/", $date_z);
        $year = $date_d[2];
        $month = $date_d[1];
        $day = $date_d[0];

        $hour_z = $zones[1];
        $hour_d = explode(":", $hour_z);
        $hour = $hour_d[0];
        $minute = $date_d[1];


        $date = new DateTime();

        $date->setDate((int)$year, (int)$month, (int)$day);
        $date->setTime((int)$hour, (int)$minute, 0);

        return $date;
    }

    /**
     * @Route("/planning/entry/new/{start}/{stop}", name="planning_entry_new_start_stop")
     * @param Request $request
     * @param string $start
     * @param string $stop
     * @return Response
     * @throws Exception
     */
    public function entry_start_stop(Request $request, $start, $stop)
    {
        $planningEntry = new PlanningEntry();
        $planningEntry->setStart(new DateTime($start));
        $planningEntry->setStop(new DateTime($stop));
        return $this->availability_new($request, $planningEntry);
    }

    /**
     * @Route("/planning/entry/new/", name="planning_availability_new")
     * @param Request $request
     * @param PlanningEntry|null $planningEntry
     * @return Response
     */
    public function availability_new(Request $request, $planningEntry = null)
    {
        if ($planningEntry == null) $planningEntry = new PlanningEntry();

        $form = $this->createForm(PlanningEntryType::class, $planningEntry);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $planningEntry = $form->getData();
            $planningEntry->setUser($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($planningEntry);
            $entityManager->flush();

            return $this->redirectToRoute('planning');
        }


        return $this->render('planning/edit.html.twig', [
            'form' => $form->createView(),
            'delete' => false,
        ]);
    }

    /**
     * @Route("/planning/entry/{id}", name="planning_entry_edit")
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function entry_edit(Request $request, $id)
    {
        $repo = $this->getDoctrine()->getRepository(PlanningEntry::class);
        /**
         * @var PlanningEntry $planningEntry
         */
        $planningEntry = $repo->find($id);
        if ($planningEntry == null) throw new NotFoundHttpException("Disponibilité non trouvé");
        if ($planningEntry->getUser() != $this->getUser() && !$this->getUser()->isAdmin()) throw new AccessDeniedException();
        $form = $this->createForm(PlanningEntryType::class, $planningEntry);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $planningEntry = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($planningEntry);
            $entityManager->flush();

            return $this->redirectToRoute('planning');
        }

        return $this->render('planning/edit.html.twig', [
            'form' => $form->createView(),
            'delete' => true,
            "id" => $planningEntry->getId(),
            "user" => $planningEntry->getUser(),
            "state" => $planningEntry->getState()
        ]);
    }
}
