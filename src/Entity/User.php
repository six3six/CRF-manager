<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = ["ROLE_USER"];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=520)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=510)
     */
    private $display_name;

    /**
     * @ORM\Column(type="date")
     */
    private $birthday;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Skill", inversedBy="users")
     */
    private $skills;

    /**
     * @ORM\OneToMany(targetEntity="PlanningEntry", mappedBy="user", orphanRemoval=true)
     */
    private $planning_entries;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cellphone = "";

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $phone = "";

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $address = "";

    public function __construct()
    {
        $this->skills = new ArrayCollection();
        $this->planning_entries = new ArrayCollection();
        $this->birthday = new DateTime();
        $d = new DateTime();
        $d->setTimestamp(time());
        $this->setBirthday($d);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

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

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array("ROLE_ADMIN", $this->roles);
    }

    public function isHyperAdmin(): bool
    {
        return in_array("ROLE_HYPER_ADMIN", $this->roles);
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->display_name;
    }

    public function setDisplayName(string $display_name): self
    {
        $this->display_name = $display_name;

        return $this;
    }


    public function getBirthday(): ?DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return Collection|Skill[]
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): self
    {
        if (!$this->skills->contains($skill)) {
            $this->skills[] = $skill;
        }

        return $this;
    }

    public function removeSkill(Skill $skill): self
    {
        if ($this->skills->contains($skill)) {
            $this->skills->removeElement($skill);
        }

        return $this;
    }

    /**
     * @return Collection|PlanningEntry[]
     */
    public function getPlanningEntries(): Collection
    {
        return $this->planning_entries;
    }

    public function addPlanningEntry(PlanningEntry $planning_entry): self
    {
        if (!$this->planning_entries->contains($planning_entry)) {
            $this->planning_entries[] = $planning_entry;
            $planning_entry->setUser($this);
        }

        return $this;
    }

    public function removePlanningEntry(PlanningEntry $planning_entry): self
    {
        if ($this->planning_entries->contains($planning_entry)) {
            $this->planning_entries->removeElement($planning_entry);
            // set the owning side to null (unless already changed)
            if ($planning_entry->getUser() === $this) {
                $planning_entry->setUser(null);
            }
        }

        return $this;
    }

    public function getCellphone(): ?string
    {
        return $this->cellphone;
    }

    public function setCellphone(string $cellphone): self
    {
        $this->cellphone = $cellphone;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }
}
