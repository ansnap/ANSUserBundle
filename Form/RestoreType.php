<?php

namespace ANS\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class RestoreType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email', array(
                    'constraints' => array(
                        new NotBlank(),
                        new Email(),
                    ),
                    'label' => 'Email',
                ))
                ->add('save', 'submit', array(
                    'label' => 'Восстановить',
        ));
    }

    public function getName()
    {
        return 'register';
    }

}
