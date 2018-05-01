<?php
/**
 * Tag repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Utils\Paginator;

/**
 * Class TagRepository.
 */
class TagRepository
{
	const NUM_ITEMS = 10;
	/**
	 * Doctrine DBAL connection.
	 *
	 * @var \Doctrine\DBAL\Connection $db
	 */
	protected $db;

	/**
	 * TagRepository constructor.
	 *
	 * @param \Doctrine\DBAL\Connection $db
	 */
	public function __construct(Connection $db)
	{
		$this->db = $db;
	}

	/**
	 * Fetch all records.
	 *
	 * @return array Result
	 */
	public function findAll()
	{
		$queryBuilder = $this->queryAll();
		return $queryBuilder->execute()->fetchAll();
	}


	public function findAllPaginated($page = 1) {
		$countQueryBuilder = $this->queryAll()
			->select('COUNT(DISTINCT t.id) AS total_results')
			->setMaxResults(1);

		$paginator = new Paginator($this->queryAll(), $countQueryBuilder);
		$paginator->setCurrentPage($page);
		$paginator->setMaxPerPage(self::NUM_ITEMS);

		return $paginator->getCurrentPageResults();
	}

	/**
	 * Find one record.
	 *
	 * @param string $id Element id
	 *
	 * @return array|mixed Result
	 */
	public function findOneById($id)
	{
		$queryBuilder = $this->queryAll();
		$queryBuilder->where('id = :id')
		             ->setParameter(':id', $id, \PDO::PARAM_INT);
		$result = $queryBuilder->execute()->fetch();

		return !$result ? [] : $result;
	}

	protected function queryAll() {
		$queryBuilder = $this->db->createQueryBuilder();

		return $queryBuilder->select('t.id', 't.name')
							->orderBy('t.id', 'ASC')
							->from('si_tags', 't');
	}


	public function save($tag) {

		if (isset($tag['id']) && ctype_digit((string) $tag['id'])) {
			// update record
			$id = $tag['id'];
			unset($tag['id']);

			return $this->db->update('si_tags', $tag, ['id' => $id]);
		} else {
			// add new record
			$this->db->insert('si_tags', $tag);
			$tag['id'] = $this->db->lastInsertId();

			return $tag;
		}

	}

	public function delete($tag) {

		return $this->db->delete('si_tags', ['id' => $tag['id']]);

	}

	public function findForUniqueness($name, $id = null)
	{
		$queryBuilder = $this->queryAll();
		$queryBuilder->where('t.name = :name')
		             ->setParameter(':name', $name, \PDO::PARAM_STR);
		if ($id) {
			$queryBuilder->andWhere('t.id <> :id')
			             ->setParameter(':id', $id, \PDO::PARAM_INT);
		}

		return $queryBuilder->execute()->fetchAll();
	}

	public function findOneByName($name) {
		$queryBuilder = $this->queryAll();
		$queryBuilder->where('t.name = :name')
			->setParameter(':name', $name, \PDO::PARAM_STR);

		$result = $queryBuilder->execute()->fetch();

		return !$result ? [] : $result;
	}

	public function findById($ids) {

		$queryBuilder = $this->queryAll();
		$queryBuilder->where('t.id IN (:ids)')
			->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

		return $queryBuilder->execute()->fetchAll();

	}

}