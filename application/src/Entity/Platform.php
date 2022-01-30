<?php

namespace App\Entity;

use App\Repository\PlatformRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlatformRepository::class)
 * @ORM\Table(name="platforms", indexes={
 *     @ORM\Index(name="app_id_ux", columns={"app_id"}),
 *     },
 *     uniqueConstraints={
 *     @ORM\UniqueConstraint(name="api_key_ux", columns={"api_key"}),
 *     @ORM\UniqueConstraint(name="code_ux", columns={"code"}),
 *     @ORM\UniqueConstraint(name="api_code_ux", columns={"code","api_key"}),
 *     @ORM\UniqueConstraint(name="code_ux", columns={"code","api_key","app_id"})
 * })
 */
class Platform
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $apiKey;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private ?string $code;

    /**
     * @ORM\Column(type="json")
     */
    private mixed $settings;

    /**
     * @ORM\ManyToOne(targetEntity="Application")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="app_id", referencedColumnName="id")
     * })
     */
    private ?Application $app;

    public function __construct()
    {
        $this->userDevices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param mixed $settings
     */
    public function setSettings($settings): void
    {
        $this->settings = $settings;
    }

    public function getApp(): ?Application
    {
        return $this->app;
    }

    public function setApp(?Application $app): self
    {
        $this->app = $app;

        return $this;
    }
}
