<?php
namespace Services;

use Repositories\OrderRepository;

class OrderService {

    private $repository;

    function __construct()
    {
        $this->repository = new OrderRepository();
    }

    public function getAll($offset = NULL, $limit = NULL) {
        return $this->repository->getAll($offset, $limit);
    }

    public function getAllByUserId($userId) {
        return $this->repository->getAllByUserId($userId);
    }

    public function getUserLibrary($userId) {
        return $this->repository->getUserLibrary($userId);
    }

    public function getOne($id) {
        return $this->repository->getOne($id);
    }

    public function insert($orders) {
        return $this->repository->insert($orders);
    }

    public function update($order, $id) {
        return $this->repository->update($order, $id);
    }

    public function delete($order) {
        return $this->repository->delete($order);
    }
}

?>