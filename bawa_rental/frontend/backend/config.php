<?php
// backend/config.php

class Config {
    const DB_HOST = 'localhost';
    const DB_NAME = 'bawa_rental';
    const DB_USER = 'root';
    const DB_PASS = '';
    
    const UPLOAD_DIR = '../uploads/';
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    
    public static function getDBConfig() {
        return [
            'host' => self::DB_HOST,
            'dbname' => self::DB_NAME,
            'username' => self::DB_USER,
            'password' => self::DB_PASS
        ];
    }
}
?>