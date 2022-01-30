<?php

namespace App\Repository;

use App\Entity\UserSession;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSession[]    findAll()
 * @method UserSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSessionRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSession::class);
    }


    /**
     * @param $userId
     * @param $deviceId
     * @return RepositoryResponse
     */
    public function getAvailableSessionViaUserId($userId, $deviceId): RepositoryResponse
    {
        try {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('s')->from(UserSession::class, 's');

            $qb->where($qb->expr()->eq('s.user', ':user'))->setParameter('user', $userId);
            $qb->andWhere($qb->expr()->gt('s.expireAt', ':expireAt'))->setParameter('expireAt', new \DateTime('now', new \DateTimeZone('-6')));
            $qb->andWhere($qb->expr()->eq('s.device', ':device'))->setParameter('device',$deviceId);

            $qb->orderBy('s.expireAt', 'DESC');
            $qb->setMaxResults(1);

            $return = $qb->getQuery()->getOneOrNullResult();
        }catch (\Exception $exception)
        {
            return new RepositoryResponse($exception);
        }

        return new RepositoryResponse($return);

    }

}
