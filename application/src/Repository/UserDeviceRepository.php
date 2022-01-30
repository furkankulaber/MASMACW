<?php

namespace App\Repository;

use App\Entity\PlayerDevice;
use App\Entity\UserDevice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Base;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserDevice|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserDevice|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserDevice[]    findAll()
 * @method UserDevice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserDeviceRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDevice::class);
    }

    /**
     * @param $platform
     * @param $userId
     * @return RepositoryResponse
     */
    public function userOfDevice($platform, $device): RepositoryResponse
    {
        try {
            $qb = $this->createQueryBuilder('ud');
            $qb->select('ud');
            $qb->andWhere($qb->expr()->eq('ud.platform',':platform'))->setParameter(':platform',$platform);
            $qb->andWhere($qb->expr()->eq('ud.device',':device'))->setParameter(':device',$device);
            $qb->orderBy('ud.id','DESC');
            $qb->setMaxResults(1);
            $response = $qb->getQuery()->useQueryCache(false)->getOneOrNullResult();
        }catch (\Exception $exception)
        {
            return new RepositoryResponse($exception);
        }

        return new RepositoryResponse($response);
    }
}
