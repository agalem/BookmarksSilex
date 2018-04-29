<?php
/**
 * Created by PhpStorm.
 * User: agalempaszek
 * Date: 15.04.2018
 * Time: 20:39
 */
namespace Controller;

use Form\BookmarksType;
use Model\Bookmarks;
use Repository\BookmarksRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Form\TagType;

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


}