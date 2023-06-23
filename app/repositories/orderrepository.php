<?php

namespace Repositories;

use Models\Movie;
use Models\Serie;
use Models\Order;
use Models\User;
use Models\Library;
use PDO;
use PDOException;
use Repositories\Repository;

class OrderRepository extends Repository
{
    function getAll($offset = NULL, $limit = NULL)
    {
        try {
            $query = "
            SELECT id, user_id, movie_id, serie_id, price FROM `library`
            ";
            if (isset($limit) && isset($offset)) {
                $query .= " LIMIT :limit OFFSET :offset ";
            }
            $stmt = $this->connection->prepare($query);
            if (isset($limit) && isset($offset)) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();

            $orders = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $orders[] = $this->getAllRow($row);
            }

            return $orders;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getAllRow($row)
    {
        $order = new Order;

        $order->id = $row['id'];
        $order->user_id = $row['user_id'];
        $order->movie_id = $row['movie_id'];
        $order->serie_id = $row['serie_id'];
        $order->price = $row['price'];

        return $order;
    }

    public function getAllByUserId($userId)
    {
        try {
            // Assuming you have a DB connection called $this->db and a table called orders
            $stmt = $this->connection->prepare("SELECT id, user_id, movie_id, serie_id, price FROM `library` WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $orders = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $orders[] = $this->getAllRow($row);
            }
            return $orders;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    //--------------------------------------------------------------------------------------

    function getAllLibraryRow($rows)
    {
        try{
            $libraries = [];
            foreach ($rows as $row) {
                $library = new Library();
                $library->id = $row['library_id'];
                $library->price = $row['price'];
    
                // Movie
                if (isset($row['movie_id'])) {
                    $movie = new Movie;
                    $movie->id = $row['movie_id'];
                    $movie->title = $row['movie_title'];
                    $movie->price = $row['movie_price'];
                    $movie->image = $row['movie_image'];
                    $movie->description = $row['movie_description'];
                    $library->movie_id = $movie;
                } else {
                    $library->movie_id = null;
                }
    
                // Series
                if (isset($row['serie_id'])) {
                    $serie = new Serie;
                    $serie->id = $row['serie_id'];
                    $serie->title = $row['serie_title'];
                    $serie->price = $row['serie_price'];
                    $serie->image = $row['serie_image'];
                    $serie->description = $row['serie_description'];
                    $library->serie_id = $serie;
                } else {
                    $library->serie_id = null;
                }
                $libraries[] = $library;
            }
    
            return $libraries;
        } catch (PDOException $e) {
            echo $e;
        }
    }


    function getUserLibrary($id)
    {
        try {
            $query = "
            SELECT l.id as library_id, l.price,
            m.id as movie_id, m.title as movie_title, m.price as movie_price, m.image as movie_image, m.description as movie_description, m.movie_id as movie_movie_id,
            s.id as serie_id, s.title as serie_title, s.price as serie_price, s.image as serie_image, s.description as serie_description, s.serie_id as serie_serie_id
            FROM `library` as l
            LEFT JOIN `movie` as m on l.movie_id = m.movie_id
            LEFT JOIN `serie` as s on l.serie_id = s.serie_id
            WHERE l.user_id = :id
            ";
            // u.id as user_id, u.username, u.email,
            // INNER JOIN `user` as u on l.user_id = u.id
            
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetchAll();
            return $this->getAllLibraryRow($row);
        } catch (PDOException $e) {
            echo $e;
        }
    }

    //------------------------------------------------------------------------------------

    function getOne($id)
    {
        try {
            $query = "
            SELECT id, user_id, movie_id, serie_id, price
            FROM `library`
            WHERE id = :id
            ";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();
            return $this->getAllRow($row);
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function insert($orders)
    {
        try {
            foreach ($orders as $order) {
                $stmt = $this->connection->prepare(
                    "INSERT into `library` (user_id, movie_id, serie_id, price)
                     VALUES (:user_id, :movie_id, :serie_id, :price)"
                );

                $stmt->execute([
                    'user_id' => $order->user_id,
                    'movie_id' => $order->movie_id,
                    'serie_id' => $order->serie_id,
                    'price' => $order->price,
                ]);

                $order->id = $this->connection->lastInsertId();
                error_log("id: " . $order->id . ", user_id: " . $order->user_id . ", movie_id: " . $order->movie_id . ", serie_id: " . $order->serie_id . ", price: " . $order->price . ".");
            }

            return true;
        } catch (PDOException $e) {
            echo $e;
            return false;
        }
    }

    function update(Order $order, $id)
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE `library` SET user_id = :user_id, movie_id = :movie_id, 
                 serie_id = :serie_id, price = :price WHERE id = :id"
            );

            $stmt->execute([
                'user_id' => $order->user_id,
                'movie_id' => $order->movie_id,
                'serie_id' => $order->serie_id,
                'price' => $order->price,
                'id' => $id,
            ]);

            return $this->getOne($order->id);
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function delete($id)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM `library` WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return;
        } catch (PDOException $e) {
            echo $e;
        }
        return true;
    }
}
