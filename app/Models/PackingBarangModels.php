<?php

namespace App\Models;

use CodeIgniter\Model;

class PackingBarangModels extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'tb_packaging';
    protected $primaryKey           = 'pa_id';
    protected $useAutoIncrement     = true;
    protected $insertID             = 0;
    protected $returnType           = 'array';
    protected $useSoftDelete        = false;
    protected $protectFields        = true;
    protected $allowedFields        = [
        "pa_nama",
        "pa_harga",
        "pa_foto",
        "u_id"
    ];

    // Dates
    protected $useTimestamps        = false;
    protected $dateFormat           = 'datetime';
    protected $createdField         = 'u_created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks       = true;
    protected $beforeInsert         = [];
    protected $afterInsert          = [];
    protected $beforeUpdate         = [];
    protected $afterUpdate          = [];
    protected $beforeFind           = [];
    protected $afterFind            = [];
    protected $beforeDelete         = [];
    protected $afterDelete          = [];

    public function getpacking_by_id($pa_id)
    {
        return $this->db->table('tb_packaging')->where('pa_id', $pa_id)->get()->getRow();
    }
    public function HargaPackaging($pa_id)
    {
        return $this->db->table('tb_packaging')->where('pa_id', $pa_id)->Get()->getResultArray();
    }
    public function datapackaging($pa_id)
    {
        return $this->db->table('tb_packaging')->where('pa_id', $pa_id)->Get()->getRowArray();
    }
}
