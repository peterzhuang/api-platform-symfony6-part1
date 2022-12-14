<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\CheeseListingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Carbon\Carbon;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\Link;
use App\Doctrine\CheeseListingSetOwnerListener;
use App\Validator\IsValidOwner;

#[ORM\Entity(repositoryClass: CheeseListingRepository::class)]
#[ORM\EntityListeners([CheeseListingSetOwnerListener::class])]
#[ApiFilter(BooleanFilter::class, properties: ['isPublished'])]
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial', 'description' => 'partial', 'owner' => 'exact', 'owner.username' => 'partial'])]
#[ApiFilter(RangeFilter::class, properties: ['price'])]
#[ApiFilter(PropertyFilter::class)]
#[ApiResource(shortName: 'cheese', operations: [
    new Get(normalizationContext: ['groups' => ['cheese:read', 'cheese:item:get']]), 
    new Post(security: "is_granted('ROLE_USER')"), 
    new GetCollection(),
    new Put(
        security: "is_granted('EDIT', previous_object)",
        securityMessage: "only the creator can edit a cheese listing",
        securityPostDenormalize: "is_granted('ROLE_USER') and object.getOwner() == user and previous_object.getOwner() == user",
        securityPostDenormalizeMessage: 'creator can not re-assign cheese listing to other owner'),
    new Patch(),
    new Delete(security: "is_granted('ROLE_ADMIN')"),
],
// normalizationContext: ['groups' => ['cheese:read'], 'swagger_definition_name' => 'Read'],
// denormalizationContext: ['groups' => ['cheese:write'], 'swagger_definition_name' => 'Write'],
paginationItemsPerPage: 10,
formats: ['json', 'html', 'jsonhal', 'jsonld', 'csv' => ['text/csv']]
)]
#[ApiResource(
    shortName: 'cheeses',
    uriTemplate: '/users/{id}/cheeses.{_format}',
    uriVariables: [
        'id' => new Link(
            fromClass: User::class, 
            fromProperty: 'cheeseListings'           
        )        
    ],
    operations: [new GetCollection()]
)]
class CheeseListing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['cheese:read', 'cheese:write', 'user:read', 'user:write'])]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max:50, maxMessage: "Describe your cheese in 50 chars or less")]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['cheese:read'])]
    #[Assert\NotBlank()]
    private ?string $description = null;

    /**
     * The price of this delicious cheese in cents
     */
    #[ORM\Column]
    #[Groups(['cheese:read', 'cheese:write', 'user:read', 'user:write'])]
    #[Assert\NotBlank()]
    private ?int $price = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?bool $isPublished = false;

    #[ORM\ManyToOne(inversedBy: 'cheeseListings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cheese:read', 'cheese:write', 'cheese:collection:post'])]
    #[IsValidOwner()]
    // #[Assert\NotBlank()]
    private ?User $owner = null;

    public function __construct(string $title)
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->title = $title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    // public function setTitle(string $title): self
    // {
    //     $this->title = $title;

    //     return $this;
    // }

    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    #[Groups(['cheese:read'])]
    public function getShortDescription(): ?string
    {
        if (strlen($this->description) < 40) {
            return $this->description;
        }

        return substr(strip_tags($this->description), 0, 40).'...';
    }
    // public function setDescription(string $description): self
    // {
    //     $this->description = $description;

    //     return $this;
    // }

    /**
     * The description of the cheese as as raw text.
     */    
    #[Groups(['cheese:write', 'user:write'])]
    #[SerializedName('description')]
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * How long ago in text that this cheese listing was added.
     */
    #[Groups(['cheese:read'])]
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
    }

    // public function setCreatedAt(\DateTimeImmutable $createdAt): self
    // {
    //     $this->createdAt = $createdAt;

    //     return $this;
    // }

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
