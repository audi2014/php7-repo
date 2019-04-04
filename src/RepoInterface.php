<?php
/**
 * Created by PhpStorm.
 * User: arturmich
 * Date: 2/27/19
 * Time: 11:24 AM
 */

namespace Audi2014\Repo;

interface RepoInterface {
    /**
     * @return \PDO
     */
    public function getPdo(): \PDO;
//todo: use repo-query
//    public function fetchQueryPageItems(RequestQueryPageInterface $query): array;
//
//    public function fetchQueryItems(RequestQueryInterface $query): array;
//
//    public function deleteQueryItems(RequestQueryInterface $query): int;
//
//    public function fetchQueryItem(RequestQueryInterface $query);
//
//    /**
//     * @param RequestQueryInterface $query
//     * @return int
//     * @throws \Exception
//     */
//    public function fetchQueryCount(RequestQueryInterface $query): int;

    /**
     * @param $data
     * @return int
     * @throws \Exception
     */
    public function insertRow($data): int;

    /**
     * @param array $rows_of_arrays
     * @return array
     * @throws \Exception
     */
    public function insertRows(array $rows_of_arrays): array;

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function fetchFirstByKeyValue(string $key, $value);

    public function fetchAllByKeyValue(string $key, $value): array;

    /**
     * @param int|null $count
     * @return array
     */
    public function fetchAll(?int $count = null): array;

    /**
     * @param string $key
     * @param string $value
     * @param array $data
     * @param int|null $count
     * @return int
     */
    public function updateRowsByKeyValue(string $key, $value, $data, ?int $count = 1): int;

    /**
     * @param string $key
     * @param string $value
     * @param int|null $count
     * @return int
     */
    public function deleteRowsByKeyValue(string $key, $value, ?int $count = 1): int;

    /**
     * @return int
     */
    public function deleteAllRows(): int;
}