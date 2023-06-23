<?php

namespace Controllers;

use Exception;
use Services\MovieService;

class MovieController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new MovieService();
    }

    public function getAll()
    {
        try {
            $offset = NULL;
            $limit = NULL;
    
            if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
                $offset = $_GET["offset"];
            }
            if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
                $limit = $_GET["limit"];
            }
    
            $movies = $this->service->getAll($offset, $limit);
    
            $this->respond($movies);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getAllById($movie_id)
    {
        try {
            // Checks for a valid jwt, returns 401 if none is found
            $token = $this->checkForJwt();
            if (!$token)
                return;
    
            $movies = $this->service->getAllById($movie_id);
    
            // Check if the movies exist
            if (empty($movies)) {
                $this->respondWithError(404, "Movies not found");
                return;
            }
    
            $this->respond($movies);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getOne($id)
    {
        try {
            $movie = $this->service->getOne($id);
    
            // we might need some kind of error checking that returns a 404 if the movie is not found in the DB
            if (!$movie) {
                $this->respondWithError(404, "Movie not found");
                return;
            }
    
            $this->respond($movie);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $jwtPayload = $this->checkForJwt();
            error_log('id= ' . $jwtPayload->data->id);

            $json = file_get_contents('php://input');
            $movieData = json_decode($json);

            $movie = $this->createObjectFromData("Models\\Movie", (array) $movieData);

            $movie->id = 0;
            $movie->title = $movieData->title;
            $movie->price = $movieData->price;
            $movie->description = $movieData->description;
            $movie->image = $movieData->image;
            $movie->movie_id = $movieData->movie_id;

            $movie = $this->service->insert($movie);

            $this->respond($movie);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    protected function createObjectFromData($className, $data)
    {
        try {
            $object = new $className;
    
            foreach ($data as $property => $value) {
                if (property_exists($object, $property)) {
                    $object->$property = $value;
                }
            }
            
            return $object;
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function update($id)
    {
        try {
            $movie = $this->createObjectFromPostedJson("Models\\Movie");
            $movie = $this->service->update($movie, $id);
            
            $this->respond($movie);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $this->service->delete($id);
            $this->respond(true);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

    }
}
