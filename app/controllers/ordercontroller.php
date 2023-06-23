<?php

namespace Controllers;

use Exception;
use Services\OrderService;

class OrderController extends Controller
{
    private $service;

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
            $token = $this->checkForJwt();
            if (!$token)
                return;

            $orders = $this->service->getUserLibrary($token->data->id);
    
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
            $jwtPayload = $this->checkForJwt();
            error_log('id= ' . $jwtPayload->data->id);
            $userId = $jwtPayload->data->id;

            $orders = [];

            $json = file_get_contents('php://input');
            $ordersData = json_decode($json);

            foreach ($ordersData as $orderData) {
                $order = $this->createObjectFromData("Models\\Order", (array)$orderData);

                if (isset($orderData->user_id)) {
                    $order->user_id = $orderData->user_id;
                } else {
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
