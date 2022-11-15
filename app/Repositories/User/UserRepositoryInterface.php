<?php

namespace App\Repositories\User;

use Illuminate\Http\Request;

interface UserRepositoryInterface
{

    public function getUserByEmail(string $email);
    public function findUnexpiredUser($id);
    public function getUserByToken(string $token);
}
