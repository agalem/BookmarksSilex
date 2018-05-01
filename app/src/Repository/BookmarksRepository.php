<?php
/**
 * Created by PhpStorm.
 * User: agalempaszek
 * Date: 29.04.2018
 * Time: 13:50
 */
namespace Repository;

use \Doctrine\DBAL\Connection;
use Utils\Paginator;

class BookmarksRepository {

	const NUM_ITEMS = 2;

	protected $db;

	public function __construct(Connection $db) {
		$this -> db = $db;
	}

	public function findAll() {
		$queryBuilder = $this->queryAll();
		return $queryBuilder->execute()->fetchAll();
	}

	public function findAllPaginated($page = 1){
		$countQueryBuilder = $this->queryAll()
			->select('COUNT(DISTINCT b.id) AS total_results')
			->setMaxResults(1);

		$paginator = new Paginator($this->queryAll(), $countQueryBuilder);
		$paginator->setCurrentPage($page);
		$paginator->setMaxPerPage(self::NUM_ITEMS);

		return $paginator->getCurrentPageResults();
	}

	public function findOneById($id) {
		$queryBuilder = $this->queryAll();
		$queryBuilder->where('b.id = :id')
		             ->setParameter(':id', $id, \PDO::PARAM_INT);
		$result = $queryBuilder->execute()->fetch();
		return !$result ? [] : $result;
	}

	protected function queryAll() {
		$queryBuilder = $this->db->createQueryBuilder();
		return $queryBuilder ->select('b.id', 'b.created_at', 'b.modified_at', 'b.title', 'b.url', 'b.is_public')
		                     ->orderBy('b.id', 'ASC')
		                     ->from('si_bookmarks', 'b');
	}


	public function save($bookmark)
	{
		$currentDateTime = new \DateTime();
		$bookmark['modified_at'] = $currentDateTime->format('Y-m-d H:i:s');
		if (isset($bookmark['id']) && ctype_digit((string) $bookmark['id'])) {
			// update record
			$id = $bookmark['id'];
			unset($bookmark['id']);

			return $this->db->update('si_bookmarks', $bookmark, ['id' => $id]);
		} else {
			// add new record
			$bookmark['created_at'] = $currentDateTime->format('Y-m-d H:i:s');

			return $this->db->insert('si_bookmarks', $bookmark);
		}
	}

	public function delete($bookmark) {

		return $this->db->delete('si_bookmarks', ['id' => $bookmark['id']]);

	}
}