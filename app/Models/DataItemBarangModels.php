<?php

namespace App\Models;

use CodeIgniter\Model;

class DataItemBarangModels extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'tb_item_barang';
    protected $primaryKey           = 'ib_id';
    protected $useAutoIncrement     = true;
    protected $insertID             = 0;
    protected $returnType           = 'array';
    protected $useSoftDelete        = false;
    protected $protectFields        = true;
    protected $allowedFields        = [
        "ib_nama",
        "ib_harga",
        // "ib_qty_stok",
        "ib_qty_jual",
        "ib_qty_beli",
        // "ib_qty_sisa_bei",
        "ib_berat/ukuran",
        "ib_ktrg_berat/ukuran",
        "ib_foto",
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
    public function dataitembarang($ib_id)
    {
        return $this->db->table('tb_item_barang')->where('ib_id', $ib_id)->Get()->getRowArray();
    }
}
