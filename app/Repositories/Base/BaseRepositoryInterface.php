<?php

namespace App\Repositories\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Ramsey\Uuid\Type\Integer;

interface BaseRepositoryInterface
{
    public function all(): ?Collection;
    public function create(array $data): Model;
    public function update(int $id, array $data): Model;
    public function find(int $id): ?Model;
    public function delete(int $id): Model;
}
