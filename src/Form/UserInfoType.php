<?php

namespace App\Form;

use App\Entity\Skill;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('display_name')
            ->add('email', EmailType::class)
            ->add('nivol', TextType::class, ["required" => false, "empty_data" => ""])
            ->add('birthday', DateType::class, [
                "widget" => "single_text",
                'attr' => ['class' => 'datepicker-d'],
                "format" => "dd/MM/yyyy",
                "html5" => false
            ])
            ->add('cellphone', TelType::class, ["required" => false, "empty_data" => ""])
            ->add('phone', TelType::class, ["required" => false, "empty_data" => ""])
            ->add('address', TextareaType::class, ["required" => false, "empty_data" => ""])
            ->add('skills', EntityType::class, [
                'class' => Skill::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                "required" => false
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
