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
use App\Service\CategoryServiceInterface;
use App\Service\QuestionServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
     * Category service.
     */
    private CategoryServiceInterface $categoryService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param QuestionServiceInterface $questionService Question interface
     * @param AnswerServiceInterface   $answerService   Answer interface
     * @param CategoryServiceInterface $categoryService Category interface
     * @param TranslatorInterface      $translator      Translator interface
     */
    public function __construct(QuestionServiceInterface $questionService, AnswerServiceInterface $answerService, CategoryServiceInterface $categoryService, TranslatorInterface $translator)
    {
        $this->questionService = $questionService;
        $this->answerService = $answerService;
        $this->categoryService = $categoryService;
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
     * @param Request $request Request
     * @param int     $id      Id
     *
     * @return Response Response
     */
    #[Route(
        '/{id}',
        name: 'question_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function show(Request $request, int $id): Response
    {
        $question = $this->questionService->findOneById($id);

        $pagination = $this->answerService->getPaginatedList(
            $request->query->getInt('page', 1),
            $question
        );

        return $this->render('question/show.html.twig', ['question' => $question, 'pagination' => $pagination]);
    }

    /**
     * Show by category action.
     *
     * @param Request $request Request
     * @param int     $id      Id
     *
     * @return Response Response
     */
    #[Route(
        '/category/{id}',
        name: 'question_show_by_category',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function showByCategory(Request $request, int $id): Response
    {
        $category = $this->categoryService->findOneById($id);

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
     * @param Request $request HTTP request
     * @param int     $id      Id
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'question_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, int $id): Response
    {
        $question = $this->questionService->findOneById($id);

        if (!$this->isGranted('EDIT', $question)) {
            throw new AccessDeniedException('Access Denied.');
        }

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
     * @param Request $request HTTP request
     * @param int     $id      Id
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'question_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, int $id): Response
    {
        $question = $this->questionService->findOneById($id);

        if (!$this->isGranted('DELETE', $question)) {
            throw new AccessDeniedException('Access Denied.');
        }

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
