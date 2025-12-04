<?php

class Database
{
    private $pdo;
    private $config;

    /**
     * Конструктор - подключение к базе данных
     * 
     * @param array|null $config Конфигурация подключения (если null, загружается из config/database.php)
     * @throws PDOException При ошибке подключения
     */
    public function __construct(?array $config = null)
    {
        try {
            if ($config === null) {
                $this->config = require __DIR__ . '/../config/database.php';
                $connection = $this->config['connections'][$this->config['default']];
            } else {
                $connection = $config;
            }

            $dsn = sprintf(
                "pgsql:host=%s;port=%s;dbname=%s;options='--client_encoding=UTF8'",
                $connection['host'],
                $connection['port'],
                $connection['database']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO(
                $dsn,
                $connection['username'],
                $connection['password'],
                $options
            );
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Не удалось подключиться к базе данных: " . $e->getMessage());
        }
    }

    /**
     * Выполнение SQL запроса с подготовленными выражениями
     * 
     * @param string $sql SQL запрос
     * @param array $params Параметры для подготовленного выражения
     * @return PDOStatement Объект результата запроса
     * @throws Exception При ошибке выполнения запроса
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            // Преобразуем boolean значения в строки для PostgreSQL
            $processedParams = [];
            foreach ($params as $key => $value) {
                if (is_bool($value)) {
                    // PostgreSQL требует 'true'/'false' как строки
                    $processedParams[$key] = $value ? 'true' : 'false';
                } else {
                    $processedParams[$key] = $value;
                }
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($processedParams);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Ошибка выполнения запроса: " . $e->getMessage());
        }
    }

    /**
     * Получение всех строк результата запроса
     * 
     * @param string $sql SQL запрос
     * @param array $params Параметры для подготовленного выражения
     * @return array Массив всех строк
     * @throws Exception При ошибке выполнения запроса
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("FetchAll error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Получение одной строки результата запроса
     * 
     * @param string $sql SQL запрос
     * @param array $params Параметры для подготовленного выражения
     * @return array|null Массив одной строки или null, если строк нет
     * @throws Exception При ошибке выполнения запроса
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->query($sql, $params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (Exception $e) {
            error_log("FetchOne error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Получение ID последней вставленной записи
     * 
     * @param string|null $name Имя последовательности (для PostgreSQL)
     * @return string ID последней вставленной записи
     */
    public function lastInsertId(?string $name = null): string
    {
        try {
            return $this->pdo->lastInsertId($name);
        } catch (PDOException $e) {
            error_log("LastInsertId error: " . $e->getMessage());
            throw new Exception("Ошибка получения ID последней записи: " . $e->getMessage());
        }
    }

    /**
     * Начало транзакции
     * 
     * @return bool true при успехе
     * @throws Exception При ошибке начала транзакции
     */
    public function beginTransaction(): bool
    {
        try {
            return $this->pdo->beginTransaction();
        } catch (PDOException $e) {
            error_log("BeginTransaction error: " . $e->getMessage());
            throw new Exception("Ошибка начала транзакции: " . $e->getMessage());
        }
    }

    /**
     * Подтверждение транзакции
     * 
     * @return bool true при успехе
     * @throws Exception При ошибке подтверждения транзакции
     */
    public function commit(): bool
    {
        try {
            return $this->pdo->commit();
        } catch (PDOException $e) {
            error_log("Commit error: " . $e->getMessage());
            throw new Exception("Ошибка подтверждения транзакции: " . $e->getMessage());
        }
    }

    /**
     * Откат транзакции
     * 
     * @return bool true при успехе
     * @throws Exception При ошибке отката транзакции
     */
    public function rollback(): bool
    {
        try {
            return $this->pdo->rollBack();
        } catch (PDOException $e) {
            error_log("Rollback error: " . $e->getMessage());
            throw new Exception("Ошибка отката транзакции: " . $e->getMessage());
        }
    }

    /**
     * Получение объекта PDO (для расширенного использования)
     * 
     * @return PDO Объект PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}

