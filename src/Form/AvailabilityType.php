<?php

namespace App\Form;

use App\Controller\PlanningController;
use App\Entity\Availability;
use App\Entity\Event;
use App\Repository\EventRepository;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvailabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $date_options = ["widget" => "single_text", 'attr' => ['class' => 'datepicker'], "format" => "dd/MM/yyyy HH:mm", "html5" => false];
        $builder
            ->add('start', DateTimeType::class, $date_options)
            ->add('stop', DateTimeType::class, $date_options)
            ->add('attached_to', EntityType::class, ['class' => Event::class, 'choice_label' => function ($event) {
                /**
                 * @var Event $event
                 */
                return $event->getName() . " (" . $event->getStart()->format(PlanningController::SHOW_FORMAT) . " - " . $event->getStop()->format(PlanningController::SHOW_FORMAT) . ")";
            },
                'query_builder' => function (EventRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where("e.stop >= '" . (new DateTime())->format(PlanningController::SQL_FORMAT) . "'")
                        ->orderBy('e.start', 'ASC');
                },
                'required' => false])
            ->add('save', SubmitType::class);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Availability::class,
        ]);
    }
}
