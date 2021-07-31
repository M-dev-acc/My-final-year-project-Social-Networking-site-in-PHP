<?php
class Database
{
    private $pdo;
    protected function connect()
    {
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=db_instagram;charset=utf8', 'root', '');

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $th) {
            echo 'Connection error:' . $th->getMessage();
        }

        return $pdo;
    }
    public static function runQuery($query, $params = array())
    {
        $statement = self::connect()->prepare($query);
        $statement->execute($params);

        if (explode(' ', $query)[0] == 'SELECT') {
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);

            return $data;
        }
    }
}
