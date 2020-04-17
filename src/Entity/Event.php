<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime")
     */
    private $stop;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="created_event")
     */
    private $created_by;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="events")
     */
    private $registered_users;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    public function __construct()
    {
        $this->registered_users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getStop(): ?DateTimeInterface
    {
        return $this->stop;
    }

    public function setStop(DateTimeInterface $stop): self
    {
        $this->stop = $stop;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->created_by;
    }

    public function setCreatedBy(?User $created_by): self
    {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getRegisteredUsers(): Collection
    {
        return $this->registered_users;
    }

    public function addRegisteredUser(User $registeredUser): self
    {
        if (!$this->registered_users->contains($registeredUser)) {
            $this->registered_users[] = $registeredUser;
        }

        return $this;
    }

    public function removeRegisteredUser(User $registeredUser): self
    {
        if ($this->registered_users->contains($registeredUser)) {
            $this->registered_users->removeElement($registeredUser);
        }

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
}
