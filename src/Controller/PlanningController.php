<?php

namespace App\Controller;


use App\Entity\Availability;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @Route("/planning/source/{source}", name="planningSources")
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
}
