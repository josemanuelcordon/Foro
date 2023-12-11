<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Entity\Thread;
use App\Form\ThreadType;
use App\Repository\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/thread')]
class ThreadController extends AbstractController
{
    #[Route('/', name: 'app_thread_index', methods: ['GET'])]
    public function index(ThreadRepository $threadRepository): Response
    {
        return $this->render('thread/index.html.twig', [
            'threads' => $threadRepository->findAll(),
        ]);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route('/new/{id}', name: 'app_thread_new', methods: ['GET', 'POST'])]
    public function new(Forum $forum, Request $request, EntityManagerInterface $entityManager): Response
    {
        $thread = new Thread();
        $form = $this->createForm(ThreadType::class, $thread);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = new Comment();

            $thread->setAuthor($this->getUser());
            $thread->setStatus(1);
            $thread->setCreatedDate(new \DateTime());
            $thread->setForum($forum);

            $comment->setAuthor($this->getUser());
            $comment->setStatus(1);
            $comment->setCreatedDate(new \DateTime());
            $comment->setThread($thread);
            $comment->setText($form->get('text')->getData());

            $entityManager->persist($thread);
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_thread_show', ['id' => $thread->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('thread/new.html.twig', [
            'thread' => $thread,
            'form' => $form,
            'forum' => $forum,
        ]);
    }

    #[Route('/{id}', name: 'app_thread_show', methods: ['GET'])]
    public function show(Thread $thread): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment, [
            'action' =>
            $this->generateUrl('app_comment_new', ['id' => $thread->getId()]),
            'method' => 'GET,POST'
        ]);
        return $this->render('thread/show.html.twig', [
            'thread' => $thread,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_thread_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Thread $thread, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ThreadType::class, $thread);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_thread_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('thread/edit.html.twig', [
            'thread' => $thread,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_thread_delete', methods: ['POST'])]
    public function delete(Request $request, Thread $thread, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $thread->getId(), $request->request->get('_token'))) {
            $entityManager->remove($thread);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_thread_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/hide/{id}', name: 'app_thread_hide', methods: ['GET'])]
    public function hide(Request $request, Thread $thread, EntityManagerInterface $entityManager): Response
    {
        $thread->setStatus(0);
        $entityManager->persist($thread);
        $entityManager->flush();

        return $this->redirectToRoute('app_forum_show', ['id' => $thread->getForum()->getId()], Response::HTTP_SEE_OTHER);
    }
}
