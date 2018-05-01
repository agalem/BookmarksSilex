<?php
/**
 * Created by PhpStorm.
 * User: agalempaszek
 * Date: 15.04.2018
 * Time: 20:39
 */
namespace Controller;

use Form\BookmarkType;
use Repository\BookmarksRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class BookmarksController implements ControllerProviderInterface {

	public function connect( Application $app ) {

		$controller = $app['controllers_factory'];

		$controller->get('/', [$this, 'indexAction'])->bind('bookmarks_index');

		$controller->get('/page/{page}', [$this, 'indexAction'])
			->value('page', 1)
			->bind('bookmarks_index_paginated');

		$controller->get('/{id}', [$this, 'viewAction'])
		           ->assert('id', '[1-9]\d*')
		           ->bind('bookmarks_view');

		$controller->match('/add', [$this, 'addAction'])
		           ->method('POST|GET')
		           ->bind('bookmark_add');

		return $controller;
	}

	public function indexAction(Application $app, $page = 1){

		$bookmarksRepository = new BookmarksRepository($app['db']);

		return $app['twig']->render(
			'bookmarks/index.html.twig',
			['paginator' => $bookmarksRepository->findAllPaginated($page)]
		);

		/*$bookmarksModel = new Bookmarks();

		return $app['twig']->render(
			'bookmarks/index.html.twig',
			['bookmarks' => $bookmarksModel->findAll()]
		);*/
	}

	public function viewAction(Application $app, Request $request) {
		$bookmarksRepository = new BookmarksRepository($app['db']);
		$id = $request->get('id', '');

		return $app['twig']->render(
			'bookmarks/view.html.twig',
			['bookmark' => $bookmarksRepository->findOneById($id)]
		);

		/*$bookmarksModel = new Bookmarks();
		$id = $request->get('id', '');

		return $app['twig']->render(
			'bookmarks/view.html.twig',
			['bookmark' => $bookmarksModel->findOneById($id)]
		);*/
	}

	public function addAction(Application $app, Request $request) {
		$bookmark = [];

		$form = $app['form.factory']->createBuilder(BookmarkType::class, $bookmark)->getForm();
		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid()) {
			$bookmarksRepository = new BookmarksRepository($app['db']);
			$bookmarksRepository->save($form->getData());

			$app['session']->getFlashBag()->add(
				'messages',
				[
					'type' => 'success',
					'message' => 'message.element_successfully_added',
				]
			);

			return $app->redirect($app['url_generator']->generate('bookmarks_index'), 301);
		}

		return $app['twig']->render(
			'bookmarks/add.html.twig',
			[
				'bookmark' => $bookmark,
				'form' => $form->createView()
			]
		);
	}


}