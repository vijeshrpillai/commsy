<?php
namespace App\Repository;

use App\Entity\SavedSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SavedSearchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavedSearch::class);
    }

    /**
     * Returns a list of saved searches belonging to the given account ID.
     * @param int $accountId The ID of the user account whose saved searches shall be returned
     * @return SavedSearch[] The list of saved searches belonging to the given account ID
     */
    public function getSavedSearchesByAccountId(int $accountId): array
    {
        // NOTE: instead of this method, one could also use the autogenerated `findByAccountId()` method:
        // $savedSearches = $repository->findByAccountId($accountId);

        $query = $this->createQueryBuilder('savedsearches')
            ->select()
            ->where('savedsearches.accountId = :accountId')
            ->setParameter('accountId', $accountId)
            ->getQuery();
        $savedSearches = $query->getResult();

        return $savedSearches;
    }

    /**
     * Deletes any saved searches belonging to the given account ID.
     * @param int $accountId The ID of the user account whose saved searches shall be deleted
     */
    public function removeSavedSearchesByAccountId(int $accountId)
    {
        $savedSearches = $this->getSavedSearchesByAccountId($accountId);
        if (empty($savedSearches)) {
            return;
        }

        $entityManager = $this->getEntityManager();

        foreach ($savedSearches as $savedSearch) {
            $entityManager->remove($savedSearch);
        }

        $entityManager->flush();
    }

    /**
     * Deletes the given saved search.
     * @param SavedSearch $savedSearch
     */
    public function removeSavedSearch(SavedSearch $savedSearch)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($savedSearch);

        $entityManager->flush();
    }
}
