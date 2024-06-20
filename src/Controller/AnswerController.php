<?php

/**
 * Answer controller.
 */

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Form\Type\AnswerType;
use App\Service\AnswerServiceInterface;
use App\Service\QuestionServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AnswerController.
 */
#[Route('/answer')]
class AnswerController extends AbstractController
{
    /**
     * Answer service.
     */
    private AnswerServiceInterface $answerService;

    /**
     * Question service.
     */
    private QuestionServiceInterface $questionService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param AnswerServiceInterface   $answerService   Answer interface
     * @param QuestionServiceInterface $questionService Question interface
     * @param TranslatorInterface      $translator      Translator interface
     */
    public function __construct(AnswerServiceInterface $answerService, QuestionServiceInterface $questionService, TranslatorInterface $translator)
    {
        $this->answerService = $answerService;
        $this->questionService = $questionService;
        $this->translator = $translator;
    }

    /**
     * Create action.
     *
     * @param Request $request Request
     * @param int     $id      Id
     *
     * @return Response Response
     */
    #[Route(
        '/{id}',
        name: 'answer_create',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|POST',
    )]
    public function create(Request $request, int $id): Response
    {
        /** @var User $author */
        $author = $this->getUser();

        $question = $this->questionService->findOneById($id);

        $answer = new Answer();
        $answer->setAuthor($author);
        $answer->setQuestion($question);
        $form = $this->createForm(
            AnswerType::class,
            $answer,
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->answerService->save($answer);

            $this->addFlash(
                'success',
                $this->translator->trans('message.success')
            );

            return $this->redirectToRoute('question_show', ['id' => $answer->getQuestion()->getId()]);
        }

        return $this->render(
            'answer/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param int     $id      Id
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'answer_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, int $id): Response
    {
        $answer = $this->answerService->findOneById($id);

        if (!$this->isGranted('VIEW', $answer)) {
            throw new AccessDeniedException('Access Denied.');
        }

        $form = $this->createForm(
            AnswerType::class,
            $answer,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('answer_edit', ['id' => $answer->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->answerService->save($answer);

            $this->addFlash(
                'success',
                $this->translator->trans('message.success')
            );

            return $this->redirectToRoute('question_show', ['id' => $answer->getQuestion()->getId()]);
        }

        return $this->render(
            'answer/edit.html.twig',
            [
                'form' => $form->createView(),
                'answer' => $answer,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param int     $id      Id
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'answer_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, int $id): Response
    {
        $answer = $this->answerService->findOneById($id);

        if (!$this->isGranted('DELETE', $answer)) {
            throw new AccessDeniedException('Access Denied.');
        }

        $form = $this->createForm(
            FormType::class,
            $answer,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('answer_delete', ['id' => $answer->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->answerService->delete($answer);

            $this->addFlash(
                'success',
                $this->translator->trans('message.success')
            );

            return $this->redirectToRoute('question_show', ['id' => $answer->getQuestion()->getId()]);
        }

        return $this->render(
            'answer/delete.html.twig',
            [
                'form' => $form->createView(),
                'answer' => $answer,
            ]
        );
    }

    /**
     * Mark action.
     *
     * @param Request $request HTTP request
     * @param int     $id      Id
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/mark', name: 'answer_mark', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function mark(Request $request, int $id): Response
    {
        $answer = $this->answerService->findOneById($id);

        if (!$this->isGranted('AWARD', $answer)) {
            throw new AccessDeniedException('Access Denied.');
        }

        $form = $this->createForm(
            FormType::class,
            $answer,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('answer_mark', ['id' => $answer->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->answerService->award($answer);

            $this->addFlash(
                'success',
                $this->translator->trans('message.success')
            );

            return $this->redirectToRoute('question_show', ['id' => $answer->getQuestion()->getId()]);
        }

        return $this->render(
            'answer/mark.html.twig',
            [
                'form' => $form->createView(),
                'answer' => $answer,
            ]
        );
    }

    /**
     * Unmark action.
     *
     * @param Request $request HTTP request
     * @param int     $id      Id
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/unmark', name: 'answer_unmark', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function unmark(Request $request, int $id): Response
    {
        $answer = $this->answerService->findOneById($id);

        if (!$this->isGranted('AWARD', $answer)) {
            throw new AccessDeniedException('Access Denied.');
        }

        $form = $this->createForm(
            FormType::class,
            $answer,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('answer_unmark', ['id' => $answer->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->answerService->deaward($answer);

            $this->addFlash(
                'success',
                $this->translator->trans('message.success')
            );

            return $this->redirectToRoute('question_show', ['id' => $answer->getQuestion()->getId()]);
        }

        return $this->render(
            'answer/unmark.html.twig',
            [
                'form' => $form->createView(),
                'answer' => $answer,
            ]
        );
    }
}
