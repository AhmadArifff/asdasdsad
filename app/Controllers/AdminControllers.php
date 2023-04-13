<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModels;
use App\Models\KabupatenModels;
use App\Models\KecamatanModels;
use App\Models\ProvinsiModels;
use App\Models\BarangModels;
use App\Models\DataItemBarangModels;
use App\Models\PackingBarangModels;
use App\Models\PeriodePembayaranModels;
use App\Models\PaketBarangModels;
use App\Models\TransaksiModels;
use App\Models\CicilanModels;
use App\Models\LogCicilanModels;
use App\Models\LogCicilanSementaraModels;
use App\Models\PengambilanPaketBarangModels;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * @property IncomingRequest $request ,$post, $load
 */




class AdminControllers extends BaseController
{
    public function __construct()
    {
        if (session()->get('u_role') != "admin") {
            echo 'Access denied';
            exit;
        }
        // $this->load->library('select2');
    }
    public function Kabupaten()
    {
        $UsersModels = new UsersModels();
        $id_provinsi = $this->request->getPost('u_provinsi');
        $kab = $UsersModels->datakabupaten1($id_provinsi);
        echo '<option value="">----Pilih Kabupaten---- </option>';
        foreach ($kab as $value => $k) {
            echo "<option value=" . $k['id_kabupaten'] . ">" . $k['nama_kabupaten'] . "</option>";
        }
    }
    public function Kecamatan()
    {
        $UsersModels = new UsersModels();
        $id_kabupaten = $this->request->getPost('u_kota');
        $kab = $UsersModels->datakecamatan1($id_kabupaten);
        echo '<option value="">----Pilih Kecamatan---- </option>';
        foreach ($kab as $value => $k) {
            echo "<option value=" . $k['id_kecamatan'] . ">" . $k['nama_kecamatan'] . "</option>";
        }
    }
    public function PaketCicilan()
    {
        $cicilanModels = new CicilanModels();
        $paketbarang = new PaketBarangModels();
        $pengambilanpaketbarang = new PengambilanPaketBarangModels();
        $u_id = $this->request->getPost('u_id');
        $p_id = $this->request->getPost('p_id');
        $kab = $cicilanModels->paketcicilan($u_id);
        $cicilan = $cicilanModels->findAll();
        $pengambilanpaket = $pengambilanpaketbarang->get_pengambilan_paket_by_idi('pp_p_id', $p_id);
        $itembarang = $pengambilanpaketbarang->get_all_item_barang();
        echo '<option value="">--Pilih Paket Cicilan--</option>';
        foreach ($kab as $value => $k) {
            $tampil = '';
            foreach ($pengambilanpaket as $tb_pengambilan_paket) {
                if ($k['p_id'] == $tb_pengambilan_paket['pp_p_id']) {
                    foreach ($itembarang as $tb_item_barang) {
                        $sb = $tb_pengambilan_paket['pp_ib_id'];
                        if ($sb == $tb_item_barang['ib_id']) {
                            $tampil .= $tb_item_barang['p_nama'] . ", ";
                        }
                    }
                }
            }
            if (!empty($tampil)) {
                echo "<option value=" . $k['t_id'] . ">" . rtrim($tampil, ", ") . " - Jumlah Paket :" . $k['t_qty'] . "</option>";
            }
        }
    }
    public function PaketLogCicilan()
    {
        $cicilanModels = new CicilanModels();
        $pengambilanpaketbarang = new PengambilanPaketBarangModels();
        $u_id = $this->request->getPost('u_id');
        $kab = $cicilanModels->get_cicilan($u_id);
        $transaksi = $cicilanModels->paketcicilan($u_id);
        $datapaket = $cicilanModels->datapaket();
        echo '<option value="">--Pilih Paket Cicilan--</option>';
        foreach ($kab as $value => $k) {
            $tampil = '';
            foreach ($transaksi as $tb_transaksi) {
                foreach ($datapaket as $tb_paket) {
                    if ($tb_transaksi['p_id'] == $tb_paket['p_id']) {
                        $tampil .= $tb_paket['p_nama'];
                        $qty = $tb_transaksi['t_qty'];
                    }
                }
            }
        }
        if (!empty($tampil)) {
            echo "<option value=" . $k['c_id'] . ">" . rtrim($tampil, ", ") . " - Jumlah Paket :" . $qty . "</option>";
        }
    }
    public function ShowPayperiode()
    {
        $paketbarang = new PaketBarangModels();
        $transaksi = new TransaksiModels();
        $p_id = $this->request->getPost('p_id');
        $Harga = $transaksi->HargaPaket($p_id);
        $dataperiode = $paketbarang->datapayperiode();
        // $datapaket = $paketbarang->datapaketbarangbyid($p_id);
        $tampil = ''; // variabel $tampil harus dideklarasikan di luar loop agar bisa bertambah satu
        foreach ($Harga as $value => $periode) {
            foreach ($dataperiode as $key => $value) {
                if ($p_id == $periode['p_id']) {
                    $pe_id = $periode['pe_id'];
                    if ($pe_id == $value['pe_id']) {
                        $tampil .= "<option value=" . $value['pe_id'] . ">" . rtrim($value['pe_nama']) . "</option>";
                        // nilai $tampil harus ditambahkan dengan hasil penggabungan string baru agar bertambah satu
                    }
                }
            }
        }
        if (!empty($tampil)) {
            echo $tampil; // nilai $tampil sekarang digunakan di sini untuk menampilkan semua opsi pada elemen select
        }
    }

