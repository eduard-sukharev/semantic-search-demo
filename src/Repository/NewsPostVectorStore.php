<?php

namespace App\Repository;

use App\Entity\ModelEnum;
use App\Entity\NewsPost;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Exception;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\VectorStores\Doctrine\DoctrineEmbeddingEntityBase;
use LLPhant\Embeddings\VectorStores\Doctrine\VectorUtils;
use Pgvector\Doctrine\CosineDistance;
use Pgvector\Doctrine\L2Distance;
use Pgvector\Doctrine\VectorType;

final class NewsPostVectorStore
{
    /**
     * @throws Exception
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $conn = $entityManager->getConnection();
        $registeredTypes = Type::getTypesMap();
        if (! array_key_exists('vector', $registeredTypes)) {
            Type::addType('vector', VectorType::class);
            $conn->getDatabasePlatform()?->registerDoctrineTypeMapping('vector', 'vector');
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function addDocument(NewsPost $document): void
    {
        $this->entityManager->persist($document);
        $this->entityManager->flush();
    }

    /**
     * @param  NewsPost[]  $documents
     *
     * @throws \Exception
     */
    public function addDocuments(array $documents): void
    {
        if ($documents === []) {
            return;
        }
        foreach ($documents as $document) {
            $this->entityManager->persist($document);
        }

        $this->entityManager->flush();
    }

    public function getBasicStats(): array
    {
        $qb = $this->entityManager->getConnection()->executeQuery('
        SELECT 
            count(*) AS total_count,
            SUM(CASE WHEN embedding_enbeddrus IS NOT NULL THEN 1 ELSE 0 END) AS embedding_enbeddrus_count,
            SUM(CASE WHEN embedding_arctic_2 IS NOT NULL THEN 1 ELSE 0 END) AS embedding_embeddingarctic2_count,
            SUM(CASE WHEN embedding_bge_m3 IS NOT NULL THEN 1 ELSE 0 END) AS embedding_embeddingbgem2_count
        FROM 
            news_post;
        ');

        return $qb->fetchAssociative();
    }

    /**
     * @return iterable<NewsPost>
     */
    public function similaritySearch(ModelEnum $embeddingField, array $embedding, int $k = 2, array $additionalArguments = []): array
    {
        $this->entityManager->getConfiguration()->addCustomNumericFunction('cosine_distance', CosineDistance::class);
        $this->entityManager->getConfiguration()->addCustomNumericFunction('l2_distance', L2Distance::class);

        $repository = $this->entityManager->getRepository(NewsPost::class);
        $qb = $repository
            ->createQueryBuilder('e')
//             ->orderBy('l2_distance(e.'. $embeddingField->fieldName() .', :embeddingString)', 'ASC')
            ->orderBy('cosine_distance(e.'. $embeddingField->fieldName() .', :embeddingString)', 'ASC')
            ->setParameter('embeddingString', VectorUtils::getVectorAsString($embedding))
            ->setMaxResults($k);

        foreach ($additionalArguments as $key => $value) {
            $paramName = 'where_'.$key;
            $qb
                ->andWhere(sprintf('e.%s = :%s', $key, $paramName))
                ->setParameter($paramName, $value);
        }
        $query = $qb->getQuery();

        /** @var DoctrineEmbeddingEntityBase[] */
        return $query->getResult();
    }

    /**
     * @return iterable<NewsPost>
     */
    public function fetchDocumentsWithoutEmbedding(ModelEnum $embeddingField, int $limit = 1): iterable
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata(NewsPost::class, 'p');
        $query = $this->entityManager->createNativeQuery('
            SELECT p.*
            FROM news_post p
            WHERE p.' . $embeddingField->columnName() . ' IS NULL
            ORDER BY p.id 
            LIMIT :limit 
            FOR UPDATE SKIP LOCKED
            ',
            $rsm
        )->setParameter('limit', $limit);

        return $query->toIterable();
    }

    public function save(): void
    {
        $this->entityManager->flush();
    }
}
