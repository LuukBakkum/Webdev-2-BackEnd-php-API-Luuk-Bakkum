<?php

namespace Repositories;

use Models\Movie;
use PDO;
use PDOException;
use Repositories\Repository;

class MovieRepository extends Repository
{
    function getAll($offset = NULL, $limit = NULL)
    {
        try {
            // waarschijnlijk nodig: title, description, image, movie_id
            $query = "SELECT id, title, price, description, image, movie_id FROM movie";
            if (isset($limit) && isset($offset)) {
                $query .= " LIMIT :limit OFFSET :offset ";
            }
            $stmt = $this->connection->prepare($query);
            if (isset($limit) && isset($offset)) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();

            $movies = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $movies[] = $this->rowToMovie($row);
            }

            return $movies;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getAllById($movie_id)
    {
        try {
            $query = "SELECT id, title, price, description, image, movie_id FROM movie WHERE movie_id = :movie_id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':movie_id', $movie_id);
            $stmt->execute();

            $movies = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $movies[] = $this->rowToMovie($row);
            }

            return $movies;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getOne($id)
    {
        try {
            $query = "SELECT id, title, price, description, image, movie_id FROM movie WHERE id = :id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();
            $movie = $this->rowToMovie($row);

            return $movie;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function rowToMovie($row)
    {
        $movie = new Movie();
        $movie->id = $row['id'];
        $movie->title = $row['title'];
        $movie->price = $row['price'];
        $movie->description = $row['description'];
        $movie->image = $row['image'];
        $movie->movie_id = $row['movie_id'];

        return $movie;
    }

    function insert($movie)
    {
        try {
            $stmt = $this->connection->prepare("INSERT into movie (title, price, description, image, movie_id) VALUES (?,?,?,?,?)");

            $stmt->execute([$movie->title, $movie->price, $movie->description, $movie->image, $movie->movie_id]);

            $movie->id = $this->connection->lastInsertId();

            return $this->getOne($movie->id);
        } catch (PDOException $e) {
            echo $e;
        }
    }


    function update($movie, $id)
    {
        try {
            $stmt = $this->connection->prepare("UPDATE movie SET title = ?, price = ?, description = ?, image = ?, movie_id = ? WHERE id = ?");

            $stmt->execute([$movie->title, $movie->price, $movie->description, $movie->image, $movie->movie_id, $id]);

            return $this->getOne($movie->id);
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function delete($id)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM movie WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return;
        } catch (PDOException $e) {
            echo $e;
        }
        return true;
    }
}
