<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PurchaseRepository::class)
 */
class Purchase
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Platform::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $platform;

    /**
     * @ORM\ManyToOne(targetEntity=UserDevice::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $device;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $receipt;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expireAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }

    public function setPlatform(?Platform $platform): self
    {
        $this->platform = $platform;

        return $this;
    }

    public function getDevice(): ?UserDevice
    {
        return $this->device;
    }

    public function setDevice(?UserDevice $device): self
    {
        $this->device = $device;

        return $this;
    }

    public function getReceipt(): ?string
    {
        return $this->receipt;
    }

    public function setReceipt(string $receipt): self
    {
        $this->receipt = $receipt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getExpireAt(): ?\DateTimeInterface
    {
        return $this->expireAt;
    }

    public function setExpireAt(\DateTimeInterface $expireAt): self
    {
        $this->expireAt = $expireAt;

        return $this;
    }
}
