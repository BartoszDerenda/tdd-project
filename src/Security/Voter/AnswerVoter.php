<?php
/**
 * Answer voter.
 */

namespace App\Security\Voter;

use App\Entity\Answer;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

/**
 * Class AnswerVoter.
 */
class AnswerVoter extends Voter
{
    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'EDIT';

    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'VIEW';

    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'DELETE';

    /**
     * Award permission.
     *
     * @const string
     */
    public const AWARD = 'AWARD';

    /**
     * Security helper.
     */
    private Security $security;

    /**
     * OrderVoter constructor.
     *
     * @param Security $security Security helper
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE, self::AWARD])
            && $subject instanceof Answer;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::AWARD => $this->canAward($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can edit answer.
     *
     * @param Answer $answer Answer entity
     * @param User   $user   User
     *
     * @return bool Result
     */
    private function canEdit(Answer $answer, User $user): bool
    {
        return $answer->getAuthor() === $user;
    }

    /**
     * Checks if user can view answer.
     *
     * @param Answer $answer Answer entity
     * @param User   $user   User
     *
     * @return bool Result
     */
    private function canView(Answer $answer, User $user): bool
    {
        return $answer->getAuthor() === $user;
    }

    /**
     * Checks if user can delete answer.
     *
     * @param Answer $answer Answer entity
     * @param User   $user   User
     *
     * @return bool Result
     */
    private function canDelete(Answer $answer, User $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN') or $answer->getAuthor() === $user) {
            return true;
        }

        return false;
    }

    /**
     * Checks if user can award an answer.
     *
     * @param Answer $answer Answer entity
     * @param User   $user   User
     *
     * @return bool Result
     */
    private function canAward(Answer $answer, User $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN') or $answer->getQuestion()->getAuthor() === $user) {
            return true;
        }

        return false;
    }
}
