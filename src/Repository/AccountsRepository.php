<?php


namespace App\Repository;


use App\Entity\Account;
use App\Entity\AuthSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AccountsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * @param string $username
     * @param int $context
     * @param int $authSourceId
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findOneByCredentials(string $username, int $context, AuthSource $authSource)
    {
        return $this->createQueryBuilder('a')
            ->where('a.username = :username')
            ->andWhere('a.authSource = :authSource')
            ->andWhere('a.contextId = :contextId')
            ->setParameters([
                'username' => $username,
                'contextId' => $context,
                'authSource' => $authSource,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $usernameOrEmail
     * @param int $context
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findOneByCredentialsShort(string $usernameOrEmail, int $context)
    {
        return $this->createQueryBuilder('a')
            ->where('a.username = :query OR a.email = :query')
            ->andWhere('a.contextId = :contextId')
            ->setParameters([
                'query' => $usernameOrEmail,
                'contextId' => $context,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

}