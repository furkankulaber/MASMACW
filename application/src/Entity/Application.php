<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApplicationRepository::class)
 * @ORM\Table(name="applications", uniqueConstraints={@ORM\UniqueConstraint(name="app_code_ux", columns={"app_code"}), @ORM\UniqueConstraint(name="title_appcode_ux", columns={"title","app_code"})})
 */
class Application
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $title;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private ?string $appCode;

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

    public function getAppCode(): ?string
    {
        return $this->appCode;
    }

    public function setAppCode(string $appCode): self
    {
        $this->appCode = $appCode;

        return $this;
    }
}
