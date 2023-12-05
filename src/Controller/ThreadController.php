<?php

namespace App\Controller;

use App\Entity\Thread;
use App\Form\ThreadType;
use App\Repository\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route('/new', name: 'app_thread_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $thread = new Thread();
        $form = $this->createForm(ThreadType::class, $thread);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($thread);
            $entityManager->flush();

            return $this->redirectToRoute('app_thread_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('thread/new.html.twig', [
            'thread' => $thread,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_thread_show', methods: ['GET'])]
    public function show(Thread $thread): Response
    {
        return $this->render('thread/show.html.twig', [
            'thread' => $thread,
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
        if ($this->isCsrfTokenValid('delete'.$thread->getId(), $request->request->get('_token'))) {
            $entityManager->remove($thread);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_thread_index', [], Response::HTTP_SEE_OTHER);
    }
}
