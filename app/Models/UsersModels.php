<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModels extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'tb_user';
    protected $primaryKey           = 'u_id';
    protected $useAutoIncrement     = true;
    protected $insertID             = 0;
    protected $returnType           = 'array';
    protected $useSoftDelete        = false;
    protected $protectFields        = true;
    protected $allowedFields        = [
        "u_username",
        "u_password",
        "u_fullname",
        "u_role",
        "u_referensi",
        "u_email",
        "u_create_at",
        "u_nik",
        "u_nama",
        "u_tempat_lahir",
        "u_tanggal_lahir",
        "u_jenis_kelamin",
        "u_provinsi",
        "u_kota",
        "u_kelurahan",
        "u_kecamatan",
        "u_kodepos"
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $dateFormat           = 'datetime';
    protected $createdField         = 'u_create_at';
    protected $updatedField         = 'u_expired_date';
    protected $deletedField         = 'u_expired_date';

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
        return $this->db->table('tb_user')->Get()->getResultArray();
    }
    public function datauserbyid($u_referensi)
    {
        return $this->db->table('tb_user')->where('u_referensi', $u_referensi)->Get()->getResultArray();
    }
    public function getuserreferensiowner()
    {
        $query = $this->db->query('SELECT * FROM `tb_user` WHERE u_role = "coordinator"');
        return $query->getResult();
    }
    public function getuserreferensicoordinator()
    {
        $query = $this->db->query('SELECT * FROM `tb_user` WHERE u_role = "coordinator"');
        return $query->getResult();
    }
    public function getuserreferensiadmin()
    {
        $query = $this->db->query('SELECT * FROM `tb_user` WHERE u_role = "coordinator" OR u_role = "admin"');
        return $query->getResult();
    }
    public function dataprovinsi()
    {
        return $this->db->table('tbl_provinsi')->Get()->getResultArray();
    }
    public function datakabupaten1($id_provinsi)
    {
        return $this->db->table('tbl_kabupaten')->where('id_provinsi', $id_provinsi)->Get()->getResultArray();
    }
    public function datakabupaten()
    {
        return $this->db->table('tbl_kabupaten')->Get()->getResultArray();
    }
    public function datakecamatan1($id_kabupaten)
    {
        return $this->db->table('tbl_kecamatan')->where('id_kabupaten', $id_kabupaten)->Get()->getResultArray();
    }
    public function datakecamatan()
    {
        return $this->db->table('tbl_kecamatan')->Get()->getResultArray();
    }
    public function countdatauser($table)
    {
        return $this->db->table($table)->countAllResults();
    }
    public function getDataByDateRange($startDate, $endDate)
    {
        $builder = $this->db->table($this->table);
        $builder->select('*');
        $builder->where("DATE(u_create_at) BETWEEN '$startDate' AND '$endDate'");
        $query = $builder->get();
        return $query->getResult();
    }
}
