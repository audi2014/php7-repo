<?php
/**
 * Created by PhpStorm.
 * User: andriyprosekov
 * Date: 27/07/2018
 * Time: 15:13
 */


namespace Audi2014\Repo;

abstract class AbstractRepo implements RepoInterface {
    protected $pdo;
    protected $logger;
    protected $log_events = ['execute'];

    public function __construct(
        \PDO $pdo
    ) {
        $this->pdo = $pdo;
    }

    public function setLogger($logger) {
        $this->logger = $logger;
    }

    private function log($str) {
        if ($this->logger) {
            ($this->logger)($str);
        } else {
            \error_log($str);
        }
    }

    /**
     * @param $sql
     * @return \PDOStatement
     */
    private function prepare($sql): \PDOStatement {
        if (in_array('prepare', $this->log_events)) {
            $this->log('prepare: ' . $sql);
        }
        return $this->getPdo()->prepare($sql);
    }


    private function execute(\PDOStatement $stm, $args) {
        if (in_array('execute', $this->log_events)) {
            $this->log('execute: ' . $stm->queryString);
            $this->log('execute: ' . json_encode($args));
        }
        return $stm->execute($args);
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO {
        return $this->pdo;
    }


    abstract protected function getEntityClass(): string;

    abstract protected function getTable(): string;

    abstract protected function getFields(): array;

    protected function getFieldsForCount(): array {
        return [];
    }

    abstract protected function getGroupBy(): ?string;

    protected function getGroupByForCount(): ?string {
        return null;
    }

    protected function getJoins(): array {
        return [];
    }

    protected function getOnDuplicateKeySql(): string {
        return '';
    }

    protected function willInsertData(array $data): array {
        return $data;
    }

    protected function willUpdateData(array $data): array {
        return $data;
    }

    protected function getFieldsSql(): string {
        return implode(', ', $this->getFields());
    }

    protected function getJoinsSql(): string {
        return implode("\n", $this->getJoins());
    }

    protected function getGroupBySql(): string {
        $v = $this->getGroupBy();
        return $v ? "GROUP BY $v" : "";
    }

    protected function getGroupByForCountSql(): string {
        $v = $this->getGroupByForCount();
        return $v ? "GROUP BY $v" : "";
    }

    protected function didFetchRow($data) {
        return $data;
    }

    protected function mapKeySelector(string $whereKey): string {
        if ($whereKey === 'id') return "{$this->getTable()}.$whereKey";
        return $whereKey;
    }

    //========================================

//todo: use repo-query
//    public function fetchQueryPageItems(RequestQueryPageInterface $query): array {
//        $sql = <<<MySQL
//SELECT {$this->getFieldsSql()}
//FROM {$this->getTable()}
//{$this->getJoinsSql()}
//{$query->getWhereSql()}
//{$this->getGroupBySql()}
//{$query->getHavingSql()}
//{$query->getOrderBySql()}
//limit {$query->getOffset()}, {$query->getCount()}
//MySQL;
//        $data = $this->fetchAllBySqlAndArgs(
//            $sql,
//            $query->getExecuteValues()
//        );
//        return $data;
//
//    }

//    public function deleteQueryItems(RequestQueryInterface $query, ?int $count = 0): int {
//        if ($count) $count = "LIMIT $count";
//        else $count = "";
//
//        $sql = <<<MySQL
//DELETE FROM {$this->getTable()}
//{$query->getWhereSql()}
//{$query->getHavingSql()}
//$count
//MySQL;
//        return $this->deleteRowsBySql(
//            $sql,
//            $query->getExecuteValues()
//        );
//
//    }
//
//    public function fetchQueryItems(RequestQueryInterface $query, ?int $count = 0): array {
//        if ($count) $count = "LIMIT $count";
//        else $count = "";
//
//        $sql = <<<MySQL
//SELECT {$this->getFieldsSql()}
//FROM {$this->getTable()}
//{$this->getJoinsSql()}
//{$query->getWhereSql()}
//{$this->getGroupBySql()}
//{$query->getHavingSql()}
//{$query->getOrderBySql()}
//$count
//MySQL;
//        $data = $this->fetchAllBySqlAndArgs(
//            $sql,
//            $query->getExecuteValues()
//        );
//        return $data;
//
//    }
//
//    public function fetchQueryItem(RequestQueryInterface $query) {
//        $sql = <<<MySQL
//SELECT {$this->getFieldsSql()}
//FROM {$this->getTable()}
//{$this->getJoinsSql()}
//{$query->getWhereSql()}
//{$this->getGroupBySql()}
//{$query->getHavingSql()}
//{$query->getOrderBySql()}
//LIMIT 0,1
//MySQL;
//        $data = $this->fetchFirstBySqlAndArgs(
//            $sql,
//            $query->getExecuteValues()
//        );
//        return $data;
//    }
//
//    /**
//     * @param RequestQueryInterface $query
//     * @return int
//     * @throws \Exception
//     */
//    public function fetchQueryCount(RequestQueryInterface $query): int {
//
//        if (!empty($this->getFieldsForCount())) {
//            $fieldsForCountSql = "{$this->getGroupBy()} as count_id, " . implode(', ', $this->getFieldsForCount());
//            $sql = /** @lang MySQL */
//                <<<SQL
//SELECT count(DISTINCT selection.count_id) as `count` FROM (
//    SELECT $fieldsForCountSql
//    FROM {$this->getTable()}
//    {$this->getJoinsSql()}
//    {$query->getWhereSql()}
//    {$this->getGroupByForCountSql()}
//    {$query->getHavingSql()}
//) as selection
//SQL;
//        } else {
//            $sql = /** @lang MySQL */
//                <<<SQL
//SELECT count(DISTINCT {$this->getGroupBy()}) as `count`
//FROM {$this->getTable()}
//{$this->getJoinsSql()}
//{$query->getWhereSql()}
//{$this->getGroupByForCountSql()}
//SQL;
//        }
//
//
//        $stmt = $this->prepare($sql);
//        $this->execute($stmt, $query->getExecuteValues());
//        $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
//        $r_count = count($rows);
//        if ($r_count !== 1) {
//            throw new \Exception("bad fetchQueryCount sql: count of returned counters !== 1. returned: ($r_count)");
//        }
//        return reset($rows);
//    }
//    //========================================
//

    /**
     * @param $data
     * @return int
     * @throws \Exception
     */
    public function insertRow($data): int {
        $data = $this->willInsertData((array)$data);
        return $this->insertRowByTableAndSql(
            $data,
            $this->getTable(),
            $this->getOnDuplicateKeySql()
        );


    }

    /**
     * @param array $rows_of_arrays
     * @return array
     * @throws \Exception
     */
    public function insertRows(array $rows_of_arrays): array {
        foreach ($rows_of_arrays as $key => $data) {
            $rows_of_arrays[$key] = $this->willInsertData((array)$data);
        }
        return $this->insertRowsByTableAndSql(
            $rows_of_arrays,
            $this->getTable(),
            $this->getOnDuplicateKeySql()
        );
    }


    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function fetchFirstByKeyValue(string $key, $value) {
        $key = $this->mapKeySelector($key);

        $sql = /** @lang MySQL */
            "SELECT {$this->getFieldsSql()} FROM {$this->getTable()} {$this->getJoinsSql()} WHERE {$key} = ? {$this->getGroupBySql()} limit 0,1";
        $data = $this->fetchFirstBySqlAndArgs(
            $sql,
            [
                $value
            ]
        );
        if (!$data) return null;
        $data = $this->didFetchRow($data);
        return $data;
    }


    public function fetchAllByKeyValue(string $key, $value): array {
        $key = $this->mapKeySelector($key);
        $sql = "SELECT {$this->getFieldsSql()} FROM {$this->getTable()} {$this->getJoinsSql()} WHERE {$key} = ? {$this->getGroupBySql()}";
        $rows = $this->fetchAllBySqlAndArgs(
            $sql,
            [
                $value
            ]
        );
        foreach ($rows as $key => $data) {
            $rows[$key] = $this->didFetchRow($data);
        }
        return $rows;
    }

    /**
     * @param int|null $count
     * @return array
     */
    public function fetchAll(?int $count = null): array {
        if ($count) $end = "LIMIT $count";
        else $end = '';
        $rows = $this->fetchAllBySqlAndArgs(
            "SELECT {$this->getFieldsSql()} FROM {$this->getTable()} {$this->getJoinsSql()} {$this->getGroupBySql()} $end",
            []
        );
        foreach ($rows as $key => $data) {
            $rows[$key] = $this->didFetchRow($data);
        }
        return $rows;
    }

    /**
     * @param string $key
     * @param string $value
     * @param $data
     * @param int|null $count
     * @return int
     */
    public function updateRowsByKeyValue(string $key, $value, $data, ?int $count = 1): int {
        $key = $this->mapKeySelector($key);
        $data = $this->willUpdateData((array)$data);
        if ($count) $end = "LIMIT $count";
        else $end = '';
        return $this->updateRowsByData(
            $data,
            $this->getTable(),
            [
                $key => $value,
            ],
            $end
        );
    }

    /**
     * @param string $key
     * @param string $value
     * @param int|null $count
     * @return int
     */
    public function deleteRowsByKeyValue(string $key, $value, ?int $count = null): int {
        $key = $this->mapKeySelector($key);
        if ($count) $count = "LIMIT $count";
        else $count = "";
        return $this->deleteRowsBySql(
        /** @lang MySQL */
            "DELETE FROM {$this->getTable()} WHERE $key = ? $count",
            [$value]
        );
    }


    /**
     * @return int
     */
    public function deleteAllRows(): int {
        return $this->deleteRowsBySql(
        /** @lang MySQL */
            "DELETE FROM {$this->getTable()}",
            []
        );
    }

    //========================================


    /**
     * @param string $sql
     * @param array $args
     * @return mixed|null
     */
    protected function fetchFirstBySqlAndArgs(
        string $sql,
        array $args
    ) {
        $query = $this->prepare($sql);
        $this->execute($query, $args);

        $result = $this->_fetch($query);

        if (empty($result)) {
            return null;
        } else {
            return $result;
        }
    }

    /**
     * @param string $sql
     * @param array $args
     * @return array
     */
    protected function fetchAllBySqlAndArgs(
        string $sql,
        array $args
    ): array {
        $query = $this->prepare($sql);
        $this->execute($query, $args);
        return $this->_fetchAll($query);
    }

    /**
     * @param array $keys
     * @param string $table
     * @param string $end
     * @return \PDOStatement
     * @throws \Exception
     */
    protected function prepareInsertRow(
        array $keys,
        string $table,
        string $end = ''
    ): \PDOStatement {
        $columnNames = implode(",", $keys);
        $placeHolders = str_pad("", (count($keys) * 2 - 1), "?,");
        $sql = /** @lang MySQL */
            "INSERT INTO $table ($columnNames) VALUES ($placeHolders) $end";
        return $this->prepare($sql);
    }


    /**
     * @param array $rows_data
     * @param string $table
     * @param string $end
     * @return array of ids
     * @throws \Exception
     */
    protected function insertRowsByTableAndSql(
        array $rows_data,
        string $table,
        string $end = ''
    ): array {
        $ids = [];
        if (empty($rows_data)) return $ids;

        $query = $this->prepareInsertRow(array_keys(reset($rows_data)), $table, $end);
        foreach ($rows_data as $key => $data) {
            $values = array_values($data);
            $this->execute($query, $values);
            $ids[] = $this->getPdo()->lastInsertId();
        }
        return $ids;
    }

    /**
     * @param array $data
     * @param string $table
     * @param string $end
     * @return int
     * @throws \Exception
     */
    protected function insertRowByTableAndSql(
        array $data,
        string $table,
        string $end = ''
    ): int {
        $values = array_values($data);
        $query = $this->prepareInsertRow(array_keys($data), $table, $end);
        $this->execute($query, $values);
        return $this->getPdo()->lastInsertId();
    }

    /**
     * @param array $data
     * @param string $table
     * @param array $whereKv
     * @param string $end
     * @return int rowCount
     */
    protected function updateRowsByData(
        array $data,
        string $table,
        array $whereKv,
        string $end = ''
    ): int {

        $updateSet = $whereSet = $values = [];
        if (empty($data)) {
            return 0;
        }
        foreach ($data as $key => $value) {
            $updateSet[] = "$key=?";
            $values[] = $value;
        }
        foreach ($whereKv as $key => $value) {
            $whereSet[] = "$key=?";
            $values[] = $value;
        }
        $columnNames = implode(",", $updateSet);
        $where = implode(" AND", $whereSet);
        $sql = /** @lang SQL */
            "UPDATE $table SET  $columnNames WHERE $where $end";
        $query = $this->prepare($sql);
        $this->execute($query, $values);
        return $query->rowCount();
    }

    /**
     * @param string $sql
     * @param array $args
     * @return int rowCount
     */
    protected function deleteRowsBySql(
        string $sql,
        array $args
    ): int {
        $query = $this->prepare($sql);
        $this->execute($query, $args);
        return $query->rowCount();
    }

    //========================================

    private function _fetch(\PDOStatement $stmt) {
        $stmt->setFetchMode(\PDO::FETCH_CLASS, $this->getEntityClass());
        return $stmt->fetch();

    }

    private function _fetchAll(\PDOStatement $stmt) {
        $stmt->setFetchMode(\PDO::FETCH_CLASS, $this->getEntityClass());
        return $stmt->fetchAll();
    }

}