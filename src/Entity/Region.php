<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Region
 *
 * @ORM\Table(name="region", uniqueConstraints={@ORM\UniqueConstraint(name="nom", columns={"nom"}), @ORM\UniqueConstraint(name="id", columns={"id"})})
 * @ORM\Entity
 */
class Region
{
    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=30, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $nom;

    /**
     * @var int
      * @ORM\OneToMany(targetEntity="Produit" ,mappedBy="Region")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
    public function __toString()
    {
        return $this->nom;
    }

}
