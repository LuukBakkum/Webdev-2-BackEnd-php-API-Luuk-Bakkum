<?php
namespace Services;

use Repositories\MovieRepository;

class MovieService {

    private $repository;

    function __construct()
    {
        $this->repository = new MovieRepository();
    }

    public function getAll($offset = NULL, $limit = NULL) {
        return $this->repository->getAll($offset, $limit);
    }

    public function getAllById($movie_id) {
        return $this->repository->getAllById($movie_id);
    }

    public function getOne($id) {
        return $this->repository->getOne($id);
    }

    public function insert($serie) {
        return $this->repository->insert($serie);
    }

    public function update($serie, $id) {
        return $this->repository->update($serie, $id);
    }

    public function delete($serie) {
        return $this->repository->delete($serie);
    }
}

?>