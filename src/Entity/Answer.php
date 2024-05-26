<?php

/**
 * Answer entity.
 */

namespace App\Entity;

use App\Repository\AnswerRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Answer.
 *
 * @psalm-suppress MissingConstructor
 */
#[ORM\Entity(repositoryClass: AnswerRepository::class)]
#[ORM\Table(name: 'answers')]
class Answer
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    /**
     * Comment.
     */
    #[ORM\Column(length: 5000)]
    private string $comment;

    /**
     * Image.
     */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $image = null;

    /**
     * Created at.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private DateTimeImmutable $createdAt;

    /**
     * Updated at.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    private DateTimeImmutable $updatedAt;

    /**
     * Question.
     */
    #[ORM\ManyToOne(targetEntity: Question::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Question $question;

    /**
     * Author.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[Assert\Type(User::class)]
    private ?User $author = null;

    /**
     * Best answer.
     */
    #[ORM\Column(type: 'boolean', options: ['default: 0'])]
    private bool $bestAnswer = false;

    /**
     * Getter for Id.
     *
     * @return int Id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Getter for comment.
     *
     * @return string Comment
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * Setter for comment.
     *
     * @param string $comment Comment
     *
     * @return $this Comment
     */
    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Getter for image.
     *
     * @return string|null Image
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Setter for image.
     *
     * @param string|null $image Image
     *
     * @return $this Image
     */
    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Getter for created at.
     *
     * @return DateTimeImmutable Created at
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for created at.
     *
     * @param DateTimeImmutable $createdAt Created at
     *
     * @return $this Created at
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Getter for updated at.
     *
     * @return DateTimeImmutable Updated at
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Setter for updated at.
     *
     * @param DateTimeImmutable $updatedAt Updated at
     *
     * @return $this Updated at
     */
    public function setUpdatedAt(DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Getter for question.
     *
     * @return Question Question
     */
    public function getQuestion(): Question
    {
        return $this->question;
    }

    /**
     * Setter for question.
     *
     * @param Question $question Question
     *
     * @return $this Question
     */
    public function setQuestion(Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Getter for author.
     *
     * @return User|null Author
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Setter for author.
     *
     * @param User|null $author Author
     *
     * @return $this Author
     */
    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Getter for best answer flag.
     *
     * @return bool|null Best answer
     */
    public function isBestAnswer(): ?bool
    {
        return $this->bestAnswer;
    }

    /**
     * Setter for best answer flag.
     *
     * @param bool $bestAnswer Best answer
     *
     * @return $this Best answer
     */
    public function setBestAnswer(bool $bestAnswer): self
    {
        $this->bestAnswer = $bestAnswer;

        return $this;
    }
}
