<?php
namespace Models;

class Order {

    public int $id;
    public int $user_id;
    public ?int $movie_id;
    public ?int $serie_id;
    public float $price;
}

// public User $user_id;
// public Movie $movie_id;
// public Serie $serie_id;
// public string $price;
// public int $id;


