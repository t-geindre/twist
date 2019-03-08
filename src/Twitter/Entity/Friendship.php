<?php

namespace Twist\Twitter\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="Twist\Twitter\Repository\FriendshipRepository")
 * @ORM\Table()
 */
class Friendship
{
    use TimestampableEntity;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="string")
     */
    private $id;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expirationDate;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $userObject;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getExpirationDate(): ?\DateTime
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTime $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public function getUserObject(): array
    {
        return json_decode($this->userObject, true);
    }

    public function setUserObject(array $userObject): void
    {
        $userObject = json_encode($userObject);

        if (false === $userObject) {
            throw new \InvalidArgumentException('Malformed user object');
        }

        $this->userObject = $userObject;
    }
}
