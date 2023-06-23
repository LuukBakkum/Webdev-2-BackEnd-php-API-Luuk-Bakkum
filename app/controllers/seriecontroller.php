<?php

namespace Controllers;

use Exception;
use Services\SerieService;

class SerieController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new SerieService();
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
    
            $series = $this->service->getAll($offset, $limit);
    
            $this->respond($series);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getAllById($serie_id)
    {
        try {
            // Checks for a valid jwt, returns 401 if none is found
            $token = $this->checkForJwt();
            if (!$token)
                return;
                
            $series = $this->service->getAllById($serie_id);
    
            // Check if the series exist
            if (empty($series)) {
                $this->respondWithError(404, "Series not found");
                return;
            }
    
            $this->respond($series);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getOne($id)
    {
        try {
            $serie = $this->service->getOne($id);
    
            // we might need some kind of error checking that returns a 404 if the serie is not found in the DB
            if (!$serie) {
                $this->respondWithError(404, "Serie not found");
                return;
            }
    
            $this->respond($serie);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $serie = $this->createObjectFromPostedJson("Models\\Serie");
            $serie = $this->service->insert($serie);
            
            $this->respond($serie);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

    }

    public function update($id)
    {
        try {
            $serie = $this->createObjectFromPostedJson("Models\\Serie");
            $serie = $this->service->update($serie, $id);

            $this->respond($serie);
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
