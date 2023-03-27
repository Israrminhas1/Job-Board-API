<?php

namespace App\Entity;

use App\Repository\ApplicantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ApplicantRepository::class)]
class Applicant
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 255)]
    #[ORM\Column(length: 255)]
    private ?string $contact = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $job_preferences = null;

    #[ORM\ManyToMany(targetEntity: Job::class, mappedBy: 'applicants')]
    private Collection $jobsApplied;

    public function __construct()
    {
        $this->jobsApplied = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
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

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getJobPreferences(): ?string
    {
        return $this->job_preferences;
    }

    public function setJobPreferences(string $job_preferences): self
    {
        $this->job_preferences = $job_preferences;

        return $this;
    }

    /**
     * @return Collection<int, Job>
     */
    public function getJobsApplied(): Collection
    {
        return $this->jobsApplied;
    }

    public function addJobsApplied(Job $jobsApplied): self
    {
        if (!$this->jobsApplied->contains($jobsApplied)) {
            $this->jobsApplied->add($jobsApplied);
            $jobsApplied->addApplicant($this);
        }

        return $this;
    }

    public function removeJobsApplied(Job $jobsApplied): self
    {
        if ($this->jobsApplied->removeElement($jobsApplied)) {
            $jobsApplied->removeApplicant($this);
        }

        return $this;
    }
}
