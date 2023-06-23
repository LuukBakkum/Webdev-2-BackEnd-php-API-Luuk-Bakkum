<?php

namespace Repositories;

use PDO;
use PDOException;
use Models\User;
use Repositories\Repository;

class UserRepository extends Repository
{
    function checkUsernamePassword($username, $password)
    {
        try {
            // retrieve the user with the given username
            $stmt = $this->connection->prepare("SELECT `id`, `username`, `password`, `email`, `admin` FROM `user` WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\User');
            $user = $stmt->fetch();

            if (!$user) {
                error_log('User not found');
                return false;
            }

            // verify if the password matches the hash in the database
            $result = $this->verifyPassword($password, $user->password);
            error_log($result = $this->verifyPassword($password, $user->password));
            error_log('Password matches: ' . $result . ' for user ' . $user->username);

            if (!$result) {
                error_log('Password does not match');
                return false;
            }

            // do not pass the password hash to the caller
            $user->password = "";

            return $user;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function registerUser($userName, $password, $email)
    {
        try {
            $stmt = $this->connection->prepare("INSERT INTO user (username, password, email, admin) VALUES (:username, :password, :email, :admin)");

            $password = $this->hashPassword($password);
            $stmt->bindParam(':username', $userName);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':admin', 0);
            $stmt->execute();

            $id = $this->connection->lastInsertId();

            return $user = $this->getOne($id);

        } catch (PDOException $e) {
            echo $e;
        }
    }

    function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    function verifyPassword($input, $hash)
    {
        return password_verify($input, $hash);
    }

    function getAll()
    {
        try {
            $stmt = $this->connection->prepare("SELECT `id`, `username`, `password`, `email`, `admin` FROM `user`");
            $stmt->execute();

            $users = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $users[] = $this->rowToUser($row);
            }

            return $users;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function rowToUser($row)
    {
        $user = new User();

        $user->id = $row['id'];
        $user->username = $row['username'];
        $user->password = $row['password'];
        $user->email = $row['email'];
        $user->admin = $row['admin'];

        return $user;
    }

    function getOne($id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT `id`, `username`, `password`, `email`, `admin` FROM `user` WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();
            $user = $this->rowToUser($row);

            return $user;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function insert($user)
    {
        try {
            $stmt = $this->connection->prepare("INSERT INTO `user` (username, password, email, admin) VALUES (:username, :password, :email, :admin)");

            $password = $this->hashPassword($user->password);
            $isAdmin = ($user->admin) ? 1 : 0;

            $stmt->execute([
                'username' => $user->username,
                'password' => $password,
                'email' => $user->email,
                'admin' => $isAdmin,
            ]);

            $user->id = $this->connection->lastInsertId();
            
            return true;
        } catch (PDOException $e) {
            echo $e;
            error_log("id: " . $user->id . ", username: " . $user->username . ", password: " . $user->password . ", email: " . $user->email . ", admin: " . $user->admin . ".");
            return false;
        }
    }

    function update($user, $id)
    {
        try {
            $stmt = $this->connection->prepare("UPDATE `user` SET username = :username, password = :password, email = :email, admin = :admin WHERE id = :id");

            $password = $this->hashPassword($user->password);
            $isAdmin = ($user->admin) ? 1 : 0;

            $stmt->execute([
                'username' => $user->username,
                'password' => $password,
                'email' => $user->email,
                'admin' => $isAdmin,
                'id' => $id,
            ]);

            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    function delete($id)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM user WHERE id = :id");

            $stmt->execute([
                'id' => $id,
            ]);

            error_log("id: " . $id . ".");

            return true;
        } catch (PDOException $e) {
            echo $e;
            return false;
        }
    }
}
