<?php
/**
 * Tag controller.
 */
namespace Controller;

use Form\TagType;
use Repository\TagRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


/**
 * Class TagController.
 */
class TagController implements ControllerProviderInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function connect(Application $app)
	{
		$controller = $app['controllers_factory'];

		$controller->get('/', [$this, 'indexAction'])->bind('tag_index');

		$controller->get('/page/{page}', [$this, 'indexAction'])
		           ->value('page', 1)
		           ->bind('tag_index_paginated');

		$controller->get('/{id}', [$this, 'viewAction'])
		           ->assert('id', '[1-9]\d*')
		           ->bind('tag_view');

		$controller->match('/add', [$this, 'addAction'])
		           ->method('POST|GET')
		           ->bind('tag_add');

		$controller->match('/{id}/edit', [$this, 'editAction'])
		           ->method('GET|POST')
		           ->assert('id', '[1-9]\d*')
		           ->bind('tag_edit');

		$controller->match('/{id}/delete', [$this, 'deleteAction'])
		           ->method('GET|POST')
		           ->assert('id', '[1-9]\d*')
		           ->bind('tag_delete');

		return $controller;
	}

	/**
	 * Index action.
	 *
	 * @param \Silex\Application $app Silex application
	 *
	 * @return \Symfony\Component\HttpFoundation\Response HTTP Response
	 */
	public function indexAction(Application $app, $page = 1)
	{
		$tagRepository = new TagRepository($app['db']);

		return $app['twig']->render(
			'tag/index.html.twig',
			['paginator' => $tagRepository->findAllPaginated($page)]
		);

		/*return $app['twig']->render(
			'tag/index.html.twig',
			['tags' => $tagRepository->findAll()]
		);*/
	}

	/**
	 * View action.
	 *
	 * @param \Silex\Application $app Silex application
	 * @param string             $id  Element Id
	 *
	 * @return \Symfony\Component\HttpFoundation\Response HTTP Response
	 */
	public function viewAction(Application $app, $id)
	{
		$tagRepository = new TagRepository($app['db']);

		return $app['twig']->render(
			'tag/view.html.twig',
			['tag' => $tagRepository->findOneById($id)]
		);
	}

	public function addAction(Application $app, Request $request) {
		$tag = [];

		$form = $app['form.factory']->createBuilder(
			TagType::class,
			$tag
			//['tag_repository' => new TagRepository($app['db'])]
		)->getForm();

		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid()) {
			$tagRepository = new TagRepository($app['db']);
			$tagRepository->save($form->getData());

			$app['session']->getFlashBag()->add(
				'messages',
				[
					'type' => 'success',
					'message' => 'message.element_successfully_added',
				]
			);

			return $app->redirect($app['url_generator']->generate('tag_index'), 301);
		}

		return $app['twig']->render(
			'tag/add.html.twig',
			[
				'tag' => $tag,
				'form' => $form->createView()
			]
		);
	}

	public function editAction(Application $app, $id ,  Request $request) {

		$tagRepository = new TagRepository($app['db']);
		$tag = $tagRepository->findOneById($id);

		if(!$tag) {
			$app['session']->getFlashBag->add(
				'messages',
				[
					'type' => 'warning',
					'message' => 'message.record_not_found'
				]
			);

			return $app->redirect($app['url_generator']->generate('tag_index'));
		}

		$form = $app['form.factory']->createBuilder(
			TagType::class,
			$tag
			//['tag_repository' => new TagRepository($app['db'])]
		)->getForm();

		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid()) {

			$tagRepository->save($form->getData());

			$app['session']->getFlashBag()->add(
				'messages',
				[
					'type' => 'success',
					'message' => 'message.element_successfully_edited'
				]
			);

			return $app->redirect($app['url_generator']->generate('tag_index'), 301);

		}

		return $app['twig']->render(
			'tag/edit.html.twig',
			[
				'tag' => $tag,
				'form' => $form->createView(),
			]
		);

	}


	public function deleteAction(Application $app, $id, Request $request) {

		$tagRepository = new TagRepository($app['db']);
		$tag = $tagRepository->findOneById($id);

		if(!$tag) {
			$app['session']->getFlashBag()->add(
				'messages',
				[
					'type' => 'warning',
					'message' => 'message.record_not_found'
				]
			);

			return $app->redirect($app['url_generator']->generate('tag_index'));
		}

		$form = $app['form.factory']->createBuilder(FormType::class, $tag)->add('id', HiddenType::class)->getForm();
		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid()) {

			$tagRepository->delete($form->getData());

			$app['session']->getFlashBag()->add(
				'messages',
				[
					'type' => 'success',
					'message' => 'message.element_successfully_deleted',
				]
			);

			return $app->redirect(
				$app['url_generator']->generate('tag_index'),
				301
			);

		}

		return $app['twig']->render(
			'tag/delete.html.twig',
			[
				'tag' => $tag,
				'form' => $form->createView(),
			]
		);

	}
}