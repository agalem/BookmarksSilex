<?php
/**
 * Tag repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
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

		if(isset($tag['id']) && ctype_digit((string) $tag['id'])) {
			$id = $tag['id'];
			unset($tag['id']);

			return $this->db->update('si_tags', $tag, ['id' => $id]);
		} else {
			return $this->db->insert('si_tags', $tag);
		}

	}

	public function delete($tag) {

		return $this->db->delete('si_tags', ['id' => $tag['id']]);

	}
}