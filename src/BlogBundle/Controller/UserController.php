<?php

// this comment is only for github commit test
// end here

namespace BlogBundle\Controller;

use BlogBundle\Entity\Role;
use BlogBundle\Entity\User;
use BlogBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
	/**
	 * @Route("/register", name="user_register")
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */

	public function registerAction(Request $request)
	{
		$user = new User();
		$form = $this->createForm(UserType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted()){

			$password = $this->get('security.password_encoder')
				->encodePassword($user, $user->getPassword());

			$user->setPassword($password);

			$role = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['name' => 'ROLE_USER']);

			$user->addRole($role);


			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();
			return $this->redirectToRoute('security_login');
		}

		return $this->render('user/register.html.twig');
	}

	/**
	 * @Route("/profile", name="user_profile")
	 */
	public function profile()
	{
		$userId = $this->getUser()->getId();
		$user = $this->getDoctrine()
				->getRepository(User::class)
				->find($userId);

		return $this->render('user/profile.html.twig', ['user' => $user]);
	}

}

