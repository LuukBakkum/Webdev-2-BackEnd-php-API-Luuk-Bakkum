<?php

namespace Repositories;

use Models\Serie;
use PDO;
use PDOException;
use Repositories\Repository;

class SerieRepository extends Repository
{
    function getAll($offset = NULL, $limit = NULL)
    {
        try {
            // waarschijnlijk nodig: title, description, image, serie_id
            $query = "SELECT id, title, price, description, image, serie_id FROM serie";
            if (isset($limit) && isset($offset)) {
                $query .= " LIMIT :limit OFFSET :offset ";
            }
            $stmt = $this->connection->prepare($query);
            if (isset($limit) && isset($offset)) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();

            $series = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {               
                $series[] = $this->rowToSerie($row);
            }

            return $series;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getAllById($serie_id){
        try {
            $query = "SELECT id, title, price, description, image, serie_id FROM serie WHERE serie_id = :serie_id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':serie_id', $serie_id);
            $stmt->execute();

            $series = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {               
                $series[] = $this->rowToSerie($row);
            }

            return $series;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getOne($id)
    {
        try {
            $query = "SELECT id, title, price, description, image, serie_id FROM serie WHERE id = :id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();
            $serie = $this->rowToSerie($row);

            return $serie;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function rowToSerie($row) {
        $serie = new Serie();
        $serie->id = $row['id'];
        $serie->title = $row['title'];
        $serie->price = $row['price'];
        $serie->description = $row['description'];
        $serie->image = $row['image'];
        $serie->serie_id = $row['serie_id'];

        return $serie;
    }

    function insert($serie)
    {
        try {
            $stmt = $this->connection->prepare("INSERT into serie (title, price, description, image, serie_id) VALUES (?,?,?,?,?)");

            $stmt->execute([$serie->title, $serie->price, $serie->description, $serie->image, $serie->serie_id]);

            $serie->id = $this->connection->lastInsertId();

            return $this->getOne($serie->id);
        } catch (PDOException $e) {
            echo $e;
        }
    }


    function update($serie, $id)
    {
        try {
            $stmt = $this->connection->prepare("UPDATE serie SET title = ?, price = ?, description = ?, image = ?, serie_id = ? WHERE id = ?");

            $stmt->execute([$serie->title, $serie->price, $serie->description, $serie->image, $serie->serie_id, $id]);

            return $this->getOne($serie->id);
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function delete($id)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM serie WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return;
        } catch (PDOException $e) {
            echo $e;
        }
        return true;
    }
}