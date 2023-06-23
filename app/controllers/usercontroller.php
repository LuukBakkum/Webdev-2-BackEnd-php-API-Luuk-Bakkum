<?php

namespace Controllers;

use Exception;
use Services\UserService;
use \Firebase\JWT\JWT;

class UserController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new UserService();
    }

    public function login() {

        try {
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
            $user = $this->service->checkUsernamePassword($postedUser->username, $postedUser->password);
    
            if(!$user) {
                $this->respondWithError(401, "Invalid login");
                return;
            }
    
            $tokenResponse = $this->generateJwt($user);
            $this->respond($tokenResponse);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function register() {

        try {
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
    
            $user = $this->service->checkUsernamePassword($postedUser->username, $postedUser->password);
            if($user) {
                $this->respondWithError(400, "Username already taken");
                return;
            }
    
            $registeredUser = $this->service->registerUser($postedUser->username, $postedUser->password, $postedUser->email);
            $this->respond($registeredUser);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function generateJwt($user) {
        try {
            $secret_key = "YOUR_SECRET_KEY";
    
            $issuer = "THE_ISSUER";
            $audience = "THE_AUDIENCE";
    
            $issuedAt = time();
            $notbefore = $issuedAt;
            $expire = $issuedAt + 6000;
    
            $payload = array(
                "iss" => $issuer,
                "aud" => $audience,
                "iat" => $issuedAt,
                "nbf" => $notbefore,
                "exp" => $expire,
                "data" => array(
                    "id" => $user->id,
                    "username" => $user->username,
                    "email" => $user->email,
                    "admin" => $user->admin
            ));
    
            $jwt = JWT::encode($payload, $secret_key, 'HS256');
    
            return
                array(
                    "message" => "Successful login.",
                    "jwt" => $jwt,
                    "user_id" => $user->id,
                    "username" => $user->username,
                    "email" => $user->email,
                    "admin" => $user->admin,
                    "expireAt" => $expire
                );
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
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

    public function getOne($id)
    {
        try {
            $token = $this->checkForJwt();
            if (!$token)
                return;
    
            $user = $this->service->getOne($id);
    
            if (empty($user)) {
                $this->respondWithError(404, "User not found");
                return;
            }
    
            $this->respond($user);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $token = $this->checkForJwt();
            if (!$token)
                return;

            if (!$token->data->admin) {
                $this->respondWithError(401, "Unauthorized");
                return;
            }
    
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
    
            $user = $this->service->create($postedUser);
    
            $this->respond($user);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function update($id)
    {
        try {
            $token = $this->checkForJwt();
            if (!$token)
                return;
    
            if (!$token->data->admin) {
                $this->respondWithError(401, "Unauthorized");
                return;
            }
    
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
    
            if (empty($this->service->getOne($id))) {
                $this->respondWithError(404, "User not found");
                return;
            }
    
            $user = $this->service->update($postedUser, $id);
    
            $this->respond($user);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $token = $this->checkForJwt();
            if (!$token)
                return;
    
            if (!$token->data->admin) {
                $this->respondWithError(401, "Unauthorized");
                return;
            }
    
            if (empty($this->service->getOne($id))) {
                $this->respondWithError(404, "User not found");
                return;
            }
    
            $this->service->delete($id);
    
            $this->respond(array("message" => "User deleted"));
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }
}
