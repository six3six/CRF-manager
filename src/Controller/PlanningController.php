<?php

namespace App\Controller;


use App\Entity\Availability;
use App\Form\AvailabilityType;
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

        $events = $user->getEvents();
        $availabilities = $user->getAvailabilities();

        foreach ($events as $event) {
            $f_event = array(
                "title" => $event->getName(),
                "start" => $event->getStart()->format(PlanningController::PLANNING_FORMAT),
                "end" => $event->getStop()->format(PlanningController::PLANNING_FORMAT),
                "url" => "/planning/event/" . $event->getId(),
                "backgroundColor" => "red",
            );
            array_push($calendar, $f_event);
        }

        foreach ($availabilities as $availability) {
            $f_event = array(
                "title" => "Disponibilité",
                "start" => $availability->getStart()->format(PlanningController::PLANNING_FORMAT),
                "end" => $availability->getStop()->format(PlanningController::PLANNING_FORMAT),
                "url" => "/planning/availability/" . $availability->getId(),
                "backgroundColor" => "green",
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
        $repo = $this->getDoctrine()->getRepository(Availability::class);
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
     * @Route("/planning/availability/new/{start}/{stop}", name="planning_availability_new_start_stop")
     * @param Request $request
     * @param string $start
     * @param string $stop
     * @return Response
     * @throws Exception
     */
    public function availability_start_stop(Request $request, $start, $stop)
    {
        $availability = new Availability();
        $availability->setStart(new DateTime($start));
        $availability->setStop(new DateTime($stop));
        return $this->availability_new($request, $availability);
    }

    /**
     * @Route("/planning/availability/new/", name="planning_availability_new")
     * @param Request $request
     * @param Availability $availability
     * @return Response
     */
    public function availability_new(Request $request, $availability = null)
    {
        if ($availability == null) $availability = new Availability();

        $form = $this->createForm(AvailabilityType::class, $availability);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $availability = $form->getData();
            $availability->setUser($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($availability);
            $entityManager->flush();

            return $this->redirectToRoute('planning');
        }


        return $this->render('planning/edit.html.twig', [
            'form' => $form->createView(),
            'delete' => false,
        ]);
    }

    /**
     * @Route("/planning/availability/{id}", name="planning_availability_edit")
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function availability_edit(Request $request, $id)
    {
        $repo = $this->getDoctrine()->getRepository(Availability::class);
        $availability = $repo->find($id);
        if ($availability == null) throw new NotFoundHttpException("Disponibilité non trouvé");
        if ($availability->getUser() != $this->getUser() && !$this->getUser()->isAdmin()) throw new AccessDeniedException();
        $form = $this->createForm(AvailabilityType::class, $availability);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $availability = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($availability);
            $entityManager->flush();

            return $this->redirectToRoute('planning');
        }

        return $this->render('planning/edit.html.twig', [
            'form' => $form->createView(),
            'delete' => true,
            "id" => $availability->getId(),
            "user" => $availability->getUser()
        ]);
    }

}
