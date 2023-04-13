<?php

namespace App\Models;

use CodeIgniter\Model;

class PaketBarangModels extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'tb_paket';
    protected $primaryKey           = 'p_id';
    protected $useAutoIncrement     = true;
    protected $insertID             = 0;
    protected $returnType           = 'array';
    protected $useSoftDelete        = false;
    protected $protectFields        = true;
    protected $allowedFields        = [
        "p_nama",
        "pe_id",
        "p_hargaJual",
        "p_hargaBarang",
        "pa_id",
        "p_cashback",
        "p_insentive",
        "p_laba",
        "p_persentaseLaba",
        "p_setoran",
        "p_foto",
        "u_id"
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $dateFormat           = 'datetime';
    protected $createdField         = 'created_at';
    protected $updatedField         = 'created_at';
    protected $deletedField         = 'created_at';

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
    public function datapackagingbarang()
    {
        return $this->db->table('tb_packaging')->Get()->getResultArray();
    }
    public function datapayperiode()
    {
        return $this->db->table('tb_pay_periode')->Get()->getResultArray();
    }
    public function datapaketbarang()
    {
        return $this->db->table('tb_paket')->Get()->getResultArray();
    }
    public function dataitembarang()
    {
        return $this->db->table('tb_item_barang')->Get()->getResultArray();
    }
    public function datapengambilanpaket()
    {
        return $this->db->table('tb_pengambilan_paket')->Get()->getResultArray();
    }
    public function datapaketbarangbyid($p_id)
    {
        return $this->db->table('tb_paket')->where('p_id', $p_id)->Get()->getRowArray();
    }
}
