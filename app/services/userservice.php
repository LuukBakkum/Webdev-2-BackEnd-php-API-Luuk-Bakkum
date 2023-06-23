<?php
namespace Services;

use Repositories\UserRepository;

class UserService {

    private $repository;

    function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function checkUsernamePassword($username, $password) {
        return $this->repository->checkUsernamePassword($username, $password);
    }

    public function registerUser($userName, $password, $email) {
        return $this->repository->registerUser($userName, $password, $email);
    }

    public function getAll($offset = NULL, $limit = NULL) {
        return $this->repository->getAll($offset, $limit);
    }

    public function getOne($id) {
        return $this->repository->getOne($id);
    }

    public function create($user) {
        return $this->repository->insert($user);
    }

    public function update($user, $id) {
        return $this->repository->update($user, $id);
    }

    public function delete($user) {
        return $this->repository->delete($user);
    }
}

?>