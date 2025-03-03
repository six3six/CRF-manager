<?php

namespace App\Form;

use App\Entity\PlanningEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $date_options = ["widget" => "single_text", 'attr' => ['class' => 'datepicker'], "format" => "dd/MM/yyyy HH:mm", "html5" => false];
        $builder
            ->add('start', DateTimeType::class, $date_options)
            ->add('stop', DateTimeType::class, $date_options)
            ->add('is_event', CheckboxType::class, ["attr" => ["class" => "is-event"], 'required' => false])
            ->add('name', TextType::class, ["attr" => ["class" => "event-field"], "required" => false, "empty_data" => ""])
            ->add("state", ChoiceType::class, [
                "choices" => ["Waiting" => PlanningEntry::STATE_WAITING, "Valid" => PlanningEntry::STATE_VALID],
                "multiple" => false,
                "expanded" => true,
                "attr" => ["class" => "event-field"]
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PlanningEntry::class,
            'is_event' => false,
            'name' => "",
            'state' => PlanningEntry::STATE_UNKNOWN
        ]);
    }
}
