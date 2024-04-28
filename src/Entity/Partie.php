<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="partie")
 */
class Partie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    #[Groups(['party:read'])]
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    #[Groups(['party:read'])]
    private $user;

    /**
     * @ORM\Column(name="datePrevue", type="date")
     * @Assert\GreaterThan("today", message="The date must be greater than today")
     */
    #[Groups(['party:read'])]
    private $datePrevue;

    /**
     * @ORM\Column(name="creneauHoraire", type="string", length=255)
     */
    #[Groups(['party:read'])]
    private $creneauHoraire;

    /**
     * @ORM\Column(type="string", length=50)
     */
    #[Groups(['party:read'])]
    private $club;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['party:read'])]
    private $commentaire;

    /**
     * @ORM\Column(type="integer")
     */
    #[Groups(['party:read'])]
    private $etat;

    #[Groups(['party:read'])]
    private $reservéPar;

    /**
     * @return mixed
     */
    public function getReservéPar()
    {
        return $this->reservéPar;
    }

    /**
     * @param mixed $reservéPar
     */
    public function setReservéPar($reservéPar): void
    {
        $this->reservéPar = $reservéPar;
    }


    public function __construct()
    {
        $this->commentaire = '';
    }

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

    public function getDatePrevue(): ?\DateTimeInterface
    {
        return $this->datePrevue;
    }

    public function setDatePrevue(\DateTimeInterface $datePrevue): self
    {
        $this->datePrevue = $datePrevue;
        return $this;
    }

    public function getCreneauHoraire(): ?string
    {
        return $this->creneauHoraire;
    }

    public function setCreneauHoraire(string $creneauHoraire): self
    {
        $this->creneauHoraire = $creneauHoraire;
        return $this;
    }

    public function getClub(): ?string
    {
        return $this->club;
    }

    public function setClub(string $club): self
    {
        $this->club = $club;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getEtat(): ?int
    {
        return $this->etat;
    }

    public function setEtat(int $etat): self
    {
        $this->etat = $etat;
        return $this;
    }
}
