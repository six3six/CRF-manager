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
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Availability", mappedBy="attached_to")
     */
    private $attached_availabilities;

    public function __construct()
    {
        $this->registered_users = new ArrayCollection();
        $this->attached_availabilities = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Availability[]
     */
    public function getAttachedAvailabilities(): Collection
    {
        return $this->attached_availabilities;
    }

    public function addAttachedAvailability(Availability $attachedAvailability): self
    {
        if (!$this->attached_availabilities->contains($attachedAvailability)) {
            $this->attached_availabilities[] = $attachedAvailability;
            $attachedAvailability->setAttachedTo($this);
        }

        return $this;
    }

    public function removeAttachedAvailability(Availability $attachedAvailability): self
    {
        if ($this->attached_availabilities->contains($attachedAvailability)) {
            $this->attached_availabilities->removeElement($attachedAvailability);
            // set the owning side to null (unless already changed)
            if ($attachedAvailability->getAttachedTo() === $this) {
                $attachedAvailability->setAttachedTo(null);
            }
        }

        return $this;
    }
}
