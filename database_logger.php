<?php
// database_logger.php

class DatabaseLogger {
    private static $queries = [];
    private static $max_queries = 50; // Increased to store more queries

    public static function log_query($query, $database = 'default') {
        self::$queries[] = [
            'query' => $query,
            'database' => $database,
            'time' => microtime(true)
        ];
        if (count(self::$queries) > self::$max_queries) {
            array_shift(self::$queries);
        }
    }

    public static function get_recent_queries($limit = 10) {
        $queries = array_slice(self::$queries, -$limit);
        $formatted_queries = [];
        foreach ($queries as $query_data) {
            $formatted_queries[] = sprintf(
                "[%s] DB: %s - %s",
                date('Y-m-d H:i:s', $query_data['time']),
                $query_data['database'],
                $query_data['query']
            );
        }
        return $formatted_queries;
    }
}

function get_recent_queries($limit = 10) {
    return DatabaseLogger::get_recent_queries($limit);
}
?>