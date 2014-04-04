<?php

namespace ANS\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use ANS\UserBundle\Form\RegisterType;
use ANS\UserBundle\Form\RestoreType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;

class UserController extends Controller
{

    /**
     * Rendering login form and submission
     * login_check and logout actions - processing automatically
     */
    public function loginAction(Request $request)
    {
        // If not guest -> redirect to home
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($request->getBaseUrl());
        }

        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                    SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render('ANSUserBundle:User:login.html.twig', array(
                    // last username entered by the user
                    'last_email' => $session->get(SecurityContext::LAST_USERNAME),
                    'error' => $error,
        ));
    }

    /**
     * User registration
     */
    public function registerAction(Request $request)
    {
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($request->getBaseUrl());
        }

        $user_class = $this->container->getParameter('ans_user.user_class');
        $user = new $user_class;

        $form = $this->createForm(new RegisterType(), $user);

        // If form was submitted
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $em = $this->getDoctrine()->getManager();

            // Antispam - vs bots
            if ($form->get('website')->getData() || !$request->get('isjs')) {
                $form->addError(new FormError('Регистрация не удалась. Повторите запрос позже'));
            }

            if ($em->getRepository($user_class)->findOneByEmail($user->getEmail())) {
                $form->get('email')->addError(new FormError('Пользователь с указанным email уже существует'));
            }

            if ($form->isValid()) {
                $site_name = $this->container->getParameter('ans_user.site_name');
                $site_email = $this->container->getParameter('ans_user.site_email');

                // Send email
                $message = \Swift_Message::newInstance()
                        ->setSubject('Регистрация на сайте ' . $site_name)
                        ->setFrom($site_email)
                        ->setTo($user->getEmail())
                        ->setBody(
                        $this->renderView('ANSUserBundle:User:mail/register.html.twig', array(
                            'site_name' => $site_name,
                            'name' => $user->getName(),
                            'password' => $user->getPlainPassword(),
                        ))
                );
                $this->get('mailer')->send($message);

                // Save user
                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Вы успешно зарегистрированы и, по электронной почте, Вам отправлен пароль. Используйте его для входа на сайт.');

                return $this->redirect($this->generateUrl('login'));
            }
        }

        return $this->render('ANSUserBundle:User:register.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * Send link to confirm changing password
     */
    public function restoreAction(Request $request)
    {
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($request->getBaseUrl());
        }

        $form = $this->createForm(new RestoreType());

        // If form was submitted
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $em = $this->getDoctrine()->getManager();

            $user_class = $this->container->getParameter('ans_user.user_class');
            $user = $em->getRepository($user_class)->findOneByEmail($form->get('email')->getData());

            if (!$user) {
                $form->get('email')->addError(new FormError('Не найдено зарегистрированных пользователей с указанным email'));
            }

            if ($form->isValid()) {
                $token_class = $this->container->getParameter('ans_user.token_class');
                $token = new $token_class;

                $token->setUser($user);

                $link = $this->generateUrl('restore_confirm', array('token' => $token->getCode()), true);

                $site_name = $this->container->getParameter('ans_user.site_name');
                $site_email = $this->container->getParameter('ans_user.site_email');

                $message = \Swift_Message::newInstance()
                        ->setSubject('Восстановление пароля на сайте ' . $site_name)
                        ->setFrom($site_email)
                        ->setTo($user->getEmail())
                        ->setBody(
                        $this->renderView('ANSUserBundle:User:mail/restore.html.twig', array(
                            'site_name' => $site_name,
                            'name' => $user->getName(),
                            'link' => $link,
                        ))
                );
                $this->get('mailer')->send($message);

                $em->persist($token);
                $em->flush();

                return $this->render('ANSUserBundle:User:restore.html.twig', array(
                            'message' => 'Success',
                ));
            }
        }

        return $this->render('ANSUserBundle:User:restore.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * Change password if confirmed
     */
    public function restoreConfirmAction(Request $request, $code)
    {
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($request->getBaseUrl());
        }

        $em = $this->getDoctrine()->getManager();

        $token_class = $this->container->getParameter('ans_user.token_class');
        $token = $em->getRepository($token_class)->findOneByCode($code);

        if (!$token) {
            throw $this->createNotFoundException('Не найден токен с кодом ' . $code);
        }

        $user = $token->getUser();
        $user->setPlainPassword($user->generatePassword());

        $site_name = $this->container->getParameter('ans_user.site_name');
        $site_email = $this->container->getParameter('ans_user.site_email');

        // Send email
        $message = \Swift_Message::newInstance()
                ->setSubject('Новый пароль для сайта ' . $site_name)
                ->setFrom($site_email)
                ->setTo($user->getEmail())
                ->setBody(
                $this->renderView('ANSUserBundle:User:mail/restoreConfirm.html.twig', array(
                    'site_name' => $site_name,
                    'name' => $user->getName(),
                    'password' => $user->getPlainPassword(),
                ))
        );
        $this->get('mailer')->send($message);

        // Save user, delete token
        $em->remove($token);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Ваш запрос подтвержден. На email Вам отправлен новый пароль для входа на сайт.');

        return $this->redirect($this->generateUrl('login'));
    }

}
