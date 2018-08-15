<?php
/**
 * Bookmarks controller.
 *
 * @copyright (c) 2016 Tomasz Chojna
 *
 * @link      http://epi.chojna.info.pl
 */
namespace Controller;

use Model\Bookmarks;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Repository\BookmarksRepository;
use Repository\TagsRepository;
use Form\BookmarkType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class BookmarksController.
 */
class BookmarksController implements ControllerProviderInterface
{
    /**
     * Routing settings.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Silex\ControllerCollection Result
     */
//    public function connect(Application $app)
//    {
//        $controller = $app['controllers_factory'];
//        $controller->get('/', [$this, 'indexAction']);
//
//        return $controller;
//    }

    public function connect(Application $app)
    {
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
            ->bind('bookmarks_add');

        $controller->match('/{id}/edit', [$this, 'editAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('bookmarks_edit');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('bookmarks_delete');

        return $controller;
    }


    /**
     * Index action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return string Response
     */
    public function indexAction(Application $app, $page = 1)
    {
        $bookmarksRepository = new BookmarksRepository($app['db']);

        return $app['twig']->render(
            'bookmarks/index.html.twig',
            ['paginator' => $bookmarksRepository->findAllPaginated($page)]
        );
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
        $bookmarksRepository = new BookmarksRepository($app['db']);

        return $app['twig']->render(
            'bookmarks/view.html.twig',
            ['bookmarks' => $bookmarksRepository->findOneById($id)]
        );
    }


    /**
     * Add action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function addAction(Application $app, Request $request)
    {
        $bookmark = [];

        $form = $app['form.factory']->createBuilder(
            BookmarkType::class,
            $bookmark,
            ['tags_repository' => new TagsRepository($app['db'])]
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
                'form' => $form->createView(),
            ]
        );
    }
}
