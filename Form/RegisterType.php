<?php

namespace ANS\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegisterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email', array(
                    'label' => 'Ваш Email',
                    'attr' => array(
                        'placeholder' => 'Адрес электронной почты',
                    ),
                ))
                ->add('name', 'text', array(
                    'label' => 'Ваше имя',
                    'attr' => array(
                        'placeholder' => 'Отображаемое на сайте имя',
                    ),
                ))
                ->add('website', 'text', array(// Only for security - vs bots, must be empty
                    'required' => false,
                    'mapped' => false,
                    'label' => 'Сайт',
                ))
                ->add('register', 'submit', array(
                    'label' => 'Зарегистрироваться',
        ));
    }

    public function getName()
    {
        return 'register';
    }

}
