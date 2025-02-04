<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AdviceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdviceRepository::class)]
class Advice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAdvicesOfTheMonth", "getMonthsOfAdvice"])]
    private ?int $id = null;

    /**
     * @var Collection<int, Month>
     */
    #[ORM\ManyToMany(targetEntity: Month::class, inversedBy: 'advice')]
    #[Groups(["getMonthsOfAdvice"])]
    #[Assert\NotBlank(message: "Le champs month est obligatoire.")]
    private Collection $month;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getAdvicesOfTheMonth", "getMonthsOfAdvice"])]
    #[Assert\NotBlank(message: "Le champs content est obligatoire.")]
    private ?string $content = null;

    public function __construct()
    {
        $this->month = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Month>
     */
    public function getMonth(): Collection
    {
        return $this->month;
    }

    public function addMonth(Month $month): static
    {
        if (!$this->month->contains($month)) {
            $this->month->add($month);
        }

        return $this;
    }

    public function removeMonth(Month $month): static
    {
        $this->month->removeElement($month);

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }
}
