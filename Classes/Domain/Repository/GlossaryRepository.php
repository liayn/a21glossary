<?php

namespace SveWap\A21glossary\Domain\Repository;

use SveWap\A21glossary\Domain\Model\Glossary;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class GlossaryRepository extends Repository
{
    protected $defaultOrderings = [
        'short' => QueryInterface::ORDER_ASCENDING
    ];

    /**
     * @return string[]
     */
    public function findAllForIndex()
    {
        /** @var Query $query */
        $query = $this->createQuery();
        $tableName = $this->objectManager->get(DataMapFactory::class)->buildDataMap($this->objectType)->getTableName();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
        $queryBuilder->selectLiteral('substr(' . $queryBuilder->quoteIdentifier('short') . ', 1, 1) AS ' . $queryBuilder->quoteIdentifier('char'))->from($tableName)->groupBy('char');

        return $query->statement($queryBuilder)->execute(true);
    }

    /**
     * @param string $char
     *
     * @return Glossary[]|QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findAllWithChar(string $char)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->like('short', $char . '%')
        );

        return $query->execute();
    }

    /**
     * @param string $q
     *
     * @return Glossary[]|QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findAllWithQuery(string $q)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalOr(
                $query->like('short', '%' . $q . '%'),
                $query->like('shortcut', '%' . $q . '%'),
                $query->like('longversion', '%' . $q . '%'),
                $query->like('description', '%' . $q . '%')
            )
        );

        return $query->execute();
    }
}
