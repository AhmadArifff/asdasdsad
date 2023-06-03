<?php

namespace App\Models;

use CodeIgniter\Model;

class LogCicilanModels extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'tb_log_cicilan';
    protected $primaryKey           = 'l_id';
    protected $useAutoIncrement     = true;
    protected $insertID             = 0;
    protected $returnType           = 'array';
    protected $useSoftDelete        = false;
    protected $protectFields        = true;
    protected $allowedFields        = [
        "l_id_sementara",
        "u_id",
        "c_id",
        "t_id",
        "l_jumlah_bayar",
        "l_jumlah_pembayaran_cicilan",
        "l_approval_by",
        "l_approval_date",
        "l_foto"
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $dateFormat           = 'datetime';
    protected $createdField         = 'created_at';
    protected $updatedField         = 'due_date';
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
    public function datauser()
    {
        $query = $this->db->query('SELECT * FROM `tb_user` WHERE u_role = "coordinator" OR u_role = "owner"OR u_role = "anggota"');
        return $query->getResultArray();
    }
    public function datalogcicilan($l_id)
    {
        return $this->db->table('tb_log_cicilan')->where('l_id', $l_id)->Get()->getRowArray();
    }
    public function deletelogcicilan($l_id)
    {
        $query = $this->db->query('DELETE FROM tb_log_cicilan WHERE l_id = ?', [$l_id]);
        return $query;
    }
    public function deletelogcicilan_l_id_sementara($l_id)
    {
        $query = $this->db->query('DELETE FROM tb_log_cicilan WHERE l_id_sementara = ?', [$l_id]);
        return $query;
    }
    public function deletelogcicilan_c_id_sementara($c_id)
    {
        $query = $this->db->query('DELETE FROM tb_log_cicilan WHERE c_id = ?', [$c_id]);
        return $query;
    }
    public function getlogcicilan_by_c_id($c_id)
    {
        $query = $this->db->query('SELECT * FROM tb_log_cicilan WHERE c_id = ? ORDER BY created_at DESC LIMIT 1', [$c_id]);
        $result = $query->getRow();
        // return $query->getResultArray();
        return $result;
    }
}
