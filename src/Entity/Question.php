<?php
/**
 * Question entity.
 */

namespace App\Entity;

use App\Repository\QuestionRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Question.
 *
 * @psalm-suppress MissingConstructor
 */
#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\Table(name: 'questions')]
class Question
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

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
     * Title.
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    /**
     * Comment.
     */
    #[ORM\Column(length: 5000)]
    private string $comment;

    /**
     * Category.
     */
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    /**
     * Tags.
     */
    #[Assert\Valid]
    #[ORM\ManyToMany(targetEntity: Tags::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'questions_tags')]
    private $tags;

    /**
     * Image.
     */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $image = null;

    /**
     * Author.
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[Assert\Type(User::class)]
    private ?User $author;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

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
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
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
     */
    public function setUpdatedAt(DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Getter for title.
     *
     * @return string Title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Setter for title.
     *
     * @param string $title Title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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
     * Getter for category.
     *
     * @return Category Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * Setter for category.
     *
     * @param Category $category Category
     *
     * @return $this Category
     */
    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Getter for tags.
     *
     * @return Collection Tags
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Add tag.
     *
     * @param Tags $tag Tags
     *
     * @return void Void
     */
    public function addTag(Tags $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }
    }

    /**
     * Remove tag.
     *
     * @param Tags $tag Tags
     *
     * @return void Void
     */
    public function removeTag(Tags $tag): void
    {
        $this->tags->removeElement($tag);
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
     * Getter for answer.
     *
     * @param Answer $answer Answer
     *
     * @return Collection Answer
     */
    public function getAnswer(Answer $answer): Collection
    {
        return $this->$answer;
    }

    /**
     * Add answer.
     *
     * @param Answer $answer Answer
     *
     * @return $this Answer
     */
    public function addAnswer(Answer $answer): self
    {
        if (!$this->$answer->contains($answer)) {
            $this->$answer->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    /**
     * Remove answer.
     *
     * @param Answer $answer Answer
     *
     * @return $this Answer
     */
    public function removeAnswer(Answer $answer): self
    {
        if ($this->$answer->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

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
}
