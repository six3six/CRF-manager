<?php

namespace App\Controller;


use App\Entity\Availability;
use App\Repository\AvailabilityRepository;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
        return $this->render('planning/index.html.twig', [
            'controller_name' => 'PlanningController',
        ]);
    }

    /**
     * @Route("/planning/source/{source}", name="planning_sources")
     * @param $source
     * @return JsonResponse
     * @throws Exception
     */
    public function source($source)
    {
        $calendar = array();
        $user = $this->getUser();

        $events = $user->getEvents();
        $availabilities = $user->getAvailabilities();
        if ($source == "events") {
            foreach ($events as $event) {
                $f_event = array(
                    "title" => $event->getName(),
                    "start" => $event->getStart()->format(PlanningController::PLANNING_FORMAT),
                    "end" => $event->getStop()->format(PlanningController::PLANNING_FORMAT),
                    "id" => $event->getId(),
                    "type" => "event"
                );
                array_push($calendar, $f_event);
            }
        } elseif ($source == "availabilities") {
            foreach ($availabilities as $availability) {
                $f_event = array(
                    "title" => "Disponibilité",
                    "start" => $availability->getStart()->format(PlanningController::PLANNING_FORMAT),
                    "end" => $availability->getStop()->format(PlanningController::PLANNING_FORMAT),
                    "url" => "/planning/modify/" . $availability->getId(),
                );
                array_push($calendar, $f_event);
            }
        } elseif ($source == "tests") {
            array_push($calendar, array(
                "title" => "Test",
                "start" => (new \DateTime())->format(PlanningController::PLANNING_FORMAT),
                "end" => (new \DateTime("2020-04-10"))->format(PlanningController::PLANNING_FORMAT)
            ));
        } else {
            throw new NotFoundHttpException("Source not found");
        }

        return new JsonResponse($calendar);
    }

    /**
     * @Route("/planning/modify/{id}", methods={"GET"}, name="planning_modify")
     * @param string $error
     * @return Response
     */
    public function modify($id, $error = "")
    {
        $repo = $this->getDoctrine()->getRepository(Availability::class);
        $availability = $repo->find($id);
        if (!$availability) throw new NotFoundHttpException("La disponibilité n'existe pas");
        if ($availability->getUser() !== $this->getUser() && !$this->getUser()->isAdmin()) throw new UnauthorizedHttpException("", "Vous n'avez pas le droit de modifier cette disponibilité");

        return $this->render('planning/modify.html.twig', [
            'controller_name' => 'PlanningController',
            'error' => $error,
            'availability' => $availability,
            'time_format' => PlanningController::SHOW_FORMAT
        ]);
    }

    /**
     * @Route("/planning/modify/{id}", methods={"POST"}, name="planning_modify_api")
     * @param Request $request
     * @param $id
     * @param string $error
     * @return Response
     */
    public function modify_api(Request $request, $id, $error = "")
    {
        $repo = $this->getDoctrine()->getRepository(Availability::class);
        $availability = $repo->find($id);
        if (!$availability) throw new NotFoundHttpException("La disponibilité n'existe pas");
        if ($availability->getUser() !== $this->getUser() && !$this->getUser()->isAdmin()) throw new UnauthorizedHttpException("", "Vous n'avez pas le droit de modifier cette disponibilité");

        $start_text = $request->request->get("start");
        $stop_text = $request->request->get("stop");

        if (!$start_text) return $this->insert($request, "Date de début manquante");
        if (!$stop_text) return $this->insert($request, "Date de fin manquante");
        try {
            $start = $this->dateTimePick2php($start_text);
            $stop = $this->dateTimePick2php($stop_text);
        } catch (Exception $e) {
            return $this->insert($request, $e->getMessage());
        }

        $availability->setStart($start);
        $availability->setStop($stop);

        $this->getDoctrine()->getManager()->persist($availability);
        $this->getDoctrine()->getManager()->flush();

        return new RedirectResponse("/planning");
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
        return new RedirectResponse("/planning");
    }

    /**
     * @Route("/planning/insert", name="planning_insert_api", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function insertAPI(Request $request)
    {
        $start_text = $request->request->get("start");
        $stop_text = $request->request->get("stop");

        if (!$start_text) return $this->insert($request, "Date de début manquante");
        if (!$stop_text) return $this->insert($request, "Date de fin manquante");
        try {
            $start = $this->dateTimePick2php($start_text);
            $stop = $this->dateTimePick2php($stop_text);
        } catch (Exception $e) {
            return $this->insert($request, $e->getMessage());
        }


        $av = new Availability();
        $av->setStart($start);
        $av->setStop($stop);
        $av->setUser($this->getUser());

        $this->getDoctrine()->getManager()->persist($av);
        $this->getDoctrine()->getManager()->flush();

        return new RedirectResponse('/planning');
    }

    private function dateTimePick2php($text): \DateTime
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


        $date = new \DateTime();

        $date->setDate((int)$year, (int)$month, (int)$day);
        $date->setTime((int)$hour, (int)$minute, 0);

        return $date;
    }

    /**
     * @Route("/planning/insert", name="planning_insert", methods={"GET"})
     * @param Request $request
     * @param string $error
     * @return Response
     */
    public function insert(Request $request, string $error = "")
    {
        return $this->render('planning/insert.html.twig', [
            'controller_name' => 'PlanningController',
            'error' => $error
        ]);
    }
}
