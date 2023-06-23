<?php

namespace Controllers;

use Exception;
use Services\OrderService;

class OrderController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new OrderService();
    }

    public function getAll()
    {
        try {
            $token = $this->checkForJwt();
            if (!$token)
                return;
    
            $offset = NULL;
            $limit = NULL;
    
            if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
                $offset = $_GET["offset"];
            }
            if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
                $limit = $_GET["limit"];
            }
    
            $orders = $this->service->getAll($offset, $limit);
    
            $this->respond($orders);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getAllByUserId()
    {
        try {
            // Checks for a valid jwt, returns 401 if none is found
            $token = $this->checkForJwt();
            if (!$token)
                return;
    
            // Assuming the service has a method getAllByUserId that fetches orders based on user_id
            // $orders = $this->service->getAllByUserId($userId);
            $orders = $this->service->getUserLibrary($token->data->id);
    
            // Check if the orders exist
            if (empty($orders)) {
                $this->respondWithError(404, "Orders not found");
                return;
            }
    
            $this->respond($orders);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getOne($id)
    {
        try {
            $orders = $this->service->getOne($id);
    
            // we might need some kind of error checking that returns a 404 if the orders is not found in the DB
            if (!$orders) {
                $this->respondWithError(404, "Orders not found");
                return;
            }
    
            $this->respond($orders);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function create()
    {
        try {
            // Decode JWT and get user ID
            $jwtPayload = $this->checkForJwt();
            error_log('id= ' . $jwtPayload->data->id);
            $userId = $jwtPayload->data->id; // Assuming the user ID is stored in a field called 'userId' in JWT

            $orders = [];

            // Get the posted JSON data and decode it into an array of objects
            $json = file_get_contents('php://input');
            $ordersData = json_decode($json);


            foreach ($ordersData as $orderData) {
                // Create the Order object
                $order = $this->createObjectFromData("Models\\Order", (array)$orderData);

                if (isset($orderData->user_id)) {
                    $order->user_id = $orderData->user_id;
                } else {
                    // Set the user ID from JWT to the Order object
                    $order->user_id = $userId;
                }

                $order->movie_id = $orderData->movie_id ?? null;
                $order->serie_id = $orderData->serie_id ?? null;
                $order->price = (float) $orderData->price;

                $orders[] = $order;
            }
            $inserted = $this->service->insert($orders);

            if ($inserted) {
                $this->respond($orders);
            } else {
                $this->respondWithError(500, "Error inserting orders.");
            }
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
            $order = $this->createObjectFromPostedJson("Models\\Order");
            $order = $this->service->update($order, $id);
            $this->respond($order);
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
