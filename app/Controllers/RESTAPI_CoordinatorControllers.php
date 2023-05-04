<?php

namespace App\Controllers;

// use App\Controllers\BaseController;
use App\Models\UsersModels;
use CodeIgniter\RESTful\ResourceController;


class RESTAPI_CoordinatorControllers extends ResourceController
{
    protected $format = 'json';

    public function getdatauser()
    {
        $UsersModels = new UsersModels();
        $dataUser = $UsersModels->findAll();
        return $this->respond($dataUser);
    }
}
