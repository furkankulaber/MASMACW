<?php


namespace App\Entity;

use App\Repository\UserSessionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserSession
 * @package App\Entity
 *
 * @ORM\Table(name="`user_sessions`", indexes={
 *     @ORM\Index(columns={"token"}),
 *     @ORM\Index(columns={"user"}),
 *     @ORM\Index(columns={"device"}),
 *     @ORM\Index(columns={"expire_at"})
 *     },
 *     uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"user","token","device"})
 * })
 * @ORM\Entity(repositoryClass=UserSessionRepository::class)
 */
class UserSession
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, unique=true, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="user", referencedColumnName="id")
     * })
     */
    private User $user;

    /**
     * @var UserDevice
     *
     * @ORM\ManyToOne(targetEntity="UserDevice")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="device", referencedColumnName="id")
     * })
     */
    private $device;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", nullable=false)
     */
    private string $token;

    /**
     * @var \DateTime
     * @ORM\Column(name="expire_at", type="datetime", nullable=false)
     */
    private $expireAt;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return UserSession
     */
    public function setId(int $id): UserSession
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return UserDevice
     */
    public function getDevice(): UserDevice
    {
        return $this->device;
    }

    /**
     * @param UserDevice|null $device
     * @return UserSession
     */
    public function setDevice(?UserDevice $device): UserSession
    {
        $this->device = $device;
        return $this;
    }


    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return UserSession
     */
    public function setToken(string $token): UserSession
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpireAt(): ?\DateTime
    {
        return $this->expireAt;
    }

    /**
     * @param \DateTime|null $expireAt
     * @return UserSession
     */
    public function setExpireAt(?\DateTime $expireAt): UserSession
    {
        $this->expireAt = $expireAt;
        return $this;
    }


}
