<?php

namespace App\Repositories\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

use App\Repositories\Base\BaseRepositoryInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Ramsey\Uuid\Type\Integer;

class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(): ?Collection
    {
        $models = $this->model->all();
        if (count($models) == 0) {
            return null;
        }
        return $models;
    }

    public function create($data): Model
    {
        return $this->model->create($data);
    }

    public function update($id, $data): Model
    {
        $this->model->where("id", $id)->update($data);
        return $this->find($id);
    }

    public function find($id): ?Model
    {
        $model = $this->model->find($id);
        if(empty($model)){
            return null;
        }
        return $model;
    }

    public function delete($id): Model
    {
        $model = $this->model->where('id', $id)->first();
        $model->delete();
        return $model;
    }
}