    public function TotalHarga()
    {
        $transaksi = new TransaksiModels();
        $p_id = $this->request->getPost('p_id');
        $Harga = $transaksi->HargaPaket($p_id);
        foreach ($Harga as $value => $H) {
            // echo "<option value=" . $H['id_kabupaten'] . ">" . $H['nama_kabupaten'] . "</option>";
            echo "<input type=" . "text" . " name=" . "p_hargapaket" . " class=" . "form-control " . " placeholder=" . "Masukan Cashback Paket Barang" . " hidden required  value=" . $H['p_hargaJual'] . ">";
            // echo "value=" . $H['pa_harga'] . ">";
        }
    }
    public function index()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $menu = [
            'AdminDashboard' => 'dashboard',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataKategoriPaket' => '',
            'DataBarang' => '',
            'MenuDataBarang' => '',
            'DataBarangSupplier' => '',
            'DataPackingBarang' => '',
            'DataPeriodeTransaksi' => '',
            'MenuDataTransaksi' => '',
            'DataPaketBarang' => '',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),
            // 'countdatauser' => $UsersModels->countAllResults(),
        ];
        return view('admin/dashboard', $menu);
    }
    //user
    public function registeruser()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => 'registeruser',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'MenuDataBarang' => '',
            'DataBarangSupplier' => '',
            'DataPackingBarang' => '',
            'MenuDataTransaksi' => '',
            'DataPeriodeTransaksi' => '',
            'DataKategoriPaket' => '',
            'DataPaketBarang' => '',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'provinsi' => $UsersModels->dataprovinsi(),
            'kabupaten' => $UsersModels->datakabupaten(),
            'kecamatan' => $UsersModels->datakecamatan(),
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        helper(['form', 'url']);
        $data['nameuser'] = $UsersModels->getuserreferensiadmin();
        return view('admin/DatabaseUser/registeruser', $data);
        // return view('register');
    }
    public function registeruserprocess()
    {
        if (!$this->validate([
            'u_username' => [
                'rules' => 'required|min_length[4]|max_length[20]|is_unique[tb_user.u_username]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 20 Karakter',
                    'is_unique' => 'Username sudah digunakan sebelumnya'
                ]
            ],
            'u_password' => [
                'rules' => 'required|min_length[4]|max_length[50]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 50 Karakter',
                ]
            ],
            'password-confirm' => [
                'rules' => 'matches[u_password]',
                'errors' => [
                    'matches' => 'Konfirmasi Password tidak sesuai dengan password',
                ]
            ],
            'u_fullname' => [
                'rules' => 'required|min_length[4]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 100 Karakter',
                ]
            ],
            'u_role' => [
                'rules' => 'required|min_length[1]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                ]
            ],
            'u_referensi' => [
                'rules' => 'required|min_length[1]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                ]
            ],
            'u_email' => [
                'rules' => 'required|min_length[4]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 100 Karakter',
                ]
            ],
            'u_create_at' => [
                'rules' => 'required|min_length[4]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 100 Karakter',
                ]
            ],
            'u_nik' => [
                'rules' => 'required|min_length[4]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 100 Karakter',
                ]
            ],
            'u_nama' => [
                'rules' => 'required|min_length[4]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 100 Karakter',
                ]
            ],
            'u_tempat_lahir' => [
                'rules' => 'required|min_length[4]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 100 Karakter',
                ]
            ],
            'u_tanggal_lahir' => [
                'rules' => 'required|min_length[4]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 100 Karakter',
                ]
            ],
            'u_jenis_kelamin' => [
                'rules' => 'required|min_length[4]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 100 Karakter',
                ]
            ],
            'u_provinsi' => [
                'rules' => 'required|min_length[1]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 100 Karakter',
                ]
            ],
            'u_kelurahan' => [
                'rules' => 'required|min_length[4]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 100 Karakter',
                ]
            ],
            'u_kecamatan' => [
                'rules' => 'required|min_length[4]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 100 Karakter',
                ]
            ],
            'u_kodepos' => [
                'rules' => 'required|min_length[4]|max_length[100]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 100 Karakter',
                ]
            ],
        ])) {
            session()->setFlashdata('error', $this->validator->listErrors());
            return redirect()->back()->withInput();
        }
        $users = new UsersModels();
        $users->insert([
            'u_username' => $this->request->getVar('u_username'),
            'u_password' => password_hash($this->request->getVar('u_password'), PASSWORD_BCRYPT),
            'u_fullname' => strtoupper($this->request->getVar('u_fullname')),
            'u_role' => $this->request->getVar('u_role'),
            'u_referensi' => $this->request->getVar('u_referensi'),
            'u_email' => $this->request->getVar('u_email'),
            'u_create_at' => $this->request->getVar('u_create_at'),
            'u_nik' => $this->request->getVar('u_nik'),
            'u_nama' => strtoupper($this->request->getVar('u_nama')),
            'u_tempat_lahir' => $this->request->getVar('u_tempat_lahir'),
            'u_tanggal_lahir' => $this->request->getVar('u_tanggal_lahir'),
            'u_jenis_kelamin' => $this->request->getVar('u_jenis_kelamin'),
            'u_provinsi' => $this->request->getVar('u_provinsi'),
            'u_kota' => $this->request->getVar('u_kota'),
            'u_kelurahan' => $this->request->getVar('u_kelurahan'),
            'u_kecamatan' => $this->request->getVar('u_kecamatan'),
            'u_kodepos' => $this->request->getVar('u_kodepos')
        ]);
        session()->setFlashdata('success', 'Data Berhasil Disimpan!');
        return redirect()->to('/admin/databaseuser/registeruser');
    }
    public function listdatauser()
    {
        $transaksi = new TransaksiModels();
        $UsersModels = new UsersModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => 'registeruser',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataPackingBarang' => '',
            'MenuDataTransaksi' => '',
            'DataKategoriPaket' => '',
            'DataPeriodeTransaksi' => '',
            'DataPaketBarang' => '',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'datauser' => $UsersModels->datauser(),
            'provinsi' => $UsersModels->dataprovinsi(),
            'kabupaten' => $UsersModels->datakabupaten(),
            'kecamatan' => $UsersModels->datakecamatan(),
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        $data['tb_user'] = $UsersModels->findAll();
        echo view('admin/DatabaseUser/datauser', $data);
        // helper(['form', 'url']);
        // $UsersModels = new UsersModels();
        // $dataa['nameuser'] = $UsersModels->getuserreferensiadmin();
        // return view('admin/datauser', $dataa);
        //berdasarkan login
        // $user = new UsersModels();
        // $data['tb_user'] = $user->where('u_referensi', session('u_id'))->findAll();
        // echo view('admin/datauser', $data);
    }
    public function edituser($u_id)
    {
        $transaksi = new TransaksiModels();
        $UsersModels = new UsersModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => 'registeruser',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataPackingBarang' => '',
            'MenuDataTransaksi' => '',
            'DataPeriodeTransaksi' => '',
            'DataKategoriPaket' => '',
            'DataPaketBarang' => '',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'provinsi' => $UsersModels->dataprovinsi(),
            'kabupaten' => $UsersModels->datakabupaten(),
            'kecamatan' => $UsersModels->datakecamatan(),
            'datauser' => $UsersModels->datauser(),
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        // ambil artikel yang akan diedit

        $data['tb_user'] = $UsersModels->where('u_id', $u_id)->first();

        // lakukan validasi data artikel
        $validation = \Config\Services::validation();
        $validation->setRules([
            'u_username' => 'required',
            'u_password' => 'required',
            'password-confirm' => 'required',
            'u_fullname' => 'required',
            'u_role' => 'required',
            'u_referensi' => 'required',
            'u_email' => 'required',
            'u_create_at' => 'required',
            'u_nik' => 'required',
            'u_nama' => 'required',
            'u_tempat_lahir' => 'required',
            'u_tanggal_lahir' => 'required',
            'u_jenis_kelamin' => 'required',
            'u_provinsi' => 'required',
            'u_kota' => 'required',
            'u_kelurahan' => 'required',
            'u_kecamatan' => 'required',
            'u_kodepos' => 'required',
        ]);
        $isDataValid = $validation->withRequest($this->request)->run();
        // jika data vlid, maka simpan ke database
        if ($isDataValid) {
            $UsersModels->update($u_id, [
                'u_username' => $this->request->getVar('u_username'),
                'u_password' => password_hash($this->request->getVar('u_password'), PASSWORD_BCRYPT),
                'u_fullname' => strtoupper($this->request->getVar('u_fullname')),
                'u_role' => $this->request->getVar('u_role'),
                'u_referensi' => $this->request->getVar('u_referensi'),
                'u_email' => $this->request->getVar('u_email'),
                'u_create_at' => $this->request->getVar('u_create_at'),
                'u_nik' => $this->request->getVar('u_nik'),
                'u_nama' => strtoupper($this->request->getVar('u_nama')),
                'u_tempat_lahir' => $this->request->getVar('u_tempat_lahir'),
                'u_tanggal_lahir' => $this->request->getVar('u_tanggal_lahir'),
                'u_jenis_kelamin' => $this->request->getVar('u_jenis_kelamin'),
                'u_provinsi' => $this->request->getVar('u_provinsi'),
                'u_kota' => $this->request->getVar('u_kota'),
                'u_kelurahan' => $this->request->getVar('u_kelurahan'),
                'u_kecamatan' => $this->request->getVar('u_kecamatan'),
                'u_kodepos' => $this->request->getVar('u_kodepos')
            ]);
            session()->setFlashdata('success', 'Data Berhasil Di Edit!');
            return redirect('admin/databaseuser/datauser');
        }

        // tampilkan form edit
        // helper(['form', 'url']);
        // $UsersModels = new UsersModels();
        // $data['tb_user'] = $UsersModels->getuserreferensiadmin();
        echo view('admin/DatabaseUser/registeredituser', $data);
    }
    public function deleteuser($u_id)
    {
        $user = new UsersModels();
        $user->delete($u_id);
        session()->setFlashdata('success', 'Data Berhasil Di Hapus!');
        return redirect('admin/databaseuser/datauser');
    }
    public function ImportFileExcelUser()
    {
        $user = new UsersModels();
        $file = $this->request->getFile('file');
        $extension = $file->getClientExtension();
        if ($extension == 'xlsx' || $extension == 'xls') {
            if ($extension == 'xls') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            $spreadsheet = $reader->load($file);
            $users = $spreadsheet->getActiveSheet()->toArray();
            $i = 1;
            foreach ($users as $key => $value) {
                if ($key == 0) {
                    continue;
                }
                if (isset($value[2])) {
                    $password = $value[2];
                } else {
                    $password = '';
                }
                $pasword = password_hash($password, PASSWORD_BCRYPT);
                $datauser = $user->findAll();
                foreach ($datauser as $data) {
                    if (isset($value[5]) && strtoupper($value[5]) == $data['u_nama']) {
                        $u_id = $data['u_id'];
                    }
                    // else {
                    //     session()->setFlashdata('error', 'Data Kolom Nama Referensi Masih ada yang tidak sesuai!');
                    //     // session()->setFlashdata('error', 'Data Kolom Upline Baris ' . $i . ' !');
                    //     return redirect('admin/databaseuser/registeruser');
                    // }
                    // $i++;
                }
                $provinsi = new ProvinsiModels();
                $dataprovinsi = $provinsi->findAll();
                $kabupaten = new KabupatenModels();
                $datakabupaten = $kabupaten->findAll();
                $kecamatan = new KecamatanModels();
                $datakecamatan = $kecamatan->findAll();
                foreach ($dataprovinsi as $data) {
                    if (isset($value[13]) && strtoupper($value[13]) == $data['nama_provinsi']) {
                        $id_provinsi = $data['id_provinsi'];
                    } else if (isset($value[13]) && strtoupper($value[13]) != $data['nama_provinsi']) {
                        $id_provinsi = null;
                    }
                }
                foreach ($datakabupaten as $data) {
                    if (isset($value[14]) && strtoupper($value[14]) == $data['nama_kabupaten']) {
                        $id_kabupaten = $data['id_kabupaten'];
                    } else if (isset($value[14]) && strtoupper($value[14]) != $data['nama_kabupaten']) {
                        $id_kabupaten = null;
                    }
                }
                foreach ($datakecamatan as $data) {
                    if (isset($value[16]) && strtoupper($value[16]) == $data['nama_kecamatan']) {
                        $id_kecamatan = $data['id_kecamatan'];
                    } else if (isset($value[16]) && strtoupper($value[16]) != $data['nama_kecamatan']) {
                        $id_kecamatan = null;
                    }
                }
                $data = [
                    'u_id' => $user->getInsertID(),
                    'u_username' => isset($value[1]) ? $value[1] : '',
                    'u_password' => $pasword,
                    'u_fullname' => isset($value[3]) ? strtoupper($value[3]) : '',
                    'u_role' => isset($value[4]) ? $value[4] : '',
                    'u_referensi' =>  $u_id,
                    'u_email' => isset($value[6]) ? $value[6] : '',
                    // 'u_create_at' => isset($value[7]) ? strtoupper($value[9]) : '',
                    'u_nik' => isset($value[8]) ? $value[8] : '',
                    'u_nama' => isset($value[9]) ? strtoupper($value[9]) : '',
                    'u_tempat_lahir' => isset($value[10]) ? strtoupper($value[10]) : '',
                    'u_tanggal_lahir' => isset($value[11]) ? $value[11] : '',
                    'u_jenis_kelamin' => isset($value[12]) ? $value[12] : '',
                    'u_provinsi' => $id_provinsi,
                    'u_kota' => $id_kabupaten,
                    'u_kelurahan' => isset($value[15]) ? strtoupper($value[15]) : '',
                    'u_kecamatan' => $id_kecamatan,
                    'u_kodepos' => isset($value[17]) ? $value[17] : '',

                ];
                $user->insert($data);
            }
            session()->setFlashdata('success', 'Data Berhasil Diimport!');
            return redirect('admin/databaseuser/datauser');
        } else {
            return redirect()->back()->with('message', 'Format File Tidak Sesuai! | Extension file harus .xls atau .xlsx');
        }
    }

    public function ExportDataExcelUser()
    {
        $user = new UsersModels();
        // $datauser = $user->findAll();
        $provinsi = new ProvinsiModels();
        $dataprovinsi = $provinsi->findAll();
        $kabupaten = new KabupatenModels();
        $datakabupaten = $kabupaten->findAll();
        $kecamatan = new KecamatanModels();
        $datakecamatan = $kecamatan->findAll();
        $spreadsheet = new Spreadsheet();
        $colomheader = $spreadsheet->getActiveSheet();
        $colomheader->setCellValue('A1', 'No');
        $colomheader->setCellValue('B1', 'Username');
        $colomheader->setCellValue('C1', 'Password');
        $colomheader->setCellValue('D1', 'Full Name');
        $colomheader->setCellValue('E1', 'Role Akses');
        $colomheader->setCellValue('F1', 'Nama Refernsi');
        $colomheader->setCellValue('G1', 'Email');
        $colomheader->setCellValue('H1', 'Data Dibuat');
        $colomheader->setCellValue('I1', 'NIK KTP');
        $colomheader->setCellValue('J1', 'Nama Lengkap KTP');
        $colomheader->setCellValue('K1', 'Tempat  Lahir');
        $colomheader->setCellValue('L1', 'Tanggal Lahir');
        $colomheader->setCellValue('M1', 'Jenis Kelamin');
        $colomheader->setCellValue('N1', 'Provinsi');
        $colomheader->setCellValue('O1', 'Kota');
        $colomheader->setCellValue('P1', 'Kelurahan');
        $colomheader->setCellValue('Q1', 'Kecamatan');
        $colomheader->setCellValue('R1', 'Kode Pos');
        $password = null;
        $colomdata = 2;
        // $startDate = $this->request->getGet('tanggal-awal');
        // $endDate = $this->request->getGet('tanggal-akhir');
        // $startDate = isset($_GET['tanggal-awal']) ? date('Y-m-d', strtotime($_GET['tanggal-awal'])) : null;
        // $endDate = isset($_GET['tanggal-akhir']) ? date('Y-m-d', strtotime($_GET['tanggal-akhir'])) : null;

        $startDate = $this->request->getVar('tanggal-awal') ? date('Y-m-d', strtotime($this->request->getVar('tanggal-awal'))) : null;
        $endDate = $this->request->getVar('tanggal-akhir') ? date('Y-m-d', strtotime($this->request->getVar('tanggal-akhir'))) : null;

        if ($startDate && $endDate) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $endDate = date('Y-m-d', strtotime($endDate));
            $datauser = $user->getDataByDateRange($startDate, $endDate);

            $datauser = $user->getDataByDateRange($startDate, $endDate);
            foreach ($datauser as $setuser) {
                $colomheader->setCellValue('A' . $colomdata, ($colomdata - 1));
                $colomheader->setCellValue('B' . $colomdata, $setuser['u_username']);
                $colomheader->setCellValue('C' . $colomdata, $password);
                $colomheader->setCellValue('D' . $colomdata, $setuser['u_fullname']);
                $colomheader->setCellValue('E' . $colomdata, $setuser['u_role']);
                $referensi = $setuser['u_referensi'];
                $id_provinsi = $setuser['u_provinsi'];
                $id_kabupaten = $setuser['u_kota'];
                $id_kecamatan = $setuser['u_kecamatan'];
                foreach ($datauser as $data) {
                    if ($referensi == $data['u_id']) {
                        $colomheader->setCellValue('F' . $colomdata, $data['u_nama']);
                    }
                }
                $colomheader->setCellValue('G' . $colomdata, $setuser['u_email']);
                $colomheader->setCellValue('H' . $colomdata, $setuser['u_create_at']);
                $colomheader->setCellValue('I' . $colomdata, $setuser['u_nik']);
                $colomheader->setCellValue('J' . $colomdata, $setuser['u_nama']);
                $colomheader->setCellValue('K' . $colomdata, $setuser['u_tempat_lahir']);
                $colomheader->setCellValue('L' . $colomdata, $setuser['u_tanggal_lahir']);
                $colomheader->setCellValue('M' . $colomdata, $setuser['u_jenis_kelamin']);
                foreach ($dataprovinsi as $data) {
                    if ($id_provinsi == $data['id_provinsi']) {
                        $colomheader->setCellValue('N' . $colomdata, $data['nama_provinsi']);
                    }
                }
                foreach ($datakabupaten as $data) {
                    if ($id_kabupaten == $data['id_kabupaten']) {
                        $colomheader->setCellValue('O' . $colomdata, $data['nama_kabupaten']);
                    }
                }
                foreach ($datakecamatan as $data) {
                    if ($id_kecamatan == $data['id_kecamatan']) {
                        $colomheader->setCellValue('Q' . $colomdata, $data['nama_kecamatan']);
                    }
                }
                $colomheader->setCellValue('P' . $colomdata, $setuser['u_kelurahan']);
                $colomheader->setCellValue('R' . $colomdata, $setuser['u_kodepos']);
                $colomdata++;
            }
        }
        $colomheader->getStyle('A1:R1')->getFont()->setBold(true);
        $colomheader->getStyle('A1:R1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $colomheader->getStyle('A1:R' . ($colomdata - 1))->applyFromArray($styleArray);

        // $colomheader->getColumnDimension('A')->setAutoSize(true);
        $colomheader->getColumnDimension('B')->setAutoSize(true);
        $colomheader->getColumnDimension('C')->setAutoSize(true);
        $colomheader->getColumnDimension('D')->setAutoSize(true);
        $colomheader->getColumnDimension('E')->setAutoSize(true);
        $colomheader->getColumnDimension('F')->setAutoSize(true);
        $colomheader->getColumnDimension('G')->setAutoSize(true);
        $colomheader->getColumnDimension('H')->setAutoSize(true);
        $colomheader->getColumnDimension('I')->setAutoSize(true);
        $colomheader->getColumnDimension('J')->setAutoSize(true);
        $colomheader->getColumnDimension('K')->setAutoSize(true);
        $colomheader->getColumnDimension('L')->setAutoSize(true);
        $colomheader->getColumnDimension('M')->setAutoSize(true);
        $colomheader->getColumnDimension('N')->setAutoSize(true);
        $colomheader->getColumnDimension('O')->setAutoSize(true);
        $colomheader->getColumnDimension('P')->setAutoSize(true);
        $colomheader->getColumnDimension('Q')->setAutoSize(true);
        $colomheader->getColumnDimension('R')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Export-Data-User_' . date('Y-m-d_H-i-s') . '.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
    public function ExportTemplateDataExcelUser()
    {
        $spreadsheet = new Spreadsheet();
        $colomheader = $spreadsheet->getActiveSheet();
        $colomheader->setCellValue('A1', 'No');
        $colomheader->setCellValue('B1', 'Username');
        $colomheader->setCellValue('C1', 'Password');
        $colomheader->setCellValue('D1', 'Full Name');
        $colomheader->setCellValue('E1', 'Role Akses');
        $colomheader->setCellValue('F1', 'Nama Refernsi');
        $colomheader->setCellValue('G1', 'Email');
        $colomheader->setCellValue('H1', 'NIK KTP');
        $colomheader->setCellValue('I1', 'Nama Lengkap KTP');
        $colomheader->setCellValue('J1', 'Tempat  Lahir');
        $colomheader->setCellValue('K1', 'Tanggal Lahir');
        $colomheader->setCellValue('L1', 'Jenis Kelamin');
        $colomheader->setCellValue('M1', 'Provinsi');
        $colomheader->setCellValue('N1', 'Kota');
        $colomheader->setCellValue('O1', 'Kelurahan');
        $colomheader->setCellValue('P1', 'Kecamatan');
        $colomheader->setCellValue('Q1', 'Kode Pos');
        $colomheader->getStyle('A1:Q1')->getFont()->setBold(true);
        $colomheader->getStyle('A1:Q1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $colomheader->getColumnDimension('A')->setAutoSize(true);
        $colomheader->getColumnDimension('B')->setAutoSize(true);
        $colomheader->getColumnDimension('C')->setAutoSize(true);
        $colomheader->getColumnDimension('D')->setAutoSize(true);
        $colomheader->getColumnDimension('E')->setAutoSize(true);
        $colomheader->getColumnDimension('F')->setAutoSize(true);
        $colomheader->getColumnDimension('G')->setAutoSize(true);
        $colomheader->getColumnDimension('H')->setAutoSize(true);
        $colomheader->getColumnDimension('I')->setAutoSize(true);
        $colomheader->getColumnDimension('J')->setAutoSize(true);
        $colomheader->getColumnDimension('K')->setAutoSize(true);
        $colomheader->getColumnDimension('L')->setAutoSize(true);
        $colomheader->getColumnDimension('M')->setAutoSize(true);
        $colomheader->getColumnDimension('N')->setAutoSize(true);
        $colomheader->getColumnDimension('O')->setAutoSize(true);
        $colomheader->getColumnDimension('P')->setAutoSize(true);
        $colomheader->getColumnDimension('Q')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Tempalte-Export-Data-User_' . date('Y-m-d_H-i-s') . '.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
    public function ExportDataExcelUsertest()
    {
        $startDate = $this->request->getVar('tanggal-awal');
        $endDate = $this->request->getVar('tanggal-akhir');
        print_r('Tanggal awal ' . $startDate . ' Tanggal Akhir ' . $endDate);
    }

    //Data Item Barang
    public function itembarang()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $itembarang = new DataItemBarangModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => 'databarangsupplier',
            'MenuDataBarang' => 'menudatabarang',
            'DataPackingBarang' => '',
            'MenuDataTransaksi' => '',
            'DataPeriodeTransaksi' => '',
            'DataKategoriPaket' => '',
            'DataPaketBarang' => '',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),


        ];
        return view('admin/DatabaseBarang/DataItemBarang/itembarang', $data);
        // return view('register');
    }
    public function itembarangprocess()
    {
        if (!$this->validate([
            'ib_nama' => 'required',
            'ib_harga' => 'required',
            // 'ib_qty_stok' => 'required',
            'ib_berat/ukuran' => 'required',
            'ib_ktrg_berat/ukuran' => 'required',
            //     'rules' => 'uploaded[sb_foto]|max_size[sb_foto,1024]|mime_in[sb_foto,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
            //     'errors' => [
            //         'uploaded' => '{field} Wajib diisi!',
            //         'max_size' => 'Ukuran {field} Maksimal 1024 KB ',
            //         'mime_in' => 'Format {field} harus JPG/JPEG/PNG!',
            //     ]
            // ],
        ])) {
            session()->setFlashdata('error', $this->validator->listErrors());
            return redirect()->back()->withInput();
        }
        $nama = strtoupper($this->request->getVar('ib_nama'));
        $ib_harga = floatval(str_replace(",", "", $this->request->getVar('ib_harga')));
        // $ib_qty_stok = floatval(str_replace(",", "", $this->request->getVar('ib_qty_stok')));
        $ib_qty_beli = floatval(str_replace(",", "", $this->request->getVar('ib_qty_beli')));
        $ib_beratukuran = floatval(str_replace(",", "", $this->request->getVar('ib_berat/ukuran')));
        // Validasi nilai variabel
        if (!is_numeric($ib_harga)  || !is_numeric($ib_qty_beli) || !is_numeric($ib_beratukuran)) {
            echo "Input tidak valid!";
            exit;
        }
        $itembarang = new DataItemBarangModels();
        $itembarang->insert([
            'ib_nama' => $nama,
            'ib_harga' => $ib_harga,
            // 'ib_qty_stok' => $ib_qty_stok,
            'ib_qty_beli' => $ib_qty_beli,
            'ib_berat/ukuran' => $ib_beratukuran,
            'ib_ktrg_berat/ukuran' => $this->request->getVar('ib_ktrg_berat/ukuran'),
            // 'sb_foto' => $nama_file,
            'u_id' => session()->get('u_id')
        ]);
        session()->setFlashdata('success', 'Data Berhasil Disimpan!');
        return redirect()->to('/admin/databasebarang/itembarang');
    }
    public function listdataitembarang()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $itembarang = new DataItemBarangModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => 'databarangsupplier',
            'MenuDataBarang' => 'menudatabarang',
            'DataPackingBarang' => '',
            'MenuDataTransaksi' => '',
            'DataPeriodeTransaksi' => '',
            'DataKategoriPaket' => '',
            'DataPaketBarang' => '',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        $data['tb_item_barang'] = $itembarang->findAll();
        echo view('admin/DatabaseBarang/DataItemBarang/dataitembarang', $data);
        //berdasarkan login
        // $user = new UsersModels();
        // $data['tb_user'] = $user->where('u_referensi', session('u_id'))->findAll();
        // echo view('admin/datauser', $data);
    }
    public function edititembarang($ib_id)
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $itembarang = new DataItemBarangModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => 'databarangsupplier',
            'MenuDataBarang' => 'menudatabarang',
            'DataPackingBarang' => '',
            'DataPaketBarang' => '',
            'DataKategoriPaket' => '',
            'MenuDataTransaksi' => '',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        // ambil artikel yang akan diedit

        $data['tb_item_barang'] = $itembarang->where('ib_id', $ib_id)->first();

        if ($this->validate([
            'ib_nama' => 'required',
            'ib_harga' => 'required',
            // 'ib_qty_stok' => 'required',
            'ib_berat/ukuran' => 'required',
            'ib_ktrg_berat/ukuran' => 'required',
        ])) {
            $nama = strtoupper($this->request->getVar('ib_nama'));
            $ib_harga = floatval(str_replace(",", "", $this->request->getVar('ib_harga')));
            // $ib_qty_stok = floatval(str_replace(",", "", $this->request->getVar('ib_qty_stok')));
            $ib_qty_beli = floatval(str_replace(",", "", $this->request->getVar('ib_qty_beli')));
            $ib_beratukuran = floatval(str_replace(",", "", $this->request->getVar('ib_berat/ukuran')));
            // Validasi nilai variabel
            if (!is_numeric($ib_harga) || !is_numeric($ib_qty_beli) || !is_numeric($ib_beratukuran)) {
                echo "Input tidak valid!";
                exit;
            }
            $itembarang->update($ib_id, [
                'ib_nama' => $nama,
                'ib_harga' => $ib_harga,
                // 'ib_qty_stok' => $ib_qty_stok,
                'ib_qty_beli' => $ib_qty_beli,
                'ib_berat/ukuran' => $ib_beratukuran,
                'ib_ktrg_berat/ukuran' => $this->request->getVar('ib_ktrg_berat/ukuran'),
                'u_id' => session()->get('u_id')
            ]);
            session()->setFlashdata('success', 'Data Berhasil Di Edit!');
            return redirect('admin/databasebarang/dataitembarang');
        } else {
            // session()->setFlashdata('error', $this->validator->listErrors());
        }
        echo view('admin/DatabaseBarang/DataItemBarang/itembarangedit', $data);
    }
    public function deleteitembarang($ib_id)
    {
        $itembarang = new DataItemBarangModels();
        $itembarang->delete($ib_id);
        session()->setFlashdata('success', 'Data Berhasil Di Hapus!');
        return redirect('admin/databasebarang/dataitembarang');
    }
    public function ImportFileExcelitembarang()
    {
        $itembarang = new DataItemBarangModels();
        $file = $this->request->getFile('file');
        $extension = $file->getClientExtension();
        if ($extension == 'xlsx' || $extension == 'xls') {
            if ($extension == 'xls') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            $spreadsheet = $reader->load($file);
            $kategori = $spreadsheet->getActiveSheet()->toArray();
            foreach ($kategori as $key => $value) {
                if ($key == 0) {
                    continue;
                }

                $data = [
                    'ib_id' => $itembarang->getInsertID(),
                    'ib_nama' => strtoupper($value[1]),
                    'ib_harga' => $value[2],
                    'ib_berat/ukuran' => $value[3],
                    'ib_ktrg_berat/ukuran' => $value[4],
                    'u_id' => session()->get('u_id'),

                ];
                $itembarang->insert($data);
            }
            session()->setFlashdata('success', 'Data Berhasil Diimport!');
            return redirect('admin/databasebarang/dataitembarang');
        } else {
            return redirect()->back()->with('message', 'Format File Tidak Sesuai! | Extension file harus .xls atau .xlsx');
        }
    }
    public function ExportDataExcelitembarang()
    {
        $itembarang = new DataItemBarangModels();
        $dataitembarang = $itembarang->findAll();
        $spreadsheet = new Spreadsheet();
        $colomheader = $spreadsheet->getActiveSheet();
        $colomheader->setCellValue('A1', 'No');
        $colomheader->setCellValue('B1', 'Nama Item Barang');
        $colomheader->setCellValue('C1', 'Harga Item Barang');
        $colomheader->setCellValue('D1', 'Berat Item Barang');
        $colomheader->setCellValue('E1', 'Ukuran Item Barang');
        // $colomheader->setCellValue('E1', 'Harga Jual');
        // $colomheader->setCellValue('F1', 'Jumlah Barang (qty)');
        // $colomheader->setCellValue('G1', 'Berat/Ukuran Barang');
        // $colomheader->setCellValue('H1', 'Keterangan Berat/Ukuran Barang');
        $colomdata = 2;
        foreach ($dataitembarang as $setitembarang) {
            $colomheader->setCellValue('A' . $colomdata, ($colomdata - 1));
            $colomheader->setCellValue('B' . $colomdata, $setitembarang['ib_nama']);
            $colomheader->setCellValue('C' . $colomdata, $setitembarang['ib_harga']);
            $colomheader->setCellValue('D' . $colomdata, $setitembarang['ib_berat/ukuran']);
            $colomheader->setCellValue('E' . $colomdata, $setitembarang['ib_ktrg_berat/ukuran']);
            $colomdata++;
        }
        $colomheader->getStyle('A1:E1')->getFont()->setBold(true);
        $colomheader->getStyle('A1:E1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $colomheader->getStyle('A1:E' . ($colomdata - 1))->applyFromArray($styleArray);

        $colomheader->getColumnDimension('A')->setAutoSize(true);
        $colomheader->getColumnDimension('B')->setAutoSize(true);
        $colomheader->getColumnDimension('C')->setAutoSize(true);
        $colomheader->getColumnDimension('D')->setAutoSize(true);
        $colomheader->getColumnDimension('E')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Export-Database-ItemBarang.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
    public function ExportTemplateDataExcelitembarang()
    {
        $spreadsheet = new Spreadsheet();
        $colomheader = $spreadsheet->getActiveSheet();
        $colomheader->setCellValue('A1', 'No');
        $colomheader->setCellValue('B1', 'Nama Item Barang');
        $colomheader->setCellValue('C1', 'Harga Item Barang');
        $colomheader->setCellValue('D1', 'Berat Item Barang');
        $colomheader->setCellValue('E1', 'Ukuran Item Barang');
        $colomheader->getStyle('A1:E1')->getFont()->setBold(true);
        $colomheader->getStyle('A1:E1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $colomheader->getColumnDimension('A')->setAutoSize(true);
        $colomheader->getColumnDimension('B')->setAutoSize(true);
        $colomheader->getColumnDimension('C')->setAutoSize(true);
        $colomheader->getColumnDimension('D')->setAutoSize(true);
        $colomheader->getColumnDimension('E')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Template-Export-Database-ItemBarang.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
    //Data Packing Barang
    public function packingbarang()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $packingbarang = new PackingBarangModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => 'menudatabarang',
            'DataPackingBarang' => 'datapackingbarang',
            'MenuDataTransaksi' => '',
            'DataKategoriPaket' => '',
            'DataPeriodeTransaksi' => '',
            'DataPaketBarang' => '',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        return view('admin/DatabaseBarang/PackagingBarang/packingbarang', $data);
        // return view('register');
    }
    public function packingbarangprocess()
    {
        if (!$this->validate([
            'pa_nama' => 'required',
            'pa_harga' => 'required',
            'pa_foto' => [
                'rules' => 'uploaded[pa_foto]|max_size[pa_foto,1024]|mime_in[pa_foto,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                'errors' => [
                    'uploaded' => '{field} Wajib diisi!',
                    'max_size' => 'Ukuran {field} Maksimal 1024 KB ',
                    'mime_in' => 'Format {field} harus JPG/JPEG/PNG!',
                ]
            ],
        ])) {
            session()->setFlashdata('error', $this->validator->listErrors());
            return redirect()->back()->withInput();
        }
        $pa_harga = floatval(str_replace(",", "", $this->request->getVar('pa_harga')));
        // Validasi nilai variabel
        if (!is_numeric($pa_harga)) {
            echo "Input tidak valid!";
            exit;
        }
        $nama = strtoupper($this->request->getVar('pa_nama'));
        $foto = $this->request->getFile('pa_foto');
        $nama_file = $foto->getRandomName();
        $packingbarang = new PackingBarangModels();
        $packingbarang->insert([
            'pa_nama' => $nama,
            'pa_harga' => $pa_harga,
            'pa_foto' => $nama_file,
            'u_id' => session()->get('u_id')
        ]);
        $foto->move('foto-packaging', $nama_file);
        session()->setFlashdata('success', 'Data Berhasil Disimpan!');
        return redirect()->to('/admin/databasebarang/packagingbarang');
    }
    public function listdatapackingbarang()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $packingbarang = new PackingBarangModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'DataPackingBarang' => 'datapackingbarang',
            'MenuDataBarang' => 'menudatabarang',
            'MenuDataTransaksi' => '',
            'DataPeriodeTransaksi' => '',
            'DataKategoriPaket' => '',
            'DataPaketBarang' => '',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),
        ];
        $data['tb_packaging'] = $packingbarang->findAll();
        echo view('admin/DatabaseBarang/PackagingBarang/datapackingbarang', $data);
        //berdasarkan login
        // $user = new UsersModels();
        // $data['tb_user'] = $user->where('u_referensi', session('u_id'))->findAll();
        // echo view('admin/datauser', $data);
    }
    public function editpackingbarang($pa_id)
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $packingbarang = new PackingBarangModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'DataTransaksi' => '',
            'MenuDataBarang' => 'menudatabarang',
            'DataPackingBarang' => 'datapackingbarang',
            'MenuDataTransaksi' => '',
            'DataPeriodeTransaksi' => '',
            'DataKategoriPaket' => '',
            'DataTransaksiCicilan' => '',
            'DataPaketBarang' => '',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        // ambil artikel yang akan diedit

        $data['tb_packaging'] = $packingbarang->where('pa_id', $pa_id)->first();
        if ($this->validate([
            'pa_nama' => 'required',
            'pa_harga' => 'required',
            'pa_foto' => [
                'rules' => 'max_size[pa_foto,1024]|mime_in[pa_foto,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                'errors' => [
                    'uploaded' => '{field} Wajib diisi!',
                    'max_size' => 'Ukuran {field} Maksimal 1024 KB ',
                    'mime_in' => 'Format {field} harus JPG/JPEG/PNG!',
                ]
            ],
        ])) {
            $notnull = $this->request->getVar('pa_harga');
            if ($notnull != null) {
                $pa_harga = floatval(str_replace(",", "", $this->request->getVar('pa_harga')));
                // Validasi nilai variabel
                if (!is_numeric($pa_harga)) {
                    echo "Input tidak valid!";
                    exit;
                }
            }
            $foto = $this->request->getFile('pa_foto');
            $prefoto = $this->request->getVar('preview');
            if ($foto->getError() == 4) {
                $nama_file = $prefoto;
            } else {
                $nama_file = $foto->getRandomName();
                if ($prefoto != '') {
                    unlink('foto-packaging/' . $prefoto);
                }
                $foto->move('foto-packaging', $nama_file);
            }
            // jika data vlid, maka simpan ke database
            $nama = strtoupper($this->request->getVar('pa_nama'));
            $packingbarang->update($pa_id, [
                'pa_nama' => $nama,
                'pa_harga' => $pa_harga,
                'pa_foto' => $nama_file,
                'u_id' => session()->get('u_id')
            ]);
            session()->setFlashdata('success', 'Data Berhasil Di Edit!');
            return redirect('admin/databasebarang/datapackagingbarang');
        } else {
            // session()->setFlashdata('error', $this->validator->listErrors());
        }
        echo view('admin/DatabaseBarang/PackagingBarang/packingbarangedit', $data);
    }
    public function deletepackingbarang($pa_id)
    {
        $packingbarang = new PackingBarangModels();
        $packingbarangfoto = $packingbarang->datapackaging($pa_id);
        if ($packingbarangfoto['pa_foto'] == '') {
        } else {
            unlink('foto-packaging/' . $packingbarangfoto['pa_foto']);
        }
        $packingbarang->delete($pa_id);
        session()->setFlashdata('success', 'Data Berhasil Di Hapus!');
        return redirect('admin/databasebarang/datapackagingbarang');
    }
    public function ImportFileExcelpackingbarang()
    {
        $packingbarang = new PackingBarangModels();
        $file = $this->request->getFile('file');
        $extension = $file->getClientExtension();
        if ($extension == 'xlsx' || $extension == 'xls') {
            if ($extension == 'xls') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            $spreadsheet = $reader->load($file);
            $kategori = $spreadsheet->getActiveSheet()->toArray();
            foreach ($kategori as $key => $value) {
                if ($key == 0) {
                    continue;
                }

                $data = [
                    'pa_id ' => $packingbarang->getInsertID(),
                    'pa_nama' => strtoupper($value[1]),
                    'pa_harga' => $value[2],
                    'u_id' => session()->get('u_id'),

                ];
                $packingbarang->insert($data);
            }
            session()->setFlashdata('success', 'Data Berhasil Diimport!');
            return redirect('admin/databasebarang/datapackagingbarang');
        } else {
            return redirect()->back()->with('message', 'Format File Tidak Sesuai! | Extension file harus .xls atau .xlsx');
        }
    }
    public function ExportDataExcelpackingbarang()
    {
        $packingbarang = new PackingBarangModels();
        $datapackingbarang = $packingbarang->findAll();
        $spreadsheet = new Spreadsheet();
        $colomheader = $spreadsheet->getActiveSheet();
        $colomheader->setCellValue('A1', 'No');
        $colomheader->setCellValue('B1', 'Nama Packaging Barang');
        $colomheader->setCellValue('C1', 'Harga Packaging Barang');

        $colomdata = 2;
        foreach ($datapackingbarang as $setpackingbarang) {
            $colomheader->setCellValue('A' . $colomdata, ($colomdata - 1));
            $colomheader->setCellValue('B' . $colomdata, $setpackingbarang['pa_nama']);
            $colomheader->setCellValue('C' . $colomdata, $setpackingbarang['pa_harga']);
            $colomdata++;
        }
        $colomheader->getStyle('A1:C1')->getFont()->setBold(true);
        $colomheader->getStyle('A1:C1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $colomheader->getStyle('A1:C' . ($colomdata - 1))->applyFromArray($styleArray);

        $colomheader->getColumnDimension('A')->setAutoSize(true);
        $colomheader->getColumnDimension('B')->setAutoSize(true);
        $colomheader->getColumnDimension('C')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Export-Data-PackingBarang.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
    public function ExportTemplateDataExcelpackingbarang()
    {
        $spreadsheet = new Spreadsheet();
        $colomheader = $spreadsheet->getActiveSheet();
        $colomheader->setCellValue('A1', 'No');
        $colomheader->setCellValue('B1', 'Nama Packaging Barang');
        $colomheader->setCellValue('C1', 'Harga Packaging Barang');
        $colomheader->getStyle('A1:C1')->getFont()->setBold(true);
        $colomheader->getStyle('A1:C1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $colomheader->getColumnDimension('A')->setAutoSize(true);
        $colomheader->getColumnDimension('B')->setAutoSize(true);
        $colomheader->getColumnDimension('C')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Template-Export-Data-PackingBarang.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
    //Data Paket Barang
    public function ShowItemBarang()
    {

        $paketbarang = new PaketBarangModels();
        $dataitembarang = $paketbarang->dataitembarang();

        echo '<div class="form-group">
            <label>Nama Item Barang</label>
            <div class="input-group-text">
                <select class="js-example-basic-multiple" name="ib_id[]" id="" required>';

        foreach ($dataitembarang as $tb_item_barang) {
            echo '<option value="' . $tb_item_barang['ib_id'] . '">' . $tb_item_barang['ib_nama'] . '</option>';
        }

        echo       '</select>
                <div class="input-group-prepend">
                    <input type="text" name="qty" class="form-control insentive" placeholder="qty" required>
                    <select class="form-control selectric" name="ib_ktrg_berat_ukuran[]" id="">
                        <option value="gram">gram</option>
                        <option value="kg">kg</option>
                        <option value="liter">liter</option>
                        <option value="ml">ml</option>
                        <option value="botol">botol</option>
                        <option value="pcs">pcs</option>
                        <option value="unit">unit</option>
                        <option value="kardus">kardus</option>
                    </select>
                </div>
                <div class="input-group-text">
                    <a href="#tambahitembarang" class="btn btn-primary btn-sm update-record"><i class="fas fa-plus"></i></a>
                </div>
            </div>
        </div>';
    }

    public function paketbarang()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $paketbarang = new PaketBarangModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => 'menudatabarang',
            'DataPackingBarang' => '',
            'MenuDataTransaksi' => '',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => '',
            'DataKategoriPaket' => '',
            'DataTransaksiCicilan' => '',
            'DataPaketBarang' => 'datapaketbarang',
            'packagingbarang' => $paketbarang->datapackagingbarang(),
            'payperiode' => $paketbarang->datapayperiode(),
            'itembarang' => $paketbarang->dataitembarang(),
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        return view('admin/DatabaseBarang/PaketBarang/paketbarang', $data);
        // return view('register');
    }
    public function paketbarangprocess()
    {
        if (!$this->validate([
            'p_nama' => 'required',
            // 'pe_id' => 'required',
            'p_hargaJual' => 'required',
            'p_hargaBarang' => 'required',
            'pa_id' => 'required',
            'p_cashback' => 'required',
            'p_foto' => [
                'rules' => 'uploaded[p_foto]|max_size[p_foto,1024]|mime_in[p_foto,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                'errors' => [
                    'uploaded' => '{field} Wajib diisi!',
                    'max_size' => 'Ukuran {field} Maksimal 1024 KB ',
                    'mime_in' => 'Format {field} harus JPG/JPEG/PNG!',
                ]
            ],
        ])) {
            session()->setFlashdata('error', $this->validator->listErrors());
            return redirect()->back()->withInput();
        }
        $paketbarang = new PaketBarangModels();
        $pe_id = $this->request->getVar('pe_id');
        $dataperiode = $paketbarang->datapayperiode();
        foreach ($dataperiode as $key => $value) {
            if ($pe_id == $value['pe_id']) {
                $pe_periode = $value['pe_periode'];
            }
        }
        $p_hargajual = floatval(str_replace(",", "", $this->request->getVar('p_hargaJual')));
        $p_hargabarang = floatval(str_replace(",", "", $this->request->getVar('p_hargaBarang')));
        $p_cashback = floatval(str_replace(",", "", $this->request->getVar('p_cashback')));
        // Validasi nilai variabel
        if (!is_numeric($p_hargajual) || !is_numeric($p_hargabarang) || !is_numeric($p_cashback)) {
            echo "Input tidak valid!";
            exit;
        }
        $insentip = ($p_hargajual - $p_cashback - $p_hargabarang) * 0.1;
        $labakotor = $p_hargajual - ($p_hargabarang + $p_cashback  + $insentip);
        $presentaseLB  = ($labakotor / $p_hargajual) * 100;
        $p_setoran = $p_hargajual / $pe_periode;
        // if (!is_array($itembarang)) {
        //     $itembarang = explode(',', $itembarang);
        // }
        // $itembarangCount = count($itembarang);

        // $qty = $this->request->getVar('qty');
        // if (!is_array($qty)) {
        //     $qty = explode(',', $qty);
        // }
        // $qtyCount = count($qty);

        // $ukuran = $this->request->getVar('ib_ktrg_berat/ukuran');
        // if (!is_array($ukuran)) {
        //     $ukuran = explode(',', $ukuran);
        // }
        // $ukuranCount = count($ukuran);

        // for ($i = 0; $i < $itembarangCount; $i++) {
        //     $databarang = $itembarang[$i];
        //     print_r($databarang . $qty[$i] . $ukuran[$i]);
        // }
        $foto = $this->request->getFile('p_foto');
        $nama_file = $foto->getRandomName();
        $nama = strtoupper($this->request->getVar('p_nama'));
        $paketbarang->insert([
            'p_nama' => $nama,
            'pe_id' => $pe_id,
            'p_hargaJual' => $p_hargajual,
            'p_hargaBarang' => $p_hargabarang,
            'pa_id' => $this->request->getVar('pa_id'),
            'p_cashback' => $p_cashback,
            'p_insentive' => $insentip,
            'p_laba' => $labakotor,
            'p_persentaseLaba' => $presentaseLB,
            'p_setoran' => $p_setoran,
            'p_foto' => $nama_file,
            'u_id' => session()->get('u_id')
        ]);
        $foto->move('foto-paket', $nama_file);
        $pp_barang = new PengambilanPaketBarangModels();
        $pp_barang->insert([
            'pp_p_id' => $paketbarang->getInsertID(),
            'pp_ib_id' => $this->request->getVar('pp_ib_id'),
            'pp_qty' => $this->request->getVar('pp_qty'),
            'pp_ktrg_berat_ukuran' => $this->request->getVar('pp_ktrg_berat_ukuran')
        ]);
        // $namabarang = count($this->request->getVar('p_barang'));
        // for ($i = 0; $i < $namabarang; $i++) {
        //     $databarang = $this->request->getVar('p_barang[' . $i . ']');
        //     $pp_barang->insert([
        //         'pp_p_id' => $paketbarang->getInsertID(),
        //         'p_sb_id' => $databarang,
        //         'p_pa_id' => $this->request->getVar('pa_id')
        //     ]);
        // }

        session()->setFlashdata('success', 'Data Berhasil Disimpan!');
        return redirect()->to('/admin/databasebarang/paketbarang');
        // echo view('admin/test');
    }
    public function listdatapaketbarang()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $paketbarang = new PaketBarangModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => 'menudatabarang',
            'DataPackingBarang' => '',
            'DataTransaksiCicilan' => '',
            'MenuDataTransaksi' => '',
            'DataPeriodeTransaksi' => '',
            'DataKategoriPaket' => '',
            'DataTransaksi' => '',
            'DataPaketBarang' => 'datapaketbarang',
            'packagingbarang' => $paketbarang->datapackagingbarang(),
            'payperiode' => $paketbarang->datapayperiode(),
            'itembarang' => $paketbarang->dataitembarang(),
            'pengambilanpaket' => $paketbarang->datapengambilanpaket(),
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        $data['tb_paket'] = $paketbarang->findAll();
        echo view('admin/DatabaseBarang/PaketBarang/datapaketbarang', $data);
        //berdasarkan login
        // $user = new UsersModels();
        // $data['tb_user'] = $user->where('u_referensi', session('u_id'))->findAll();
        // echo view('admin/datauser', $data);
    }
    public function editpaketbarang($p_id)
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $paketbarang = new PaketBarangModels();
        $pp_barang = new PengambilanPaketBarangModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => 'menudatabarang',
            'DataPackingBarang' => '',
            'MenuDataTransaksi' => '',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => '',
            'DataKategoriPaket' => '',
            'DataTransaksiCicilan' => '',
            'DataPaketBarang' => 'datapaketbarang',
            'packagingbarang' => $paketbarang->datapackagingbarang(),
            'payperiode' => $paketbarang->datapayperiode(),
            'itembarang' => $paketbarang->dataitembarang(),
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        // ambil artikel yang akan diedit

        $data['tb_paket'] = $paketbarang->where('p_id', $p_id)->first();
        // $data['pengambilan_paket'] = $paketbarang->get_pengambilan_paket_by_id($p_id);

        // lakukan validasi data artikel
        $validation = \Config\Services::validation();
        $validation->setRules([
            'p_nama' => 'required',
            'pe_id' => 'required',
            'p_hargaJual' => 'required',
            'p_hargaBarang' => 'required',
            'pa_id' => 'required',
            'p_cashback' => 'required',
        ]);
        $isDataValid = $validation->withRequest($this->request)->run();
        // jika data vlid, maka simpan ke database
        if ($isDataValid) {
            $paketbarang = new PaketBarangModels();
            $pe_id = $this->request->getVar('pe_id');
            $dataperiode = $paketbarang->datapayperiode();
            foreach ($dataperiode as $key => $value) {
                if ($pe_id == $value['pe_id']) {
                    $pe_periode = $value['pe_periode'];
                }
            }
            $notnull1 = $this->request->getVar('p_hargaJual');
            $notnull2 = $this->request->getVar('p_hargaBarang');
            $notnull4 = $this->request->getVar('p_cashback');
            if ($notnull1 != null && $notnull2 != null  && $notnull4 != null) {
                $p_hargajual = floatval(str_replace(",", "", $this->request->getVar('p_hargaJual')));
                $p_hargabarang = floatval(str_replace(",", "", $this->request->getVar('p_hargaBarang')));
                $p_cashback = floatval(str_replace(",", "", $this->request->getVar('p_cashback')));
                // Validasi nilai variabel
                if (!is_numeric($p_hargajual) || !is_numeric($p_hargabarang) || !is_numeric($p_cashback)) {
                    echo "Input tidak valid!";
                    exit;
                }
                $insentip = ($p_hargajual - $p_cashback - $p_hargabarang) * 0.1;
                $labakotor = $p_hargajual - ($p_hargabarang + $p_cashback + $insentip);
                $presentaseLB  = ($labakotor / $p_hargajual) * 100;
                $p_setoran = $p_hargajual / $pe_periode;
                $foto = $this->request->getFile('p_foto');
                $prefoto = $this->request->getVar('preview');
                if ($foto->getError() == 4) {
                    $nama_file = $prefoto;
                } else {
                    $nama_file = $foto->getRandomName();
                    if ($prefoto != '') {
                        unlink('foto-paket/' . $prefoto);
                    }
                    $foto->move('foto-paket', $nama_file);
                }
                $nama = strtoupper($this->request->getVar('p_nama'));
                $paketbarang->update($p_id, [
                    'p_nama' => $nama,
                    'pe_id' => $this->request->getVar('pe_id'),
                    'p_hargaJual' => $p_hargajual,
                    'p_hargaBarang' => $p_hargabarang,
                    'pa_id' => $this->request->getVar('pa_id'),
                    'p_cashback' => $p_cashback,
                    'p_insentive' => $insentip,
                    'p_laba' => $labakotor,
                    'p_persentaseLaba' => $presentaseLB,
                    'p_setoran' => $p_setoran,
                    'p_foto' => $nama_file,
                    'u_id' => session()->get('u_id')
                ]);
                // $pp_barang = new PengambilanPaketBarangModels();
                // $pp_barang->update([
                //     'pp_p_id' => $paketbarang->getInsertID(),
                //     'pp_ib_id' => $this->request->getVar('pp_ib_id'),
                //     'pp_qty' => $this->request->getVar('pp_qty'),
                //     'pp_ktrg_berat_ukuran' => $this->request->getVar('pp_ktrg_berat_ukuran')
                // ]);
            }
            session()->setFlashdata('success', 'Data Berhasil Di Edit!');
            return redirect('admin/databasebarang/datapaketbarang');
        }
        echo view('admin/DatabaseBarang/PaketBarang/paketbarangedit', $data);
    }
    public function deletepaketbarang($p_id)
    {
        $paketbarang = new PaketBarangModels();
        $pengambilanpaketbarang = new PengambilanPaketBarangModels();
        $pengambilanpaketbarang->deletes($p_id);
        $paketbarangbyid = $paketbarang->datapaketbarangbyid($p_id);
        if ($paketbarangbyid['p_foto'] == '') {
        } else {
            unlink('foto-paket/' . $paketbarangbyid['p_foto']);
        }
        $paketbarang->delete($p_id);
        session()->setFlashdata('success', 'Data Berhasil Di Hapus!');
        return redirect('admin/databasebarang/datapaketbarang');
    }
    public function ImportFileExcelpaketbarang()
    {
        $paketbarang = new PaketBarangModels();
        $file = $this->request->getFile('file');
        $extension = $file->getClientExtension();
        if ($extension == 'xlsx' || $extension == 'xls') {
            if ($extension == 'xls') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            $spreadsheet = $reader->load($file);
            $kategori = $spreadsheet->getActiveSheet()->toArray();
            foreach ($kategori as $key => $value) {
                if ($key == 0) {
                    continue;
                }
                $barang = new BarangModels();
                $databarang = $barang->findAll();
                foreach ($databarang as $setbarang) {
                    if ($value[2] == $setbarang['b_nama']) {
                        $brg = $setbarang['b_id'];
                    }
                }

                $data = [
                    'sb_id' => $value[0],
                    // 's_id' => $sp,
                    'b_id' => $brg,
                    'u_id' => session()->get('u_id'),

                ];
                $paketbarang->insert($data);
            }
            session()->setFlashdata('success', 'Data Berhasil Diimport!');
            return redirect('admin/databarang/datapaketbarang');
        } else {
            return redirect()->back()->with('message', 'Format File Tidak Sesuai! | Extension file harus .xls atau .xlsx');
        }
    }
    public function ExportDataExcelpaketbarang()
    {
        $paketbarang = new PaketBarangModels();
        $datapaketbarang = $paketbarang->findAll();
        $spreadsheet = new Spreadsheet();
        $colomheader = $spreadsheet->getActiveSheet();
        $colomheader->setCellValue('A1', 'No');
        $colomheader->setCellValue('B1', 'Nama Paket Barang');
        $colomheader->setCellValue('C1', 'Nama Periode Pembayaran');
        $colomheader->setCellValue('C1', 'Jumlah Periode Pembayaran');
        $colomheader->setCellValue('C1', 'Harga Asli Paket Barang');
        $colomheader->setCellValue('C1', 'Harga Jual Paket Barang');
        $colomheader->setCellValue('C1', 'Harga Setoran Paket Barang');
        $colomheader->setCellValue('C1', 'Nama Packaging Barang');
        $colomheader->setCellValue('C1', 'Harga Packaging Barang');
        $colomheader->setCellValue('C1', 'Cashback Paket Barang');
        $colomheader->setCellValue('C1', 'Insentive Paket Barang');
        $colomheader->setCellValue('C1', 'Laba Paket Barang');
        $colomheader->setCellValue('C1', 'Persentase Laba Paket Barang');
        $colomheader->setCellValue('C1', 'Nama Item Barang');

        $barang = new BarangModels();
        $databarang = $barang->findAll();
        $colomdata = 2;
        foreach ($datapaketbarang as $setpaketbarang) {
            $colomheader->setCellValue('A' . $colomdata, ($colomdata - 1));
            foreach ($databarang as $setbarang) {
                if ($setpaketbarang['b_id'] == $setbarang['b_id']) {
                    $colomheader->setCellValue('C' . $colomdata, $setbarang['b_nama']);
                }
            }
            $colomdata++;
        }
        $colomheader->getStyle('A1:C1')->getFont()->setBold(true);
        $colomheader->getStyle('A1:C1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $colomheader->getStyle('A1:C' . ($colomdata - 1))->applyFromArray($styleArray);

        $colomheader->getColumnDimension('A')->setAutoSize(true);
        $colomheader->getColumnDimension('B')->setAutoSize(true);
        $colomheader->getColumnDimension('C')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Export-Data-PaketBarang.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }



    //Data Periode Pembayaran
    public function periodepembayaran()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $periodepembayaran = new PeriodePembayaranModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataKategoriPaket' => '',
            'DataPackingBarang' => '',
            'DataPaketBarang' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => 'dataperiodetransaksi',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        return view('admin/DataTransaksi/PeriodePembayaran/periodepembayaran', $data);
        // return view('register');
    }
    public function periodepembayaranprocess()
    {
        if (!$this->validate([
            'pe_nama' => 'required',
            'pe_periode' => 'required',
        ])) {
            session()->setFlashdata('error', $this->validator->listErrors());
            return redirect()->back()->withInput();
        }
        $periodepembayaran = new PeriodePembayaranModels();
        $periodepembayaran->insert([
            'pe_nama' => strtoupper($this->request->getVar('pe_nama')),
            'pe_periode' => $this->request->getVar('pe_periode'),
            'u_id' => session()->get('u_id')
        ]);
        session()->setFlashdata('success', 'Data Berhasil Disimpan!');
        return redirect()->to('/admin/datatransaksi/periodepembayaran');
    }
    public function listdataperiodepembayaran()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $periodepembayaran = new PeriodePembayaranModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'DataPackingBarang' => '',
            'MenuDataBarang' => '',
            'DataPaketBarang' => '',
            'DataKategoriPaket' => '',
            'DataTransaksiCicilan' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => 'dataperiodetransaksi',
            'DataTransaksi' => '',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        $data['tb_pay_periode'] = $periodepembayaran->findAll();
        echo view('admin/DataTransaksi/PeriodePembayaran/dataperiodepembayaran', $data);
        //berdasarkan login
        // $user = new UsersModels();
        // $data['tb_user'] = $user->where('u_referensi', session('u_id'))->findAll();
        // echo view('admin/datauser', $data);
    }
    public function editperiodepembayaran($pe_id)
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $periodepembayaran = new PeriodePembayaranModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataKategoriPaket' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataPackingBarang' => '',
            'DataPaketBarang' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => 'dataperiodetransaksi',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        // ambil artikel yang akan diedit

        $data['tb_pay_periode'] = $periodepembayaran->where('pe_id', $pe_id)->first();

        // lakukan validasi data artikel
        $validation = \Config\Services::validation();
        $validation->setRules([
            'pe_nama' => 'required',
            'pe_periode' => 'required',
        ]);
        $isDataValid = $validation->withRequest($this->request)->run();
        // jika data vlid, maka simpan ke database
        if ($isDataValid) {
            $periodepembayaran->update($pe_id, [
                'pe_nama' => strtoupper($this->request->getVar('pe_nama')),
                'pe_periode' => $this->request->getVar('pe_periode'),
                'u_id' => session()->get('u_id')
            ]);
            session()->setFlashdata('success', 'Data Berhasil Di Edit!');
            return redirect('admin/datatransaksi/dataperiodepembayaran');
        }
        echo view('admin/DataTransaksi/PeriodePembayaran/periodepembayaranedit', $data);
    }
    public function deleteperiodepembayaran($pe_id)
    {
        $periodepembayaran = new PeriodePembayaranModels();
        $periodepembayaran->delete($pe_id);
        session()->setFlashdata('success', 'Data Berhasil Di Hapus!');
        return redirect('admin/datatransaksi/dataperiodepembayaran');
    }
    public function ImportFileExcelperiodepembayaran()
    {
        $periodepembayaran = new PeriodePembayaranModels();
        $file = $this->request->getFile('file');
        $extension = $file->getClientExtension();
        if ($extension == 'xlsx' || $extension == 'xls') {
            if ($extension == 'xls') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            $spreadsheet = $reader->load($file);
            $kategori = $spreadsheet->getActiveSheet()->toArray();
            foreach ($kategori as $key => $value) {
                if ($key == 0) {
                    continue;
                }

                $data = [
                    'pe_id ' => $periodepembayaran->getInsertID(),
                    'pe_nama' => strtoupper($value[1]),
                    'pe_periode' => $value[2],
                    'u_id' => session()->get('u_id'),

                ];
                $periodepembayaran->insert($data);
            }
            session()->setFlashdata('success', 'Data Berhasil Diimport!');
            return redirect('admin/datatransaksi/dataperiodepembayaran');
        } else {
            return redirect()->back()->with('message', 'Format File Tidak Sesuai! | Extension file harus .xls atau .xlsx');
        }
    }
    public function ExportDataExcelperiodepembayaran()
    {
        $periodepembayaran = new PeriodePembayaranModels();
        $dataperiodepembayaran = $periodepembayaran->findAll();
        $spreadsheet = new Spreadsheet();
        $colomheader = $spreadsheet->getActiveSheet();
        $colomheader->setCellValue('A1', 'No');
        $colomheader->setCellValue('B1', 'Nama Periode Pembayaran');
        $colomheader->setCellValue('C1', 'Jumlah Periode Pembayaran');

        $colomdata = 2;
        foreach ($dataperiodepembayaran as $setperiodepembayaran) {
            $colomheader->setCellValue('A' . $colomdata, ($colomdata - 1));
            $colomheader->setCellValue('B' . $colomdata, $setperiodepembayaran['pe_nama']);
            $colomheader->setCellValue('C' . $colomdata, $setperiodepembayaran['pe_periode']);
            $colomdata++;
        }
        $colomheader->getStyle('A1:C1')->getFont()->setBold(true);
        $colomheader->getStyle('A1:C1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $colomheader->getStyle('A1:C' . ($colomdata - 1))->applyFromArray($styleArray);

        $colomheader->getColumnDimension('A')->setAutoSize(true);
        $colomheader->getColumnDimension('B')->setAutoSize(true);
        $colomheader->getColumnDimension('C')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Export-Data-PeriodePembayaran.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
    public function ExportTemplateDataExcelperiodepembayaran()
    {
        $spreadsheet = new Spreadsheet();
        $colomheader = $spreadsheet->getActiveSheet();
        $colomheader->setCellValue('A1', 'No');
        $colomheader->setCellValue('B1', 'Nama Periode Pembayaran');
        $colomheader->setCellValue('C1', 'Jumlah Periode Pembayaran');
        $colomheader->getStyle('A1:C1')->getFont()->setBold(true);
        $colomheader->getStyle('A1:C1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $colomheader->getColumnDimension('A')->setAutoSize(true);
        $colomheader->getColumnDimension('B')->setAutoSize(true);
        $colomheader->getColumnDimension('C')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Tempalte-Export-Data-PeriodePembayaran.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
    //Data Transaksi Pembayaran
    public function transaksi()
    {
        $UsersModels = new UsersModels();
        $paketbarang = new PaketBarangModels();
        $transaksi = new TransaksiModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataKategoriPaket' => '',
            'DataPackingBarang' => '',
            'DataPaketBarang' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => 'datatransaksi',
            'DataPaket' => $transaksi->datapaket(),
            'DataUser' => $transaksi->datauser(),
            'payperiode' => $paketbarang->datapayperiode(),
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        return view('admin/DataTransaksi/TransaksiPaket/transaksi', $data);
        // return view('register');
    }
    public function transaksiprocess()
    {
        if (!$this->validate([
            'u_id' => 'required',
            'p_id' => 'required',
            't_qty' => 'required',
            // 't_status' => 'required',
        ])) {
            session()->setFlashdata('error', $this->validator->listErrors());
            return redirect()->back()->withInput();
        }
        $hargapaket = $this->request->getVar('p_hargapaket');
        $u_id = $this->request->getVar('u_id');
        $p_id = $this->request->getVar('p_id');
        $pe_id = $this->request->getVar('pe_id');
        $qty = $this->request->getVar('t_qty');
        $total = $hargapaket * $qty;
        $transaksi = new TransaksiModels();
        $transaksi->insert([
            'u_id' => $u_id,
            'p_id' => $p_id,
            'pe_id' => $pe_id,
            't_qty' => $qty,
            't_totalharga' => $total,
            // 't_status' => $this->request->getVar('t_status')
        ]);
        // $cicilan = new CicilanModels();
        // $datapaket = $transaksi->HargaPaket($p_id);
        // foreach ($datapaket as $value => $k) {
        //     $hargajual = $k['p_hargaJual'];
        // }
        // $number = 0;
        // for ($i = 0; $i < $qty; $i++) {
        //     $cicilan->insert([
        //         'u_id' => $u_id,
        //         'p_id' => $p_id,
        //         't_id' => $transaksi->getInsertID(),
        //         'pe_id' => $pe_id,
        //         'c_total_cicilan' => $hargajual,
        //         'c_total_biaya' => $hargajual,
        //         'c_cicilan_masuk' => $number,
        //         'c_cicilan_outstanding' => $number,
        //         'c_biaya_masuk' => $number,
        //         'c_biaya_outstanding' => $number,
        //         // 't_status' => $this->request->getVar('t_status')
        //     ]);
        // }
        session()->setFlashdata('success', 'Data Berhasil Disimpan!');
        return redirect()->to('/admin/datatransaksi/transaksi');
    }
    public function listdatatransaksi()
    {
        $UsersModels = new UsersModels();
        $paketbarang = new PaketBarangModels();
        $transaksi = new TransaksiModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'DataPackingBarang' => '',
            'MenuDataBarang' => '',
            'DataPaketBarang' => '',
            'DataKategoriPaket' => '',
            'DataTransaksiCicilan' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => 'datatransaksi',
            'DataPaket' => $transaksi->datapaket(),
            'DataUser' => $transaksi->datauser(),
            'payperiode' => $paketbarang->datapayperiode(),
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),


        ];
        $data['tb_transaksi'] = $transaksi->findAll();
        echo view('admin/DataTransaksi/TransaksiPaket/datatransaksi', $data);
        //berdasarkan login
        // $user = new UsersModels();
        // $data['tb_user'] = $user->where('u_referensi', session('u_id'))->findAll();
        // echo view('admin/datauser', $data);
    }
    public function editapprovedtransaksi($t_id)
    {
        $paketbarang = new PaketBarangModels();
        $transaksi = new TransaksiModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataPackingBarang' => '',
            'DataPaketBarang' => '',
            'DataKategoriPaket' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => 'datatransaksi',
            'DataPaket' => $transaksi->datapaket(),
            'DataUser' => $transaksi->datauser(),
            'payperiode' => $paketbarang->datapayperiode(),
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',


        ];
        $data['tb_transaksi'] = $transaksi->findAll();
        if ($t_id != null) {
            $transaksi->update($t_id, [
                't_approval_by' => session()->get('u_id')
            ]);
            // session()->setFlashdata('success', 'Data Berhasil Di Setujui!');
            $cicilan = new CicilanModels();
            $datatransaksi = $cicilan->datatransaksiby_id($t_id);
            foreach ($datatransaksi as $datatransaksi_id) {
                $u_id = $datatransaksi_id['u_id'];
                $p_id = $datatransaksi_id['p_id'];
                $pe_id = $datatransaksi_id['pe_id'];
                $qty = $datatransaksi_id['t_qty'];
            }
            $dataperiode = $transaksi->dataperiodeby_id($pe_id);
            foreach ($dataperiode as $value => $pay) {
                $pe_periode = $pay['pe_periode'];
            }
            $datapaket = $transaksi->HargaPaket($p_id);
            foreach ($datapaket as $value => $k) {
                $hargajual = $k['p_hargaJual'];
            }
            $number = 0;
            for ($i = 0; $i < $qty; $i++) {
                $cicilan->insert([
                    'u_id' => $u_id,
                    'p_id' => $p_id,
                    't_id' => $t_id,
                    'pe_id' => $pe_id,
                    'c_total_cicilan' => $pe_periode,
                    'c_total_biaya' => $hargajual,
                    'c_cicilan_masuk' => $number,
                    'c_cicilan_outstanding' => $pe_periode,
                    'c_biaya_masuk' => $number,
                    'c_biaya_outstanding' => $hargajual,
                    // 't_status' => $this->request->getVar('t_status')
                ]);
            }
            return redirect()->back();
        }
        return redirect()->back();
    }
    public function editnoapprovedtransaksi($t_id)
    {
        $paketbarang = new PaketBarangModels();
        $transaksi = new TransaksiModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataPackingBarang' => '',
            'DataKategoriPaket' => '',
            'DataPaketBarang' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => 'datatransaksi',
            'DataPaket' => $transaksi->datapaket(),
            'DataUser' => $transaksi->datauser(),
            'payperiode' => $paketbarang->datapayperiode(),
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',

        ];
        $data['tb_transaksi'] = $transaksi->findAll();
        if ($t_id != null) {
            $transaksi->update($t_id, [
                't_approval_by' => Null
            ]);
            $cicilan = new CicilanModels();
            // $datacicilan = $cicilan->get_cicilanby_t_id($t_id);
            $datacicilan = $cicilan->paketcicilanby_t_id($t_id);
            $dataset_c_id = $cicilan->get_cicilanby_t_id($t_id);
            foreach ($dataset_c_id as $value => $tb_cicilan) {
                foreach ($datacicilan as $value => $data) {
                    if ($t_id == $data['t_id']) {
                        $t_id1 = (int)$data['t_id'];
                    }
                    if ($t_id1 == $tb_cicilan['t_id']) {
                        $c_id1 = (int)$tb_cicilan['c_id'];
                    }
                }
            }

            if ($t_id1 != null) {
                $cicilan->deletelogcicilan($c_id1);
                $cicilan->deletelogcicilansementara($c_id1);
                $cicilan->deletecicilan($t_id);
            }
            // print_r('data transaksi : ' . 'uid' . $u_id . $p_id . $pe_id . '<br>' . 'data cicilan : ' . $u_id1 . $p_id1 . $pe_id1);
            // session()->setFlashdata('success', 'Data Berhasil Di Setujui!');
            return redirect()->back();
        }
        return redirect()->back();
    }
    public function edittransaksi($t_id)
    {
        $UsersModels = new UsersModels();
        $paketbarang = new PaketBarangModels();
        $transaksi = new TransaksiModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataPackingBarang' => '',
            'DataKategoriPaket' => '',
            'DataPaketBarang' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => 'datatransaksi',
            'DataPaket' => $transaksi->datapaket(),
            'DataUser' => $transaksi->datauser(),
            'payperiode' => $paketbarang->datapayperiode(),
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        // ambil artikel yang akan diedit

        $data['tb_transaksi'] = $transaksi->where('t_id', $t_id)->first();

        // lakukan validasi data artikel
        $validation = \Config\Services::validation();
        $validation->setRules([
            'u_id' => 'required',
            'p_id' => 'required',
            't_qty' => 'required',
            // 't_status' => 'required',
        ]);
        $isDataValid = $validation->withRequest($this->request)->run();

        // jika data vlid, maka simpan ke database
        if ($isDataValid) {
            $hargapaket = $this->request->getVar('p_hargapaket');
            $hargapaketedit = $this->request->getVar('t_totalhargaedit');
            if ($hargapaket != null) {
                $lookuphargapaket = $hargapaket;
            } else {
                $lookuphargapaket = $hargapaketedit;
            }
            $qty = $this->request->getVar('t_qty');
            $total = $lookuphargapaket * $qty;
            $u_id = $this->request->getVar('u_id');
            $pe_id = $this->request->getVar('pe_id');
            $p_id = $this->request->getVar('p_id');
            $transaksi->update($t_id, [
                'u_id' => $u_id,
                'p_id' => $p_id,
                'pe_id' => $pe_id,
                't_qty' => $qty,
                't_totalharga' => $total,
                // 't_status' => $this->request->getVar('t_status')
            ]);
            $cicilan = new CicilanModels();
            // $datacicilan = $cicilan->get_cicilanby_t_id($t_id);
            // $datacicilan1 = $cicilan->paketcicilanby_t_id($t_id);
            // foreach ($datacicilan as $value => $data) {
            //     foreach ($datacicilan1 as $value => $data1) {
            //         if ($data['t_id'] == $data1['t_id']) {
            //             $u_id1 = (int)$data1['u_id'];
            //             $p_id1 = (int)$data1['p_id'];
            //             $pe_id1 = (int)$data1['pe_id'];
            //             $t_qty1 = (int)$data1['t_qty'];
            //         }
            //     }
            // }
            $datapaket = $transaksi->HargaPaket($p_id);
            foreach ($datapaket as $value => $k) {
                $hargajual = $k['p_hargaJual'];
            }
            $number = 0;
            // if ($qty != $t_qty1 || $p_id != $p_id1 || $u_id != $u_id1 || $pe_id != $pe_id1) {
            //     $cicilan->deletecicilan($t_id);

            //     for ($i = 0; $i < $qty; $i++) {
            //         $cicilan->insert([
            //             'u_id' => $this->request->getVar('u_id'),
            //             'p_id' => $p_id,
            //             't_id' => $t_id,
            //             'pe_id' => $this->request->getVar('pe_id'),
            //             'c_total_cicilan' => $hargajual,
            //             'c_total_biaya' => $hargajual,
            //             'c_cicilan_masuk' => $number,
            //             'c_cicilan_outstanding' => $number,
            //             'c_biaya_masuk' => $number,
            //             'c_biaya_outstanding' => $number,
            //             // 't_status' => $this->request->getVar('t_status')
            //         ]);
            //     }
            // }
            if ($transaksi->where('t_id') != null) {
                $cicilan->deletecicilan($t_id);
            }

            for ($i = 0; $i < $qty; $i++) {
                $cicilan->insert([
                    'u_id' => $u_id,
                    'p_id' => $p_id,
                    't_id' => $t_id,
                    'pe_id' => $pe_id,
                    'c_total_cicilan' => $hargajual,
                    'c_total_biaya' => $hargajual,
                    'c_cicilan_masuk' => $number,
                    'c_cicilan_outstanding' => $number,
                    'c_biaya_masuk' => $number,
                    'c_biaya_outstanding' => $number,
                    // 't_status' => $this->request->getVar('t_status')
                ]);
            }

            session()->setFlashdata('success', 'Data Berhasil Di Edit!');
            return redirect('admin/datatransaksi/datatransaksi');
        }
        echo view('admin/DataTransaksi/TransaksiPaket/transaksiedit', $data);
    }
    public function deletetransaksi($p_id)
    {
        $transaksi = new TransaksiModels();
        $transaksi->delete($p_id);
        session()->setFlashdata('success', 'Data Berhasil Di Hapus!');
        return redirect('admin/datatransaksi/datatransaksi');
    }
    public function ImportFileExceltransaksi()
    {
        $transaksi = new TransaksiModels();
        $datatransaksi = $transaksi->findAll();
        $file = $this->request->getFile('file');
        $extension = $file->getClientExtension();
        if ($extension == 'xlsx' || $extension == 'xls') {
            if ($extension == 'xls') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            $spreadsheet = $reader->load($file);
            $kategori = $spreadsheet->getActiveSheet()->toArray();
            foreach ($kategori as $key => $value) {
                if ($key == 0) {
                    continue;
                }
                $users = new UsersModels();
                $datausers = $users->findAll();
                $datausers1 = $users->findAll();
                $paket = new PaketBarangModels();
                $datapaket = $paket->findAll();
                $periodepembayaran = new PeriodePembayaranModels();
                $dataperiodepembayaran = $periodepembayaran->findAll();
                foreach ($datausers as $data) {
                    if (isset($value[1]) && strtoupper($value[1])  == $data['u_nama']) {
                        $u_id = $data['u_id'];
                    }
                }
                $qty = $value[4];
                foreach ($datapaket as $setpaket) {
                    if (isset($value[2]) && strtoupper($value[2])  == $setpaket['p_nama']) {
                        $p_id = $setpaket['p_id'];
                        $p_hargaJual = $setpaket['p_hargaJual'];
                        $jumlah = $qty * $p_hargaJual;
                    }
                }
                foreach ($dataperiodepembayaran as $setperiodepembayaran) {
                    if (isset($value[3]) && strtoupper($value[3])  == $setperiodepembayaran['pe_nama']) {
                        $pe_id = $setperiodepembayaran['pe_id'];
                    }
                }


                $data = [
                    't_id ' => $transaksi->getInsertID(),
                    'u_id' => $u_id,
                    'p_id' => $p_id,
                    'pe_id' => $pe_id,
                    't_qty' => $value[4],
                    't_totalharga' => $jumlah,

                ];
                $transaksi->insert($data);
            }
            session()->setFlashdata('success', 'Data Berhasil Diimport!');
            return redirect('admin/datatransaksi/datatransaksi');
        } else {
            return redirect()->back()->with('message', 'Format File Tidak Sesuai! | Extension file harus .xls atau .xlsx');
        }
    }
    public function ExportDataExceltransaksi()
    {
        $transaksi = new TransaksiModels();
        $datatransaksi = $transaksi->findAll();
        $spreadsheet = new Spreadsheet();
        $colomheader = $spreadsheet->getActiveSheet();
        $colomheader->setCellValue('A1', 'No');
        $colomheader->setCellValue('B1', 'Nama Pengambil Paket');
        $colomheader->setCellValue('C1', 'Nama Referensi');
        $colomheader->setCellValue('D1', 'Nama Paket');
        $colomheader->setCellValue('E1', 'Nama Periode Pembayaran');
        $colomheader->setCellValue('F1', 'Jumlah Periode Pembayaran');
        $colomheader->setCellValue('G1', 'Jumlah Paket');
        $colomheader->setCellValue('H1', 'Jumlah Total Harga Paket');
        $colomheader->setCellValue('I1', 'Waktu Transaksi Paket');

        $users = new UsersModels();
        $datausers = $users->findAll();
        $datausers1 = $users->findAll();
        $paket = new PaketBarangModels();
        $datapaket = $paket->findAll();
        $periodepembayaran = new PeriodePembayaranModels();
        $dataperiodepembayaran = $periodepembayaran->findAll();
        $colomdata = 2;
        foreach ($datatransaksi as $settransaksi) {
            $colomheader->setCellValue('A' . $colomdata, ($colomdata - 1));
            foreach ($datausers as $data) {
                if ($settransaksi['u_id'] == $data['u_id']) {
                    $namapengambil = $data['u_nama'];
                    $colomheader->setCellValue('B' . $colomdata, $namapengambil);
                    $referensi = $data['u_referensi'];
                }
            }
            foreach ($datausers as $data) {
                if ($referensi == $data['u_id']) {
                    $colomheader->setCellValue('C' . $colomdata, $data['u_nama']);
                }
            }

            foreach ($datapaket as $setpaket) {
                if ($settransaksi['p_id'] == $setpaket['p_id']) {
                    $colomheader->setCellValue('D' . $colomdata, $setpaket['p_nama']);
                }
            }

            foreach ($dataperiodepembayaran as $setperiodepembayaran) {
                if ($settransaksi['pe_id'] == $setperiodepembayaran['pe_id']) {
                    $colomheader->setCellValue('E' . $colomdata, $setperiodepembayaran['pe_nama']);
                    $colomheader->setCellValue('F' . $colomdata, $setperiodepembayaran['pe_periode']);
                }
            }

            $colomheader->setCellValue('G' . $colomdata, $settransaksi['t_qty']);
            $colomheader->setCellValue('H' . $colomdata, $settransaksi['t_totalharga']);
            $colomheader->setCellValue('I' . $colomdata, $settransaksi['waktu']);
            $colomdata++;
        }

        $colomheader->getStyle('A1:I1')->getFont()->setBold(true);
        $colomheader->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $colomheader->getStyle('A1:I' . ($colomdata - 1))->applyFromArray($styleArray);

        $colomheader->getColumnDimension('A')->setAutoSize(true);
        $colomheader->getColumnDimension('B')->setAutoSize(true);
        $colomheader->getColumnDimension('C')->setAutoSize(true);
        $colomheader->getColumnDimension('D')->setAutoSize(true);
        $colomheader->getColumnDimension('E')->setAutoSize(true);
        $colomheader->getColumnDimension('F')->setAutoSize(true);
        $colomheader->getColumnDimension('G')->setAutoSize(true);
        $colomheader->getColumnDimension('H')->setAutoSize(true);
        $colomheader->getColumnDimension('I')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Export-Data-Transaksi.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
    public function ExportTemplateDataExceltransaksi()
    {
        $spreadsheet = new Spreadsheet();
        $colomheader = $spreadsheet->getActiveSheet();
        $colomheader->setCellValue('A1', 'No');
        $colomheader->setCellValue('B1', 'Nama Pengambil Paket');
        $colomheader->setCellValue('C1', 'Nama Paket');
        $colomheader->setCellValue('D1', 'Nama Periode Pembayaran');
        $colomheader->setCellValue('E1', 'Jumlah Paket');

        $colomheader->getStyle('A1:E1')->getFont()->setBold(true);
        $colomheader->getStyle('A1:E1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $colomheader->getColumnDimension('A')->setAutoSize(true);
        $colomheader->getColumnDimension('B')->setAutoSize(true);
        $colomheader->getColumnDimension('C')->setAutoSize(true);
        $colomheader->getColumnDimension('D')->setAutoSize(true);
        $colomheader->getColumnDimension('E')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Template-Export-Data-Transaksi.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
    //Data cicilan Pembayaran

    public function cicilan()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $paketbarang = new PaketBarangModels();
        $cicilan = new CicilanModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataPackingBarang' => '',
            'DataKategoriPaket' => '',
            'DataPaketBarang' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => '',
            'DataPaket' => $cicilan->datapaket(),
            'DataUser' => $cicilan->datauser(),
            'DataPayPeriode' => $cicilan->dataperiode(),
            'DataTransaksiFungsi' => $cicilan->datatransaksi(),
            'DataTransaksiCicilan' => 'datatransaksicicilan',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        return view('admin/DataTransaksi/TransaksiCicilan/cicilan', $data);
        // return view('register');
    }
    public function cicilanprocess()
    {
        if (!$this->validate([
            'u_id' => 'required',
            'p_id' => 'required',
            't_id' => 'required',
            'pe_id' => 'required',
            'c_total_cicilan' => 'required',
            'c_cicilan_masuk' => 'required',
            'c_cicilan_outstanding' => 'required',
            'c_total_biaya' => 'required',
            'c_biaya_masuk' => 'required',
            'c_biaya_outstanding' => 'required',
        ])) {
            session()->setFlashdata('error', $this->validator->listErrors());
            return redirect()->back()->withInput();
        }
        $c_total_cicilan = floatval(str_replace(",", "", $this->request->getVar('c_total_cicilan')));
        $c_cicilan_masuk = floatval(str_replace(",", "", $this->request->getVar('c_cicilan_masuk')));
        $c_cicilan_outstanding = floatval(str_replace(",", "", $this->request->getVar('c_cicilan_outstanding')));
        $c_total_biaya = floatval(str_replace(",", "", $this->request->getVar('c_total_biaya')));
        $c_biaya_masuk = floatval(str_replace(",", "", $this->request->getVar('c_biaya_masuk')));
        $c_biaya_outstanding = floatval(str_replace(",", "", $this->request->getVar('c_biaya_outstanding')));
        // Validasi nilai variabel
        if (!is_numeric($c_total_cicilan) || !is_numeric($c_cicilan_masuk) || !is_numeric($c_cicilan_outstanding) || !is_numeric($c_total_biaya) || !is_numeric($c_biaya_masuk) || !is_numeric($c_biaya_outstanding)) {
            echo "Input tidak valid!";
            exit;
        }
        $cicilan = new CicilanModels();
        $cicilan->insert([
            'u_id' => $this->request->getVar('u_id'),
            'p_id' => $this->request->getVar('p_id'),
            't_id' => $this->request->getVar('t_id'),
            'pe_id' => $this->request->getVar('pe_id'),
            'c_total_cicilan' => $c_total_cicilan,
            'c_cicilan_masuk' => $c_cicilan_masuk,
            'c_cicilan_outstanding' => $c_cicilan_outstanding,
            'c_total_biaya' => $c_total_biaya,
            'c_biaya_masuk' => $c_biaya_masuk,
            'c_biaya_outstanding' => $c_biaya_outstanding
        ]);
        session()->setFlashdata('success', 'Data Berhasil Disimpan!');
        return redirect()->to('/admin/datatransaksi/cicilan');
    }
    public function listdatacicilan()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $paketbarang = new PaketBarangModels();
        $cicilan = new CicilanModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'DataPackingBarang' => '',
            'DataKategoriPaket' => '',
            'MenuDataBarang' => '',
            'DataPaketBarang' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => '',
            'DataPaket' => $cicilan->datapaket(),
            'DataUser' => $cicilan->datauser(),
            'DataPayPeriode' => $cicilan->dataperiode(),
            'DataTransaksiFungsi' => $cicilan->datatransaksi(),
            'DataTransaksiCicilan' => 'datatransaksicicilan',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),
        ];
        $data['tb_cicilan'] = $cicilan->findAll();
        echo view('admin/DataTransaksi/TransaksiCicilan/datacicilan', $data);
        //berdasarkan login
        // $user = new UsersModels();
        // $data['tb_user'] = $user->where('u_referensi', session('u_id'))->findAll();
        // echo view('admin/datauser', $data);
    }
    public function editcicilan($c_id)
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $paketbarang = new PaketBarangModels();
        $cicilan = new CicilanModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataPackingBarang' => '',
            'DataKategoriPaket' => '',
            'DataPaketBarang' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => '',
            'DataPaket' => $cicilan->datapaket(),
            'DataUser' => $cicilan->datauser(),
            'DataPayPeriode' => $cicilan->dataperiode(),
            'DataTransaksiFungsi' => $cicilan->datatransaksi(),
            'DataTransaksiCicilan' => 'datatransaksicicilan',
            'DataTransaksiLogCicilan' => '',
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),
        ];
        // ambil artikel yang akan diedit

        $data['tb_cicilan'] = $cicilan->where('c_id', $c_id)->first();

        // lakukan validasi data artikel
        $validation = \Config\Services::validation();
        $validation->setRules([
            'u_id' => 'required',
            'p_id' => 'required',
            't_id' => 'required',
            'pe_id' => 'required',
            'c_total_cicilan' => 'required',
            'c_cicilan_masuk' => 'required',
            'c_cicilan_outstanding' => 'required',
            'c_total_biaya' => 'required',
            'c_biaya_masuk' => 'required',
            'c_biaya_outstanding' => 'required',
        ]);
        $isDataValid = $validation->withRequest($this->request)->run();
        // jika data vlid, maka simpan ke database
        if ($isDataValid) {
            $c_total_cicilan = floatval(str_replace(",", "", $this->request->getVar('c_total_cicilan')));
            $c_cicilan_masuk = floatval(str_replace(",", "", $this->request->getVar('c_cicilan_masuk')));
            $c_cicilan_outstanding = floatval(str_replace(",", "", $this->request->getVar('c_cicilan_outstanding')));
            $c_total_biaya = floatval(str_replace(",", "", $this->request->getVar('c_total_biaya')));
            $c_biaya_masuk = floatval(str_replace(",", "", $this->request->getVar('c_biaya_masuk')));
            $c_biaya_outstanding = floatval(str_replace(",", "", $this->request->getVar('c_biaya_outstanding')));
            // Validasi nilai variabel
            if (!is_numeric($c_total_cicilan) || !is_numeric($c_cicilan_masuk) || !is_numeric($c_cicilan_outstanding) || !is_numeric($c_total_biaya) || !is_numeric($c_biaya_masuk) || !is_numeric($c_biaya_outstanding)) {
                echo "Input tidak valid!";
                exit;
            }
            $cicilan->update($c_id, [
                'u_id' => $this->request->getVar('u_id'),
                'p_id' => $this->request->getVar('p_id'),
                't_id' => $this->request->getVar('t_id'),
                'pe_id' => $this->request->getVar('pe_id'),
                'c_total_cicilan' => $c_total_cicilan,
                'c_cicilan_masuk' => $c_cicilan_masuk,
                'c_cicilan_outstanding' => $c_cicilan_outstanding,
                'c_total_biaya' => $c_total_biaya,
                'c_biaya_masuk' => $c_biaya_masuk,
                'c_biaya_outstanding' => $c_biaya_outstanding
            ]);
            session()->setFlashdata('success', 'Data Berhasil Di Edit!');
            return redirect('admin/datatransaksi/datacicilan');
        }
        echo view('admin/DataTransaksi/TransaksiCicilan/cicilanedit', $data);
    }
    public function deletecicilan($c_id)
    {
        $cicilan = new CicilanModels();
        $cicilan->delete($c_id);
        session()->setFlashdata('success', 'Data Berhasil Di Hapus!');
        return redirect('admin/datatransaksi/datacicilan');
    }
    public function ImportFileExcelcicilan()
    {
        $cicilan = new CicilanModels();
        $file = $this->request->getFile('file');
        $extension = $file->getClientExtension();
        if ($extension == 'xlsx' || $extension == 'xls') {
            if ($extension == 'xls') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            $spreadsheet = $reader->load($file);
            $kategori = $spreadsheet->getActiveSheet()->toArray();
            foreach ($kategori as $key => $value) {
                if ($key == 0) {
                    continue;
                }
                $users = new UsersModels();
                $datausers = $users->findAll();
                foreach ($datausers as $setusers) {
                    if ($value[1] == $setusers['u_nama']) {
                        $user = $setusers['u_id'];
                    }
                }
                $paket = new PaketBarangModels();
                $datapaket = $paket->findAll();
                foreach ($datapaket as $setpaket) {
                    if ($value[2] == $setpaket['p_nama']) {
                        $pkt = $setpaket['p_id'];
                    }
                }
                $transaksi = new TransaksiModels();
                $datatransaksi = $transaksi->findAll();
                foreach ($datatransaksi as $settransaksi) {
                    if ($value[3] == $settransaksi['t_status']) {
                        $trk = $settransaksi['t_id'];
                    }
                }
                $payperiode = new PeriodePembayaranModels();
                $datapayperiode = $payperiode->findAll();
                foreach ($datapayperiode as $setpayperiode) {
                    if ($value[4] == $setpayperiode['pe_periode']) {
                        $periode = $setpayperiode['pe_id'];
                    }
                }
                $data = [
                    'c_id' => $value[0],
                    'u_id' => $user,
                    'p_id' => $pkt,
                    't_id' => $trk,
                    'pe_id' => $periode,
                    'c_total_cicilan' => $value[5],
                    'c_cicilan_masuk' => $value[6],
                    'c_cicilan_outstanding' => $value[7],
                    'c_total_biaya' => $value[8],
                    'c_biaya_masuk' => $value[9],
                    'c_biaya_outstanding' => $value[10],

                ];
                $cicilan->insert($data);
            }
            session()->setFlashdata('success', 'Data Berhasil Diimport!');
            return redirect('admin/datacicilan/datacicilan');
        } else {
            return redirect()->back()->with('message', 'Format File Tidak Sesuai! | Extension file harus .xls atau .xlsx');
        }
    }
    public function ExportDataExcelcicilan()
    {
        $cicilan = new CicilanModels();
        $datacicilan = $cicilan->findAll();
        $spreadsheet = new Spreadsheet();
        $colomheader = $spreadsheet->getActiveSheet();
        $colomheader->setCellValue('A1', 'No');
        $colomheader->setCellValue('B1', 'Nama Pengambil Paket');
        $colomheader->setCellValue('C1', 'Nama Paket');
        $colomheader->setCellValue('D1', 'Status Cicilan Paket');
        $colomheader->setCellValue('E1', 'Periode Cicilan');
        $colomheader->setCellValue('F1', 'Jumlah Total Cicilan');
        $colomheader->setCellValue('G1', 'Jumlah Cicilan Masuk');
        $colomheader->setCellValue('H1', 'Jumlah Cicilan Outstanding');
        $colomheader->setCellValue('I1', 'Jumlah Total Biaya');
        $colomheader->setCellValue('J1', 'Jumlah Biaya Masuk');
        $colomheader->setCellValue('K1', 'Jumlah Biaya Outstanding');

        $users = new UsersModels();
        $datausers = $users->findAll();
        $paket = new PaketBarangModels();
        $datapaket = $paket->findAll();
        $transaksi = new TransaksiModels();
        $datatransaksi = $transaksi->findAll();
        $payperiode = new PeriodePembayaranModels();
        $datapayperiode = $payperiode->findAll();
        $colomdata = 2;
        foreach ($datacicilan as $setcicilan) {
            $totalharga = $setcicilan['c_biaya_masuk'];
            $colomheader->setCellValue('A' . $colomdata, ($colomdata - 1));
            foreach ($datausers as $setusers) {
                if ($setcicilan['u_id'] == $setusers['u_id']) {
                    $colomheader->setCellValue('B' . $colomdata, $setusers['u_nama']);
                }
            }
            foreach ($datapaket as $setpaket) {
                if ($setcicilan['p_id'] == $setpaket['p_id']) {
                    $hargajual = $setpaket['p_hargaJual'];
                    $colomheader->setCellValue('C' . $colomdata, $setpaket['p_nama']);
                }
            }
            foreach ($datatransaksi as $settransaksi) {
                if ($setcicilan['t_id'] == $settransaksi['t_id']) {
                    $t_qty = $settransaksi['t_qty'];
                    $jumlahhargajual = $hargajual * $t_qty;
                    if ($totalharga == $jumlahhargajual) {
                        $status = "SUDAH LUNAS";
                    } else {
                        $status = "BELUM LUNAS";
                    }
                    $colomheader->setCellValue('D' . $colomdata, $status);
                }
            }
            foreach ($datapayperiode as $setpayperiode) {
                if ($setcicilan['pe_id'] == $setpayperiode['pe_id']) {
                    $colomheader->setCellValue('E' . $colomdata, $setpayperiode['pe_nama']);
                }
            }
            $colomheader->setCellValue('F' . $colomdata, $setcicilan['c_total_cicilan']);
            $colomheader->setCellValue('G' . $colomdata, $setcicilan['c_cicilan_masuk']);
            $colomheader->setCellValue('H' . $colomdata, $setcicilan['c_cicilan_outstanding']);
            $colomheader->setCellValue('I' . $colomdata, $setcicilan['c_total_biaya']);
            $colomheader->setCellValue('J' . $colomdata, $setcicilan['c_biaya_masuk']);
            $colomheader->setCellValue('K' . $colomdata, $setcicilan['c_biaya_outstanding']);
            $colomdata++;
        }
        $colomheader->getStyle('A1:K1')->getFont()->setBold(true);
        $colomheader->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $colomheader->getStyle('A1:K' . ($colomdata - 1))->applyFromArray($styleArray);

        $colomheader->getColumnDimension('A')->setAutoSize(true);
        $colomheader->getColumnDimension('B')->setAutoSize(true);
        $colomheader->getColumnDimension('C')->setAutoSize(true);
        $colomheader->getColumnDimension('D')->setAutoSize(true);
        $colomheader->getColumnDimension('E')->setAutoSize(true);
        $colomheader->getColumnDimension('F')->setAutoSize(true);
        $colomheader->getColumnDimension('G')->setAutoSize(true);
        $colomheader->getColumnDimension('H')->setAutoSize(true);
        $colomheader->getColumnDimension('I')->setAutoSize(true);
        $colomheader->getColumnDimension('J')->setAutoSize(true);
        $colomheader->getColumnDimension('K')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Export-Data-Cicilan.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
    //Data Barang Supplier
    public function logcicilan()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $logcicilan = new LogCicilanModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataKategoriPaket' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataPackingBarang' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => '',
            'DataPaketBarang' => '',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => 'datatransaksilogcicilan',
            'DataUser' => $logcicilan->datauser(),
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        return view('admin/DataTransaksi/TransaksiLogCicilan/logcicilan', $data);
        // return view('register');
    }
    public function logcicilanprocess()
    {
        $transaksi = new TransaksiModels();
        if (!$this->validate([
            'u_id' => 'required',
            'l_jumlah_bayar' => 'required',
            'l_foto' => [
                'rules' => 'uploaded[l_foto]|max_size[l_foto,1024]|mime_in[l_foto,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                'errors' => [
                    'uploaded' => '{field} Wajib diisi!',
                    'max_size' => 'Ukuran {field} Maksimal 1024 KB ',
                    'mime_in' => 'Format {field} harus JPG/JPEG/PNG!',
                ]
            ],
        ])) {
            session()->setFlashdata('error', $this->validator->listErrors());
            return redirect()->back()->withInput();
        }
        $l_jumlah_bayar = floatval(str_replace(",", "", $this->request->getVar('l_jumlah_bayar')));
        // Validasi nilai variabel
        if (!is_numeric($l_jumlah_bayar)) {
            echo "Input tidak valid!";
            exit;
        }
        $foto = $this->request->getFile('l_foto');
        $nama_file = $foto->getRandomName();
        // $logcicilan = new LogCicilanModels();
        $logcicilan = new LogCicilanSementaraModels();
        $u_id = $this->request->getVar('u_id');
        $c_id = $this->request->getVar('c_id');
        $datacicilan = $transaksi->datacicilanby_id($c_id);
        $datapaket = $transaksi->datapaket();
        foreach ($datacicilan as $tb_cicilan) {
            $p_id = $tb_cicilan['p_id'];
        }
        $datatransaksi = $transaksi->datatransaksi_by_id($p_id);
        foreach ($datatransaksi as $tb_transaksi) {
            $t_qty = $tb_transaksi['t_qty'];
        }
        foreach ($datapaket as $tb_paket) {
            if ($p_id == $tb_paket['p_id']) {
                $p_setoran = $tb_paket['p_setoran'];
            }
        }
        $jumlah_pembayaran_cicilan = $l_jumlah_bayar / $p_setoran;
        $harga_bayar_cicilan = $l_jumlah_bayar / $jumlah_pembayaran_cicilan;
        // print_r($u_id.'-'.$c_id.'-'.$jumlah_bayar_cicilan.'-'.$jumlah_pembayaran_cicilan);
        // for ($i = 0; $i <= $jumlah_pembayaran_cicilan; $i++) {
        $logcicilan->insert([
            'u_id' => $u_id,
            'c_id' => $c_id,
            'l_jumlah_bayar' => $l_jumlah_bayar,
            'l_jumlah_pembayaran_cicilan' => $jumlah_pembayaran_cicilan,
            'l_foto' => $nama_file
        ]);
        // }
        $foto->move('foto-bukti-pembayaran', $nama_file);
        session()->setFlashdata('success', 'Data Berhasil Disimpan!');
        return redirect()->to('/admin/datatransaksi/logcicilan');
    }
    public function listdatalogcicilan()
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        // $logcicilan = new LogCicilanModels();
        $logcicilan = new LogCicilanSementaraModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataKategoriPaket' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataPackingBarang' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => '',
            'DataPaketBarang' => '',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => 'datatransaksilogcicilan',
            'DataUser' => $logcicilan->datauser(),
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),
        ];
        $data['tb_log_cicilan'] = $logcicilan->findAll();
        echo view('admin/DataTransaksi/TransaksiLogCicilan/datalogcicilan', $data);
        //berdasarkan login
        // $user = new UsersModels();
        // $data['tb_user'] = $user->where('u_referensi', session('u_id'))->findAll();
        // echo view('admin/datauser', $data);
    }
    public function editapprovedlogcicilan($l_id)
    {
        $paketbarang = new PaketBarangModels();
        $transaksi = new TransaksiModels();
        $logcicilan = new LogCicilanModels();
        $logcicilansementara = new LogCicilanSementaraModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataPackingBarang' => '',
            'DataPaketBarang' => '',
            'DataKategoriPaket' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => 'datatransaksi',
            'DataPaket' => $transaksi->datapaket(),
            'DataUser' => $transaksi->datauser(),
            'payperiode' => $paketbarang->datapayperiode(),
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',


        ];
        $current_time = time();
        if ($l_id != null) {
            $logcicilansementara->update($l_id, [
                'l_approval_by' => session()->get('u_id'),
                'l_approval_date' => $current_time
            ]);
            // session()->setFlashdata('success', 'Data Berhasil Di Setujui!');
            $datalogcicilansementara = $transaksi->datalogcicilansementara_by_id($l_id);
            foreach ($datalogcicilansementara as $datadatalogcicilan_id) {
                $u_id = $datadatalogcicilan_id['u_id'];
                $c_id  = $datadatalogcicilan_id['c_id'];
                $l_jumlah_bayar = $datadatalogcicilan_id['l_jumlah_bayar'];
                $l_jumlah_pembayaran_cicilan = $datadatalogcicilan_id['l_jumlah_pembayaran_cicilan'];
                $l_foto = $datadatalogcicilan_id['l_foto'];
            }
            // Validasi nilai variabel
            if (!is_numeric($l_jumlah_bayar)) {
                echo "Input tidak valid!";
                exit;
            }
            $logcicilan->insert([
                'u_id' => $u_id,
                'c_id' => $c_id,
                'l_jumlah_bayar' => $l_jumlah_bayar,
                'l_jumlah_pembayaran_cicilan' => $l_jumlah_pembayaran_cicilan,
                'l_foto' => $l_foto,
                'l_approval_by' => session()->get('u_id'),
                'l_approval_date' => $current_time
                // 't_status' => $this->request->getVar('t_status')
            ]);
            return redirect()->back();
        }
        return redirect()->back();
    }
    public function editnoapprovedlogcicilan($l_id)
    {
        $paketbarang = new PaketBarangModels();
        $transaksi = new TransaksiModels();
        $logcicilan = new LogCicilanModels();
        $logcicilansementara = new LogCicilanSementaraModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataPackingBarang' => '',
            'DataKategoriPaket' => '',
            'DataPaketBarang' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => 'datatransaksi',
            'DataPaket' => $transaksi->datapaket(),
            'DataUser' => $transaksi->datauser(),
            'payperiode' => $paketbarang->datapayperiode(),
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => '',

        ];
        $data['tb_transaksi'] = $transaksi->findAll();
        $current_time = time();
        if ($l_id != null) {
            // $logcicilan->update($l_id, [
            //     'l_approval_by' => null,
            //     'l_approval_date' => $current_time
            // ]);
            $logcicilansementara->update($l_id, [
                'l_approval_by' => null,
                'l_approval_date' => $current_time
            ]);
            // session()->setFlashdata('success', 'Data Berhasil Di Setujui!');
            $datalogcicilansementara = $transaksi->datalogcicilansementara_by_id($l_id);
            foreach ($datalogcicilansementara as $datadatalogcicilan_id) {
                $l_id = $datadatalogcicilan_id['l_id'];
            }
            // $datacicilan = $cicilan->get_cicilanby_t_id($t_id);
            $datalogcicilan = $transaksi->datalogcicilan_by_id($l_id);
            foreach ($datalogcicilan as $value => $data) {
                if ($l_id == $data['l_id']) {
                    $l_id1 = (int)$data['l_id'];
                }
                if ($l_id1 != null) {
                    $logcicilan->deletelogcicilan($l_id);
                }
            }


            // print_r('data transaksi : ' . 'uid' . $u_id . $p_id . $pe_id . '<br>' . 'data cicilan : ' . $u_id1 . $p_id1 . $pe_id1);
            // session()->setFlashdata('success', 'Data Berhasil Di Setujui!');
            return redirect()->back();
        }
        return redirect()->back();
    }
    public function editlogcicilan($l_id)
    {
        $UsersModels = new UsersModels();
        $transaksi = new TransaksiModels();
        $logcicilan = new LogCicilanModels();
        // $logcicilan = new LogCicilanModels();
        $data = [
            'AdminDashboard' => '',
            'RegisterUser' => '',
            'RegisterSupplier' => '',
            'DataKategoriBarang' => '',
            'DataBarang' => '',
            'DataBarangSupplier' => '',
            'MenuDataBarang' => '',
            'DataPackingBarang' => '',
            'DataPaketBarang' => '',
            'DataKategoriPaket' => '',
            'MenuDataTransaksi' => 'menudatatransaksi',
            'DataPeriodeTransaksi' => '',
            'DataTransaksi' => '',
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => 'datatransaksilogcicilan',
            'DataUser' => $logcicilan->datauser(),
            'NotipDataTransaksi' => $transaksi->get_datatransaksi(),
            'NotipDataLogcicilan' => $transaksi->get_datalogcicilan(),
            'NotipDataLogcicilansementara' => $transaksi->get_datalogcicilan_sementara(),
            'NotipDatacicilan' => $transaksi->get_datacicilan(),
            'NotipDataPeriode' => $transaksi->get_dataperiode(),
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),
        ];
        // ambil artikel yang akan diedit

        $data['tb_log_cicilan'] = $logcicilan->where('l_id', $l_id)->first();

        if ($this->validate([
            'u_id' => 'required',
            'l_jumlah_bayar' => 'required',
            'l_foto' => [
                'rules' => 'max_size[l_foto,1024]|mime_in[l_foto,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                'errors' => [
                    'uploaded' => '{field} Wajib diisi!',
                    'max_size' => 'Ukuran {field} Maksimal 1024 KB ',
                    'mime_in' => 'Format {field} harus JPG/JPEG/PNG!',
                ]
            ],
        ])) {
            $l_jumlah_bayar = floatval(str_replace(",", "", $this->request->getVar('l_jumlah_bayar')));
            // Validasi nilai variabel
            if (!is_numeric($l_jumlah_bayar)) {
                echo "Input tidak valid!";
                exit;
            }
            $foto = $this->request->getFile('l_foto');
            $prefoto = $this->request->getVar('preview');
            if ($foto->getError() == 4) {
                $nama_file = $prefoto;
            } else {
                $nama_file = $foto->getRandomName();
                if ($prefoto != '') {
                    unlink('foto-bukti-pembayaran/' . $prefoto);
                }
                $foto->move('foto-bukti-pembayaran', $nama_file);
            }
            $logcicilan->update($l_id, [
                'u_id' => $this->request->getVar('u_id'),
                'l_jumlah_bayar' =>  $l_jumlah_bayar,
                'l_approval_by' => session()->get('u_id'),
                'l_foto' => $nama_file
            ]);
            session()->setFlashdata('success', 'Data Berhasil Di Edit!');
            return redirect('admin/datatransaksi/datalogcicilan');
        } else {
            // session()->setFlashdata('error', $this->validator->listErrors());
        }
        echo view('admin/DataTransaksi/TransaksiLogCicilan/logcicilanedit', $data);
    }
    public function deletelogcicilan($l_id)
    {
        // $logcicilan = new LogCicilanModels();
        $logcicilan = new LogCicilanSementaraModels();
        $logcicilanfoto = $logcicilan->datalogcicilan($l_id);
        if ($logcicilanfoto['l_foto'] == '') {
        } else {
            unlink('foto-bukti-pembayaran/' . $logcicilanfoto['l_foto']);
        }
        $logcicilan->delete($l_id);
        session()->setFlashdata('success', 'Data Berhasil Di Hapus!');
        return redirect('admin/datatransaksi/datalogcicilan');
    }
    // public function ImportFileExcellogcicilan()
    // {
    //     $logcicilan = new LogCicilanModels();
    //     $transaksi = new TransaksiModels();
    //     $datapaket = new PaketBarangModels();
    //     $cicilan = new CicilanModels();
    //     $file = $this->request->getFile('file');
    //     $extension = $file->getClientExtension();
    //     if ($extension == 'xlsx' || $extension == 'xls') {
    //         if ($extension == 'xls') {
    //             $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    //         } else {
    //             $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    //         }
    //         $spreadsheet = $reader->load($file);
    //         $kategori = $spreadsheet->getActiveSheet()->toArray();
    //         foreach ($kategori as $key => $value) {
    //             if ($key == 0) {
    //                 continue;
    //             }
    //             $users = new UsersModels();
    //             $datausers = $users->findAll();
    //             $datacicilan = $cicilan->findAll();
    //             foreach ($datausers as $setusers) {
    //                 if ($value[1] == $setusers['u_nama']) {
    //                     $u_id = $setusers['u_id'];
    //                 }
    //             }
    //             foreach ($datacicilan as $setcicilan) {
    //                 foreach ($transaksi as $tb_transaksi) {
    //                     foreach ($datapaket as $tb_paket) {
    //                         if ($tb_transaksi['p_id'] == $tb_paket['p_id']) {
    //                             $tampil = $tb_paket['p_nama'];
    //                             $qty = $tb_transaksi['t_qty'];
    //                         }
    //                     }
    //                 }
    //                 if ($value[2] == $setcicilan['u_nama']) {
    //                     $u_id = $setcicilan['u_id'];
    //                 }
    //             }

    //             $data = [
    //                 'l_id' => $logcicilan->getInsertID(),
    //                 'u_id' => $u_id,
    //                 'c_id' => $u_id,
    //                 'l_jumlah_bayar' => $value[2],

    //             ];
    //             $logcicilan->insert($data);
    //         }
    //         session()->setFlashdata('success', 'Data Berhasil Diimport!');
    //         return redirect('admin/datatransaksi/datalogcicilan');
    //     } else {
    //         return redirect()->back()->with('message', 'Format File Tidak Sesuai! | Extension file harus .xls atau .xlsx');
    //     }
    // }
    public function ExportDataExcellogcicilan()
    {
        $logcicilan = new logcicilanModels();
        $datalogcicilan = $logcicilan->findAll();
        $spreadsheet = new Spreadsheet();
        $colomheader = $spreadsheet->getActiveSheet();
        $colomheader->setCellValue('A1', 'No');
        $colomheader->setCellValue('B1', 'Nama Pengambil Paket');
        $colomheader->setCellValue('C1', 'Referensi Pembayaran Cicilan');
        $colomheader->setCellValue('D1', 'Jumlah Bayar Cicilan');
        $colomheader->setCellValue('E1', 'Waktu Pembayaran Cicilan');
        $colomheader->setCellValue('F1', 'Bukti Pembayaran Cicilan');

        $users = new UsersModels();
        $datausers = $users->findAll();
        $colomdata = 2;
        foreach ($datalogcicilan as $setlogcicilan) {
            $colomheader->setCellValue('A' . $colomdata, ($colomdata - 1));
            foreach ($datausers as $data) {
                if ($setlogcicilan['u_id'] == $data['u_id']) {
                    $namapengambil = $data['u_nama'];
                    $colomheader->setCellValue('B' . $colomdata, $namapengambil);
                    $referensi = $data['u_referensi'];
                }
            }
            foreach ($datausers as $data) {
                if ($referensi == $data['u_id']) {
                    $colomheader->setCellValue('C' . $colomdata, $data['u_nama']);
                }
            }
            $colomheader->setCellValue('D' . $colomdata, $setlogcicilan['l_jumlah_bayar']);
            $colomheader->setCellValue('E' . $colomdata, $setlogcicilan['created_at']);
            // $colomheader->setCellValue('F' . $colomdata, $setlogcicilan['created_at']);
            $colomdata++;
        }
        $colomheader->getStyle('A1:F1')->getFont()->setBold(true);
        $colomheader->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $colomheader->getStyle('A1:F' . ($colomdata - 1))->applyFromArray($styleArray);

        $colomheader->getColumnDimension('A')->setAutoSize(true);
        $colomheader->getColumnDimension('B')->setAutoSize(true);
        $colomheader->getColumnDimension('C')->setAutoSize(true);
        $colomheader->getColumnDimension('D')->setAutoSize(true);
        $colomheader->getColumnDimension('E')->setAutoSize(true);
        $colomheader->getColumnDimension('F')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Export-Data-LogCicilan.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
}
