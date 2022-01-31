<?php

namespace App\Repository;

use App\Entity\Purchase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Purchase|null find($id, $lockMode = null, $lockVersion = null)
 * @method Purchase|null findOneBy(array $criteria, array $orderBy = null)
 * @method Purchase[]    findAll()
 * @method Purchase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }

    public function getWaitingOrExp()
    {
        try {
            $rsm = new ResultSetMappingBuilder($this->_em);
            $rsm->addRootEntityFromClassMetadata(Purchase::class, 'w');
            $date = new \DateTime('now', new \DateTimeZone('Europe/Istanbul'));
            $dateTwo = clone $date;
            $dateTwo->sub(new \DateInterval('PT10M'));
            $sql = 'select * from purchase where status = ? OR (expire_at < ? AND status = ? ) OR (update_at < ? AND status = ?)';
            $query = $this->_em->createNativeQuery($sql, $rsm);
            $query->setParameter(1,'w')
                ->setParameter(2, $date)
                ->setParameter(3, 'a')
                ->setParameter(4, $dateTwo)
                ->setParameter(5, 'd');
            $entity = $query->getResult();
        } catch (\Exception $e) {
            return new RepositoryResponse(null, false, $e->getMessage(), $e);
        }
        return new RepositoryResponse($entity);
    }

}
