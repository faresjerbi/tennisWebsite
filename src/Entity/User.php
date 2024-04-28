<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface

{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"user:read"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(length=10)
     */
    private ?string $nom = null;

    /**
     * @ORM\Column(length=10)
     */
    private ?string $prenom = null;

    /**
     * @ORM\Column(name="mail", length= 50)
     */
    private ?string $email = null;

    /**
     * @ORM\Column(length= 255)
     */
    private ?string $password = null;

    /**
     * @ORM\Column(length= 20)
     */
    private ?string $genre = null;

    /**
     * @ORM\Column(type=Types::DATE_MUTABLE)
     */
    public ?\DateTimeInterface $date_de_naissance = null;

    /**
     * @ORM\Column(name="role")
     * @Groups({"user:read"})
     */
    private array $roles = [];

    /**
     * @ORM\Column(length= 50, nullable= true)
     */
    private ?string $niveau = null;

    /**
     * @ORM\Column(length= 50, nullable= true)
     */
    private ?string $disponibilite = null;

    /**
     * @ORM\Column(length= 255, nullable=true)
     */
    private ?string $img = null;

    /**
     * @ORM\Column(type="string", name="reset_code", length=255, nullable=true)
     */
    private ?string $resetCode = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $bloque = false;

    /**
     * @return bool
     */
    public function isBloque(): bool
    {
        return $this->bloque;
    }

    /**
     * @param bool $bloque
     */
    public function setBloque(bool $bloque): void
    {
        $this->bloque = $bloque;
    }


    private ?string $confirmPassword = null;

    public function getResetCode(): ?string
    {
        return $this->resetCode;
    }

    public function setResetCode(?string $resetCode): void
    {
        $this->resetCode = $resetCode;
    }

    /**
     * @return string|null
     */
    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    /**
     * @param string|null $confirmPassword
     */
    public function setConfirmPassword(?string $confirmPassword): void
    {
        $this->confirmPassword = $confirmPassword;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string|null $nom
     */
    public function setNom(?string $nom): void
    {
        $this->nom = $nom;
    }

    /**
     * @return string|null
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * @param string|null $prenom
     */
    public function setPrenom(?string $prenom): void
    {
        $this->prenom = $prenom;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getGenre(): ?string
    {
        return $this->genre;
    }

    /**
     * @param string|null $genre
     */
    public function setGenre(?string $genre): void
    {
        $this->genre = $genre;
    }

    public function getDateDeNaissance(): ?\DateTimeInterface
    {
        return $this->date_de_naissance;
    }

    public function setDateDeNaissance(?\DateTimeInterface $date_de_naissance): self
    {
        $this->date_de_naissance = $date_de_naissance;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    /**
     * @param string|null $niveau
     */
    public function setNiveau(?string $niveau): void
    {
        $this->niveau = $niveau;
    }

    /**
     * @return string|null
     */
    public function getDisponibilite(): ?string
    {
        return $this->disponibilite;
    }

    /**
     * @param string|null $disponibilite
     */
    public function setDisponibilite(?string $disponibilite): void
    {
        $this->disponibilite = $disponibilite;
    }

    /**
     * @return string|null
     */
    public function getImg(): ?string
    {
        return $this->img;
    }

    /**
     * @param string|null $img
     */
    public function setImg(?string $img): void
    {
        $this->img = $img;
    }


    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

}
