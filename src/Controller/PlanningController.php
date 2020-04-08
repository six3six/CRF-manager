<?php

namespace App\Controller;


use App\Entity\Availability;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\DBAL\Exception\ServerException;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PlanningController extends AbstractController
{
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
                    "start" => $event->getStart()->format(\DateTime::ISO8601),
                    "end" => $event->getStop()->format(\DateTime::ISO8601),
                );
                array_push($calendar, $f_event);
            }
        } elseif ($source == "availabilities") {
            foreach ($availabilities as $availability) {
                $f_event = array(
                    "title" => $availability->getName(),
                    "start" => $availability->getStart()->format(\DateTime::ISO8601),
                    "end" => $availability->getStop()->format(\DateTime::ISO8601),
                );
                array_push($calendar, $f_event);
            }
        } elseif ($source == "tests") {
            array_push($calendar, array(
                "title" => "Test",
                "start" => (new \DateTime())->format(\DateTime::ISO8601),
                "end" => (new \DateTime("2020-04-10"))->format(\DateTime::ISO8601)
            ));
        } else {
            throw new NotFoundHttpException("Source not found");
        }

        return new JsonResponse($calendar);
    }

    /**
     * @Route("/planning/insert", name="planning_insert", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function insert(Request $request)
    {
        try {
            $start = new \DateTime($request->server->get("start"));
            $stop = new \DateTime($request->server->get("stop"));
        } catch (Exception $e) {
            return new JsonResponse(array("error" => "Bad time encoding"));
        }

        $av = new Availability();
        $av->setStart($start);
        $av->setStop($stop);
        $av->setUser($this->getUser());

        return new JsonResponse(array("response" => "ok"));
    }
}
