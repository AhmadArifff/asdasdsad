<?php

namespace App\Models;

use CodeIgniter\Model;

class PengambilanTransaksiPaketBarangModels extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'tb_transaksi_pengambilan_paket';
    protected $primaryKey           = 'pp_id';
    protected $useAutoIncrement     = true;
    protected $insertID             = 0;
    protected $returnType           = 'array';
    protected $useSoftDelete        = false;
    protected $protectFields        = true;
    protected $allowedFields        = [
        "pp_t_id",
        "pp_p_id",
        "pp_ib_id",
        "pp_qty",
        "pp_ktrg_berat_ukuran"
    ];

    // Dates
    protected $useTimestamps        = false;
    protected $dateFormat           = 'datetime';
    protected $createdField         = 'created_at';
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
    public function get_pengambilan_paket_by_idi($field, $id)
    {
        return $this->where($field, $id)->findAll();
    }

    public function get_all_item_barang()
    {
        return $this->db->table('tb_item_barang')->get()->getResultArray();
    }
    public function deletes($id)
    {
        $query = "DELETE FROM tb_pengambilan_paket WHERE pp_p_id = ?";
        $this->db->query($query, [$id]);
    }
}
