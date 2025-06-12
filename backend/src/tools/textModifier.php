<?php
    function camelToSnake(array $data): array {
        $result = [];
        foreach ($data as $key => $value) {
            $snakeKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
            $result[$snakeKey] = $value;
        }
        return $result;
    }

    function camelToSnakeRecursive(array $input): array {
    $output = [];
    foreach ($input as $key => $value) {
        $snakeKey = strtolower(preg_replace('/[A-Z]/', '_$0', $key));
        if (is_array($value) && array_keys($value) !== range(0, count($value) - 1)) {
            // associative array
            $output[$snakeKey] = camelToSnakeRecursive($value);
        } else {
            $output[$snakeKey] = $value;
        }
    }
    return $output;
}
