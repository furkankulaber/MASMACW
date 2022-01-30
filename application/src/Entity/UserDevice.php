<?php

namespace App\Entity;

use App\Repository\UserDeviceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserDeviceRepository::class)
 * @ORM\Table(name="user_devices", indexes={
 *     @ORM\Index(name="user_xn", columns={"user_id"}),
 *     @ORM\Index(name="platform_xn", columns={"platform_id"}),
 *     @ORM\Index(name="device_xn", columns={"device"}),
 *     @ORM\Index(name="language_xn", columns={"language"}),
 *     },
 *     uniqueConstraints={
 *     @ORM\UniqueConstraint(name="user_device_platform_xu", columns={"user_id","device","platform_id"})
 * })
 */
class UserDevice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userDevices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Platform::class, inversedBy="userDevices")
     */
    private $platform;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $device;

    /**
     * @ORM\Column(type="json")
     */
    private $deviceInfo;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $language;

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

    public function getDevice(): ?string
    {
        return $this->device;
    }

    public function setDevice(string $device): self
    {
        $this->device = $device;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeviceInfo()
    {
        return $this->deviceInfo;
    }

    /**
     * @param mixed $deviceInfo
     * @return UserDevice
     */
    public function setDeviceInfo($deviceInfo)
    {
        $this->deviceInfo = $deviceInfo;
        return $this;
    }
}
