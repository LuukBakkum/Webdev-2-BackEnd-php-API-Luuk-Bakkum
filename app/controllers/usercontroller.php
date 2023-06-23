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
            // read user data from request body
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
    
            // get user from db
            $user = $this->service->checkUsernamePassword($postedUser->username, $postedUser->password);
    
            // if the method returned false, the username and/or password were incorrect
            if(!$user) {
                $this->respondWithError(401, "Invalid login");
                return;
            }
    
            // generate jwt
            $tokenResponse = $this->generateJwt($user);
    
            $this->respond($tokenResponse);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function register() {

        try {
            // read user data from request body
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
    
            // check if the username is already taken
            $user = $this->service->checkUsernamePassword($postedUser->username, $postedUser->password);
    
            if($user) {
                $this->respondWithError(400, "Username already taken");
                return;
            }
    
            // register user
            $this->service->registerUser($postedUser->username, $postedUser->password, $postedUser->email);
    
            $this->respond(array("message" => "User registered"));
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function generateJwt($user) {
        try {
            $secret_key = "YOUR_SECRET_KEY";
    
            $issuer = "THE_ISSUER"; // this can be the domain/servername that issues the token
            $audience = "THE_AUDIENCE"; // this can be the domain/servername that checks the token
    
            $issuedAt = time(); // issued at
            $notbefore = $issuedAt; //not valid before 
            $expire = $issuedAt + 6000; // expiration time is set at +600 seconds (10 minutes)
    
            // JWT expiration times should be kept short (10-30 minutes)
            // A refresh token system should be implemented if we want clients to stay logged in for longer periods
    
            // note how these claims are 3 characters long to keep the JWT as small as possible
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
            // Checks for a valid jwt, returns 401 if none is found
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
            // Checks for a valid jwt, returns 401 if none is found
            $token = $this->checkForJwt();
            if (!$token)
                return;
    
            $user = $this->service->getOne($id);
    
            // Check if the user exists
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
            // Checks for a valid jwt, returns 401 if none is found
            $token = $this->checkForJwt();
            if (!$token)
                return;
    
            // Check if the user is an admin
            if (!$token->data->admin) {
                $this->respondWithError(401, "Unauthorized");
                return;
            }
    
            // read user data from request body
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
    
            // create user
            $user = $this->service->create($postedUser);
    
            $this->respond($user);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function update($id)
    {
        try {
            // Checks for a valid jwt, returns 401 if none is found
            $token = $this->checkForJwt();
            if (!$token)
                return;
    
            // Check if the user is an admin
            if (!$token->data->admin) {
                $this->respondWithError(401, "Unauthorized");
                return;
            }
    
            // read user data from request body
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
    
            // Check if the user exists
            if (empty($this->service->getOne($id))) {
                $this->respondWithError(404, "User not found");
                return;
            }
    
            // update user
            $user = $this->service->update($postedUser, $id);
    
            $this->respond($user);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            // Checks for a valid jwt, returns 401 if none is found
            $token = $this->checkForJwt();
            if (!$token)
                return;
    
            // Check if the user is an admin
            if (!$token->data->admin) {
                $this->respondWithError(401, "Unauthorized");
                return;
            }
    
            // Check if the user exists
            if (empty($this->service->getOne($id))) {
                $this->respondWithError(404, "User not found");
                return;
            }
    
            // delete user
            $this->service->delete($id);
    
            $this->respond(array("message" => "User deleted"));
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }
}
