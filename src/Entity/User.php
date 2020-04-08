<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
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
    private $roles = [];

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
    private $name;

    /**
     * @ORM\Column(type="string", length=510)
     */
    private $surname;

    /**
     * @ORM\Column(type="date")
     */
    private $birthday;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Skill", inversedBy="users")
     */
    private $skils;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="created_by")
     */
    private $created_event;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Event", mappedBy="registered_users")
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Availability", mappedBy="user", orphanRemoval=true)
     */
    private $availabilities;

    public function __construct()
    {
        $this->skils = new ArrayCollection();
        $this->created_event = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->availabilities = new ArrayCollection();
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

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return Collection|Skill[]
     */
    public function getSkils(): Collection
    {
        return $this->skils;
    }

    public function addSkil(Skill $skil): self
    {
        if (!$this->skils->contains($skil)) {
            $this->skils[] = $skil;
        }

        return $this;
    }

    public function removeSkil(Skill $skil): self
    {
        if ($this->skils->contains($skil)) {
            $this->skils->removeElement($skil);
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getCreatedEvent(): Collection
    {
        return $this->created_event;
    }

    public function addCreatedEvent(Event $createdEvent): self
    {
        if (!$this->created_event->contains($createdEvent)) {
            $this->created_event[] = $createdEvent;
            $createdEvent->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedEvent(Event $createdEvent): self
    {
        if ($this->created_event->contains($createdEvent)) {
            $this->created_event->removeElement($createdEvent);
            // set the owning side to null (unless already changed)
            if ($createdEvent->getCreatedBy() === $this) {
                $createdEvent->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->addRegisteredUser($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            $event->removeRegisteredUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|Availability[]
     */
    public function getAvailabilities(): Collection
    {
        return $this->availabilities;
    }

    public function addAvailability(Availability $availability): self
    {
        if (!$this->availabilities->contains($availability)) {
            $this->availabilities[] = $availability;
            $availability->setUser($this);
        }

        return $this;
    }

    public function removeAvailability(Availability $availability): self
    {
        if ($this->availabilities->contains($availability)) {
            $this->availabilities->removeElement($availability);
            // set the owning side to null (unless already changed)
            if ($availability->getUser() === $this) {
                $availability->setUser(null);
            }
        }

        return $this;
    }
}
