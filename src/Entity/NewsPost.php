<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LLPhant\Embeddings\VectorStores\Doctrine\VectorType;

#[ORM\Entity]
#[ORM\Table(name: 'news_post')]
class NewsPost
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    public int $id;

    #[ORM\Column(name: 'embedding_enbeddrus', type: VectorType::VECTOR, length: 768)]
    public ?array $embeddingEnbeddrus;

    #[ORM\Column(name: 'embedding_arctic_2', type: VectorType::VECTOR, length: 1024)]
    public ?array $embeddingArctic2;

    #[ORM\Column(name: 'embedding_bge_m3', type: VectorType::VECTOR, length: 1024)]
    public ?array $embeddingBgeM3;

    #[ORM\Column(type: Types::TEXT)]
    public ?string $title;

    #[ORM\Column(type: Types::TEXT)]
    public string $content;

    public function getId(): int
    {
        return $this->id;
    }
}
