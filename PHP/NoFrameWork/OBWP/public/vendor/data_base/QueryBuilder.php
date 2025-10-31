<?php

namespace vendor\data_base;

use PDO;
use PDOException;

class QueryBuilder
{
    private string $table = '';
    private string $type = 'select';
    private array $where = [];
    private array $select = ['*'];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $orderBy = [];
    private array $params = [];
    private array $data = [];
    private array $whereGroups = [];
    private array $groupBy = [];
    private array $join = [];

    public function from(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(array $columns): self
    {
        $this->select = $columns;
        return $this;
    }

    public function where(string $column, string $operator, $value): self
    {
        if (strtoupper($operator) === 'IN' && is_array($value)) {
            // Для оператора IN с массивом значений
            $placeholders = [];
            foreach ($value as $i => $val) {
                $paramName = 'param_' . count($this->params);
                $placeholders[] = ":$paramName";
                $this->params[$paramName] = $val;
            }
            $this->where[] = ["type" => "AND", "condition" => "$column IN (" . implode(', ', $placeholders) . ")"];
        } else {
            // Обычное условие
            $paramName = 'param_' . count($this->params);
            $this->where[] = ["type" => "AND", "condition" => "$column $operator :$paramName"];
            $this->params[$paramName] = $value;
        }
        return $this;
    }

    public function orWhere(string $column, string $operator, $value): self
    {
        if (strtoupper($operator) === 'IN' && is_array($value)) {
            // Для оператора IN с массивом значений
            $placeholders = [];
            foreach ($value as $i => $val) {
                $paramName = 'param_' . count($this->params);
                $placeholders[] = ":$paramName";
                $this->params[$paramName] = $val;
            }
            $this->where[] = ["type" => "OR", "condition" => "$column IN (" . implode(', ', $placeholders) . ")"];
        } else {
            // Обычное условие
            $paramName = 'param_' . count($this->params);
            $this->where[] = ["type" => "OR", "condition" => "$column $operator :$paramName"];
            $this->params[$paramName] = $value;
        }
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->join[] = "$type JOIN $table ON $first $operator $second";
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function set(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function get(): array
    {
        $this->type = 'select';
        $sql = $this->buildSelectQuery();
        $stmt = DB::getPdo()->prepare($sql);

        foreach ($this->params as $param => $value) {
            $stmt->bindValue(":$param", $value);
        }

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            die("<b>Fatal query error:</b> $e");
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function groupBy(array $columns): self
    {
        $this->groupBy = array_merge($this->groupBy, $columns);
        return $this;
    }

    public function insert(array $data = []): bool
    {
        $this->type = 'insert';
        if (!empty($data)) {
            $this->data = $data;
        }

        $sql = $this->buildInsertQuery();
        $stmt = DB::getPdo()->prepare($sql);

        foreach ($this->data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            die("<b>Fatal query error:</b> $e");
        }
    }

    public function update(array $data = []): int
    {
        $this->type = 'update';
        if (!empty($data)) {
            $this->data = $data;
        }

        $sql = $this->buildUpdateQuery();
        $stmt = DB::getPdo()->prepare($sql);

        // Bind set values
        foreach ($this->data as $key => $value) {
            $stmt->bindValue(":set_$key", $value);
        }

        // Bind where values
        foreach ($this->params as $param => $value) {
            $stmt->bindValue(":$param", $value);
        }

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            die("<b>Fatal query error:</b> $e");
        }

        return $stmt->rowCount();
    }

    public function delete(): int
    {
        $this->type = 'delete';
        $sql = $this->buildDeleteQuery();
        $stmt = DB::getPdo()->prepare($sql);

        foreach ($this->params as $param => $value) {
            $stmt->bindValue(":$param", $value);
        }

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            die("<b>Fatal query error:</b> $e");
        }

        return $stmt->rowCount();
    }

    public function sql(bool $withParams = true): string
    {
        switch ($this->type) {
            case 'select':
                return $this->buildSelectQuery($withParams);
            case 'insert':
                return $this->buildInsertQuery($withParams);
            case 'update':
                return $this->buildUpdateQuery($withParams);
            case 'delete':
                return $this->buildDeleteQuery($withParams);
            default:
                return $this->buildSelectQuery($withParams);
        }
    }

    private function buildSelectQuery(bool $withParams = false): string
    {
        $sql = "SELECT " . implode(', ', $this->select) . " FROM " . $this->table;

        if (!empty($this->join)) {
            $sql .= " " . implode(' ', $this->join);
        }

        if (!empty($this->where)) {
            $sql .= " WHERE " . $this->buildWhereClause($withParams);
        }

        // Добавление GROUP BY
        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET " . $this->offset;
        }

        return $sql;
    }

    private function buildInsertQuery(bool $withParams = false): string
    {
        $columns = implode(', ', array_keys($this->data));
        $placeholders = implode(', ', array_map(fn($k) => $withParams ? $this->quoteValue($this->data[$k]) : ":$k", array_keys($this->data)));

        return "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
    }

    private function buildUpdateQuery(bool $withParams = false): string
    {
        $setParts = [];
        foreach ($this->data as $column => $value) {
            $setValue = $withParams ? $this->quoteValue($value) : ":set_$column";
            $setParts[] = "$column = $setValue";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts);

        if (!empty($this->where)) {
            $sql .= " WHERE " . $this->buildWhereClause($withParams);
        }

        return $sql;
    }

    private function buildDeleteQuery(bool $withParams = false): string
    {
        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->where)) {
            $sql .= " WHERE " . $this->buildWhereClause($withParams);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }

        return $sql;
    }

    private function buildWhereClause(bool $withParams = false): string
    {
        $whereParts = [];
        $firstCondition = true;

        foreach ($this->where as $condition) {
            $clause = $condition['condition'];

            if ($withParams) {
                foreach ($this->params as $param => $value) {
                    $clause = str_replace(":$param", $this->quoteValue($value), $clause);
                }
            }

            if ($firstCondition) {
                $whereParts[] = $clause;
                $firstCondition = false;
            } else {
                $whereParts[] = $condition['type'] . ' ' . $clause;
            }
        }

        return implode(' ', $whereParts);
    }

    private function quoteValue($value): string
    {
        if (is_string($value)) {
            return "'" . str_replace("'", "''", $value) . "'";
        } elseif (is_bool($value)) {
            return $value ? '1' : '0';
        } elseif (is_null($value)) {
            return 'NULL';
        }
        return (string)$value;
    }
}