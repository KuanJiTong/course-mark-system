<?php
    function camelToSnake(array $data): array {
        $result = [];
        foreach ($data as $key => $value) {
            $snakeKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
            $result[$snakeKey] = $value;
        }
        return $result;
    }