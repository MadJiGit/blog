<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\User;
use BlogBundle\Form\ArticleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ArticleController extends Controller
{

	/**
	 * @Route("/article/create", name="article_create")
	 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function createAction(Request $request)
	{
		$article = new Article();
		$form = $this->createForm(ArticleType::class, $article);
		$form->handleRequest($request);


		if ($form->isSubmitted() && $form->isValid()){
			$currentUser = $this->getUser();
			$article->setAuthor($currentUser);
			$em = $this->getDoctrine()->getManager();
			$em->persist($article);
			$em->flush();

			return $this->redirectToRoute('blog_index');
		}

		return $this->render('article/create.html.twig',
			['form' => $form->createView()]);
	}

	/**
	 * @Route("/article/{id}", name="article_view")
	 * @param $id
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function viewArticle($id)
	{

		$article = $this->getDoctrine()->getRepository(Article::class)->find($id);

		return $this->render('article/article.html.twig', ['article' => $article]);

	}

	/**
	 * @Route("/article/edit/{id}", name="article_edit")
	 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
	 * @param Request $request
	 * @param $id
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function editAction(Request $request, $id)
	{
		$article = $this->getDoctrine()->getRepository(Article::class)->find($id);
		$form = $this->createForm(ArticleType::class, $article);
		$form->handleRequest($request);

		if ($article === null){
			return $this->redirectToRoute('blog_index');
		}

		/** @var User $currentUser */
		$currentUser = $this->getUser();

		if (!$currentUser->isAuthor($article) && !$currentUser->isAdmin()){
			return $this->redirectToRoute('blog_index');
		}

		if ($form->isSubmitted() && $form->isValid()){
			$currentUser = $this->getUser();
			$article->setAuthor($currentUser);
			$em = $this->getDoctrine()->getManager();
			$em->merge($article);
			$em->flush();

			return $this->redirectToRoute('blog_index');
		}

		return $this->render('article/edit.html.twig',
			['form' => $form->createView(),
				'article' => $article]);
	}

	/**
	 * @Route("/article/delete/{id}", name="article_delete")
	 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
	 * @param Request $request
	 * @param $id
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function deleteAction(Request $request, $id)
	{
		$article = $this
			->getDoctrine()
			->getRepository(Article::class)
			->find($id);

		if ($article === null){
			return $this->redirectToRoute('blog_index');
		}

		/** @var User $currentUser */
		$currentUser = $this->getUser();

		if (!$currentUser->isAuthor($article) && !$currentUser->isAdmin()){
			return $this->redirectToRoute('blog_index');
		}

		$form = $this->createForm(ArticleType::class, $article);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()){
			$currentUser = $this->getUser();
			$article->setAuthor($currentUser);
			$em = $this->getDoctrine()->getManager();
			$em->remove($article);
			$em->flush();

			return $this->redirectToRoute('blog_index');
		}

		return $this->render('article/delete.html.twig',
			['form' => $form->createView(),
				'article' => $article]);
	}
}
