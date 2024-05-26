<?php
/**
 * Question controller.
 */

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Question;
use App\Entity\User;
use App\Form\Type\QuestionType;
use App\Service\AnswerServiceInterface;
use App\Service\QuestionServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class QuestionController.
 */
#[Route('/question')]
class QuestionController extends AbstractController
{
    /**
     * Question service.
     */
    private QuestionServiceInterface $questionService;

    /**
     * Answer service.
     */
    private AnswerServiceInterface $answerService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param QuestionServiceInterface $questionService Question interface
     * @param AnswerServiceInterface   $answerService   Answer interface
     * @param TranslatorInterface      $translator      Translator interface
     */
    public function __construct(QuestionServiceInterface $questionService, AnswerServiceInterface $answerService, TranslatorInterface $translator)
    {
        $this->questionService = $questionService;
        $this->answerService = $answerService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param Request $request Request
     *
     * @return Response Response
     */
    #[Route(name: 'question_index', methods: 'GET')]
    public function index(Request $request): Response
    {
        $pagination = $this->questionService->getPaginatedList(
            $request->query->getInt('page', 1)
        );

        return $this->render('question/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Question $question Question entity
     * @param Request  $request  Request
     *
     * @return Response Response
     */
    #[Route(
        '/{id}',
        name: 'question_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function show(Question $question, Request $request): Response
    {
        $pagination = $this->answerService->getPaginatedList(
            $request->query->getInt('page', 1),
            $question
        );

        return $this->render('question/show.html.twig', ['question' => $question, 'pagination' => $pagination]);
    }

    /**
     * Show by category action.
     *
     * @param Category $category Category entity
     * @param Request  $request  Request
     *
     * @return Response Response
     */
    #[Route(
        '/category/{id}',
        name: 'question_show_by_category',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function showByCategory(Category $category, Request $request): Response
    {
        $pagination = $this->questionService->queryByCategory(
            $request->query->getInt('page', 1),
            $category
        );

        return $this->render('question/category.html.twig', ['category' => $category, 'pagination' => $pagination]);
    }

    /**
     * Create action.
     *
     * @param Request $request Request
     *
     * @return Response Response
     */
    #[Route('/create', name: 'question_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $question = new Question();
        $question->setAuthor($user);
        $form = $this->createForm(
            QuestionType::class,
            $question,
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $request->files->get('question')['image'];
            if ($file) {
                $filename = md5(uniqid()).'.'.$file->guessClientExtension();

                $file->move(
                    $this->getParameter('uploads_dir'),
                    $filename
                );
                $question->setImage($filename);
            }
            $this->questionService->save($question);

            $this->addFlash(
                'success',
                $this->translator->trans('message.success')
            );

            return $this->redirectToRoute('question_index');
        }

        return $this->render(
            'question/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request  $request  HTTP request
     * @param Question $question Question entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'question_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    #[IsGranted('EDIT', subject: 'question')]
    public function edit(Request $request, Question $question): Response
    {
        $form = $this->createForm(
            QuestionType::class,
            $question,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('question_edit', ['id' => $question->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $request->files->get('question')['image'];
            if ($file) {
                $filename = md5(uniqid()).'.'.$file->guessClientExtension();

                $file->move(
                    $this->getParameter('uploads_dir'),
                    $filename
                );
                $question->setImage($filename);
            }
            $this->questionService->save($question);

            $this->addFlash(
                'success',
                $this->translator->trans('message.success')
            );

            return $this->redirectToRoute('question_show', ['id' => $question->getId()]);
        }

        return $this->render(
            'question/edit.html.twig',
            [
                'form' => $form->createView(),
                'question' => $question,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request  $request  HTTP request
     * @param Question $question Question entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'question_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    #[IsGranted('DELETE', subject: 'question')]
    public function delete(Request $request, Question $question): Response
    {
        $form = $this->createForm(
            FormType::class,
            $question,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('question_delete', ['id' => $question->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->questionService->delete($question);

            $this->addFlash(
                'success',
                $this->translator->trans('message.success')
            );

            return $this->redirectToRoute('question_index');
        }

        return $this->render(
            'question/delete.html.twig',
            [
                'form' => $form->createView(),
                'question' => $question,
            ]
        );
    }
}
