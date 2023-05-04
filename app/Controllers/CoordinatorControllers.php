<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModels;
use App\Models\PeriodePembayaranModels;
use App\Models\PaketBarangModels;
use App\Models\KabupatenModels;
use App\Models\KecamatanModels;
use App\Models\ProvinsiModels;
use App\Models\TransaksiModels;
use App\Models\CicilanModels;
use App\Models\LogCicilanModels;
use App\Models\LogCicilanSementaraModels;
use App\Models\PengambilanPaketBarangModels;
use App\Models\PengambilanTransaksiPaketBarangModels;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class CoordinatorControllers extends BaseController
{
    public function __construct()
    {
        if (session()->get('u_role') != "coordinator") {
            echo 'Access denied';
            exit;
        }
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
        $tb_cicilan = $cicilanModels->findAll();
        $pengambilanpaketbarang = new PengambilanPaketBarangModels();
        $u_id = $this->request->getPost('u_id');
        $kab = $cicilanModels->get_cicilan($u_id);
        $Datatransaksi = new TransaksiModels();
        $transaksi = $cicilanModels->paketcicilan($u_id);
        $datapaket = $cicilanModels->datapaket();
        echo '<option value="">--Pilih Paket Cicilan--</option>';
        foreach ($kab as $tb_c) {
            $tampil = '';
            foreach ($transaksi as $tb_transaksi) {
                if ($tb_transaksi['t_id'] == $tb_c['t_id']) {
                    $status = $tb_c['c_biaya_outstanding'];
                    if ($status != 0) {
                        $p_id = $tb_transaksi['p_id'];
                        foreach ($datapaket as $tb_paket) {
                            if ($p_id == $tb_paket['p_id']) {
                                $tampil .= $tb_paket['p_nama'];
                                $qty = $tb_transaksi['t_qty'];
                            }
                        }
                    }
                }
            }
            if (!empty($tampil)) {
                echo "<option value=" . $tb_c['c_id'] . ">" . rtrim($tampil, ", ") . " - Jumlah Paket :" . $qty . "</option>";
            }
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
            // 'countdatauser' => $UsersModels->countAllResults(),
        ];
        return view('coordinator/dashboard', $menu);
    }
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
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        helper(['form', 'url']);
        $data['nameuser'] = $UsersModels->getuserreferensicoordinator();
        return view('coordinator/DatabaseUser/registeruser', $data);
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
        $u_id = session('u_id');
        $users->insert([
            'u_username' => $this->request->getVar('u_username'),
            'u_password' => password_hash($this->request->getVar('u_password'), PASSWORD_BCRYPT),
            'u_fullname' => $this->request->getVar('u_fullname'),
            'u_role' => $this->request->getVar('u_role'),
            'u_referensi' => $u_id,
            'u_email' => $this->request->getVar('u_email'),
            'u_create_at' => $this->request->getVar('u_create_at'),
            'u_nik' => $this->request->getVar('u_nik'),
            'u_nama' => $this->request->getVar('u_nama'),
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
        return redirect()->to('/coordinator/databaseuser/registeruser');
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
            'NotipDataUser' => $UsersModels->findAll(),
            'NotipDataPaket' => $transaksi->datapaket(),

        ];
        $data['tb_user'] = $UsersModels->where('u_referensi', session('u_id'))->findAll();
        echo view('coordinator/DatabaseUser/datauser', $data);
        // helper(['form', 'url']);
        // $UsersModels = new UsersModels();
        // $dataa['nameuser'] = $UsersModels->getuserreferensiadmin();
        // return view('coordinator/datauser', $dataa);
        //berdasarkan login
        // $user = new UsersModels();
        // $data['tb_user'] = $user->where('u_referensi', session('u_id'))->findAll();
        // echo view('coordinator/datauser', $data);
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
        $u_idreferensi = session()->get('u_id');
        if ($isDataValid) {
            $UsersModels->update($u_id, [
                'u_username' => $this->request->getVar('u_username'),
                'u_password' => password_hash($this->request->getVar('u_password'), PASSWORD_BCRYPT),
                'u_fullname' => $this->request->getVar('u_fullname'),
                'u_role' => $this->request->getVar('u_role'),
                'u_referensi' => $u_idreferensi,
                'u_email' => $this->request->getVar('u_email'),
                'u_create_at' => $this->request->getVar('u_create_at'),
                'u_nik' => $this->request->getVar('u_nik'),
                'u_nama' => $this->request->getVar('u_nama'),
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
            return redirect('coordinator/databaseuser/datauser');
        }

        // tampilkan form edit
        // helper(['form', 'url']);
        // $UsersModels = new UsersModels();
        // $data['tb_user'] = $UsersModels->getuserreferensiadmin();
        echo view('coordinator/DatabaseUser/registeredituser', $data);
    }
    public function deleteuser($u_id)
    {
        $user = new UsersModels();
        try {
            $result = $user->delete($u_id);
            if ($result) {
                session()->setFlashdata('success', 'Data Berhasil Di Hapus!');
            } else {
                session()->setFlashdata('error', 'Data Tidak Berhasil Di Hapus!');
            }
        } catch (\mysqli_sql_exception $e) {
            session()->setFlashdata('error', 'Data Tidak Berhasil Di Hapus! Karena Data ini kemungkinan sudah dipakai di:
            <br>1. <b>Data User</b> Sebagai referensi <b>Anggota</b>!
            <br>2. <b>Data Transaksi Paket</b> sebagai identitas yang mengambil Paket!
            <br>3. <b>Data Transaksi Cicilan</b> sebagai identitas yang mengambil Paket Cicilan!
            <br>4. <b>Data Transaksi Log Cicilan</b> sebagai identitas yang membayar Paket Cicilan!
            <br>Silahkan Cek Data user yang kemungkinan sudah dipakai di <b>4 form diatas</b>, jika ada maka hapus terlebih dahulu dan apabila sudah tidak ada maka Data User yang mau dihapus dapat dilakukan!');
        }
        return redirect('coordinator/databaseuser/datauser');
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
                foreach ($dataprovinsi as $datapr) {
                    if (isset($value[12]) && strtoupper($value[12]) == $datapr['nama_provinsi']) {
                        $id_provinsi = $datapr['id_provinsi'];
                    }
                    foreach ($datakabupaten as $datakb) {
                        if (isset($value[13]) && strtoupper($value[13]) == $datakb['nama_kabupaten']) {
                            $id_kabupaten = $datakb['id_kabupaten'];
                        }
                    }
                    foreach ($datakecamatan as $datakc) {
                        if (isset($value[15]) && strtoupper($value[15]) == $datakc['nama_kecamatan']) {
                            $id_kecamatan = $datakc['id_kecamatan'];
                        }
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
                    'u_nik' => isset($value[7]) ? $value[7] : '',
                    'u_nama' => isset($value[8]) ? strtoupper($value[8]) : '',
                    'u_tempat_lahir' => isset($value[9]) ? strtoupper($value[9]) : '',
                    'u_tanggal_lahir' => isset($value[10]) ? $value[10] : '',
                    'u_jenis_kelamin' => isset($value[11]) ? $value[11] : '',
                    'u_provinsi' => $id_provinsi,
                    'u_kota' => $id_kabupaten,
                    'u_kelurahan' => isset($value[14]) ? strtoupper($value[14]) : '',
                    'u_kecamatan' => $id_kecamatan,
                    'u_kodepos' => isset($value[16]) ? $value[16] : '',

                ];
                $user->insert($data);
            }
            session()->setFlashdata('success', 'Data Berhasil Diimport!');
            return redirect('coordinator/databaseuser/datauser');
        } else {
            return redirect()->back()->with('message', 'Format File Tidak Sesuai! | Extension file harus .xls atau .xlsx');
        }
    }
    public function ExportDataExcelUser()
    {
        $user = new UsersModels();
        $datausers = $user->where('u_referensi', session('u_id'))->findAll();
        $datauser = $user->findAll();
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
        // $colomheader->setCellValue('C1', 'Password');
        $colomheader->setCellValue('C1', 'Full Name');
        $colomheader->setCellValue('D1', 'Role Akses');
        $colomheader->setCellValue('E1', 'Nama Refernsi');
        $colomheader->setCellValue('F1', 'Email');
        $colomheader->setCellValue('G1', 'Data Dibuat');
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
        $password = null;
        $colomdata = 2;
        foreach ($datausers as $setuser) {
            $colomheader->setCellValue('A' . $colomdata, ($colomdata - 1));
            $colomheader->setCellValue('B' . $colomdata, $setuser['u_username']);
            // $colomheader->setCellValue('C' . $colomdata, $password);
            $colomheader->setCellValue('C' . $colomdata, $setuser['u_fullname']);
            $colomheader->setCellValue('D' . $colomdata, $setuser['u_role']);
            $referensi = $setuser['u_referensi'];
            $id_provinsi = $setuser['u_provinsi'];
            $id_kabupaten = $setuser['u_kota'];
            $id_kecamatan = $setuser['u_kecamatan'];
            foreach ($datauser as $data) {
                if ($referensi == $data['u_id']) {
                    $colomheader->setCellValue('E' . $colomdata, $data['u_nama']);
                }
            }
            $colomheader->setCellValue('F' . $colomdata, $setuser['u_email']);
            $colomheader->setCellValue('G' . $colomdata, $setuser['u_create_at']);
            $colomheader->setCellValue('H' . $colomdata, $setuser['u_nik']);
            $colomheader->setCellValue('I' . $colomdata, $setuser['u_nama']);
            $colomheader->setCellValue('J' . $colomdata, $setuser['u_tempat_lahir']);
            $colomheader->setCellValue('K' . $colomdata, $setuser['u_tanggal_lahir']);
            $colomheader->setCellValue('L' . $colomdata, $setuser['u_jenis_kelamin']);
            foreach ($dataprovinsi as $data) {
                if ($id_provinsi == $data['id_provinsi']) {
                    $colomheader->setCellValue('M' . $colomdata, $data['nama_provinsi']);
                }
            }
            foreach ($datakabupaten as $data) {
                if ($id_kabupaten == $data['id_kabupaten']) {
                    $colomheader->setCellValue('N' . $colomdata, $data['nama_kabupaten']);
                }
            }
            foreach ($datakecamatan as $data) {
                if ($id_kecamatan == $data['id_kecamatan']) {
                    $colomheader->setCellValue('O' . $colomdata, $data['nama_kecamatan']);
                }
            }
            $colomheader->setCellValue('P' . $colomdata, $setuser['u_kelurahan']);
            $colomheader->setCellValue('Q' . $colomdata, $setuser['u_kodepos']);
            $colomdata++;
        }
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
        $colomheader->getStyle('A1:Q' . ($colomdata - 1))->applyFromArray($styleArray);

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

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheet1.sheet');
        header('Content-Disposition: attachment;filename=Export-Data-User.xlsx');
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
    //--------------------------------------------------------------------------
    //Data Transaksi Pembayaran
    public function transaksi()
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


        ];
        return view('coordinator/DataTransaksi/TransaksiPaket/transaksi', $data);
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
        $u_id = $this->request->getVar('u_id');
        $p_id = $this->request->getVar('p_id');
        $pe_id = $this->request->getVar('pe_id');
        $qty = $this->request->getVar('t_qty');
        $hargapaket = 0;
        $paketbarang = new PaketBarangModels();
        $datapaket = $paketbarang->findAll();
        foreach ($datapaket as $tb_paket) {
            if ($p_id == $tb_paket['p_id']) {
                $hargapaket = $tb_paket['p_hargaJual'];
            }
        }
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
        $datapengambilanpaket = new PengambilanPaketBarangModels();
        $PTPB = new PengambilanTransaksiPaketBarangModels();
        $datapengambilanpaket = $datapengambilanpaket->findAll();
        for ($i = 0; $i < $qty; $i++) {
            foreach ($datapengambilanpaket as $tb_pengambilan_paket) {
                if ($p_id == $tb_pengambilan_paket['pp_p_id']) {
                    $pp_p_id = $tb_pengambilan_paket['pp_p_id'];
                    $pp_ib_id = $tb_pengambilan_paket['pp_ib_id'];
                    $pp_qty = $tb_pengambilan_paket['pp_qty'];
                    $pp_ktrg_berat_ukuran = $tb_pengambilan_paket['pp_ktrg_berat_ukuran'];
                    $PTPB->insert([
                        'pp_t_id' => $transaksi->getInsertID(),
                        'pp_p_id' => $pp_p_id,
                        'pp_ib_id' => $pp_ib_id,
                        'pp_qty' => $pp_qty,
                        'pp_ktrg_berat_ukuran' => $pp_ktrg_berat_ukuran
                    ]);
                }
            }
        }
        session()->setFlashdata('success', 'Data Berhasil Disimpan!');
        return redirect()->to('/coordinator/datatransaksi/transaksi');
    }
    public function listdatatransaksi()
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


        ];
        $data['tb_transaksi'] = $transaksi->findAll();
        echo view('coordinator/DataTransaksi/TransaksiPaket/datatransaksi', $data);
        //berdasarkan login
        // $user = new UsersModels();
        // $data['tb_user'] = $user->where('u_referensi', session('u_id'))->findAll();
        // echo view('coordinator/datauser', $data);
    }
    public function editapprovedtransaksi($p_id)
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
        if ($p_id != null) {
            $transaksi->update($p_id, [
                't_approval_by' => session()->get('u_id')
            ]);
            session()->setFlashdata('success', 'Data Berhasil Di Setujui!');
            return redirect('coordinator/datatransaksi/datatransaksi');
        }
        echo view('coordinator/DataTransaksi/TransaksiPaket/datatransaksi', $data);
    }
    public function editnoapprovedtransaksi($p_id)
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
        if ($p_id != null) {
            $transaksi->update($p_id, [
                't_approval_by' => session()->get('u_id')
            ]);
            session()->setFlashdata('success', 'Data Berhasil Di Setujui!');
            return redirect('coordinator/datatransaksi/datatransaksi');
        }
        echo view('coordinator/DataTransaksi/TransaksiPaket/datatransaksi', $data);
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
            $u_id = $this->request->getVar('u_id');
            $pe_id = $this->request->getVar('pe_id');
            $p_id = $this->request->getVar('p_id');
            $datapaket = $paketbarang->findAll();
            foreach ($datapaket as $tb_paket) {
                if ($p_id == $tb_paket['p_id']) {
                    $hargapaket = $tb_paket['p_hargaJual'];
                }
            }
            $total = $hargapaket * $qty;
            $transaksi->update($t_id, [
                'u_id' => $u_id,
                'p_id' => $p_id,
                'pe_id' => $pe_id,
                't_qty' => $qty,
                't_totalharga' => $total,
                // 't_status' => $this->request->getVar('t_status')
            ]);
            $cicilan = new CicilanModels();
            $datapengambilanpaket = new PengambilanPaketBarangModels();
            $PTPB = new PengambilanTransaksiPaketBarangModels();
            $cicilan->deletetransaksi_pengambilanpaket($t_id);
            $datapengambilanpaket = $datapengambilanpaket->findAll();
            for ($i = 0; $i < $qty; $i++) {
                foreach ($datapengambilanpaket as $tb_pengambilan_paket) {
                    if ($p_id == $tb_pengambilan_paket['pp_p_id']) {
                        $pp_p_id = $tb_pengambilan_paket['pp_p_id'];
                        $pp_ib_id = $tb_pengambilan_paket['pp_ib_id'];
                        $pp_qty = $tb_pengambilan_paket['pp_qty'];
                        $pp_ktrg_berat_ukuran = $tb_pengambilan_paket['pp_ktrg_berat_ukuran'];
                        $PTPB->insert([
                            'pp_t_id' => $transaksi->getInsertID(),
                            'pp_p_id' => $pp_p_id,
                            'pp_ib_id' => $pp_ib_id,
                            'pp_qty' => $pp_qty,
                            'pp_ktrg_berat_ukuran' => $pp_ktrg_berat_ukuran
                        ]);
                    }
                }
            }
            $datapaket = $transaksi->HargaPaket($p_id);
            foreach ($datapaket as $value => $k) {
                $hargajual = $k['p_hargaJual'];
            }
            $dataperiode = $transaksi->dataperiodeby_id($pe_id);
            foreach ($dataperiode as $value => $pay) {
                $pe_periode = $pay['pe_periode'];
            }
            $number = 0;
            if ($transaksi->where('t_id') != null) {
                $cicilan->deletecicilan($t_id);
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
            }
            session()->setFlashdata('success', 'Data Berhasil Di Edit!');
            return redirect('coordinator/datatransaksi/datatransaksi');
        }
        echo view('coordinator/DataTransaksi/TransaksiPaket/transaksiedit', $data);
    }
    public function deletetransaksi($t_id)
    {
        $transaksi = new TransaksiModels();
        // $transaksi->deletetransaksicicilan($t_id);
        try {
            $result = $transaksi->delete($t_id);
            // $result_delete = $transaksi->deletetransaksipengambilanpeket($t_id);
            // if ($result_delete) {
            if ($result) {
                $transaksi->deletetransaksipengambilanpeket($t_id);
                session()->setFlashdata('success', 'Data Berhasil Di Hapus!');
            } else {
                session()->setFlashdata('error', 'Data Tidak Berhasil Di Hapus!');
            }
            // }
        } catch (\mysqli_sql_exception $e) {
            session()->setFlashdata('error', 'Data Tidak Berhasil Di Hapus! Karena Data ini kemungkinan sudah dipakai di:
            <br>1. <b>Data Transaksi Cicilan</b> Sebagai <b>Data Cicilan Pembayaran Paket</b>!
            <br>2. <b>Data Transaksi Log Cicilan</b> sebagai <b>Data Untuk Pembayaran Setiap Periode Cicilan</b>!');
        }
        return redirect('coordinator/datatransaksi/datatransaksi');
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
            return redirect('coordinator/datatransaksi/datatransaksi');
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


        ];
        return view('coordinator/DataTransaksi/TransaksiCicilan/cicilan', $data);
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
        return redirect()->to('/coordinator/datatransaksi/cicilan');
    }
    public function listdatacicilan()
    {
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

        ];
        $data['tb_cicilan'] = $cicilan->findAll();
        echo view('coordinator/DataTransaksi/TransaksiCicilan/datacicilan', $data);
        //berdasarkan login
        // $user = new UsersModels();
        // $data['tb_user'] = $user->where('u_referensi', session('u_id'))->findAll();
        // echo view('coordinator/datauser', $data);
    }
    public function editcicilan($c_id)
    {
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
            return redirect('coordinator/datatransaksi/datacicilan');
        }
        echo view('coordinator/DataTransaksi/TransaksiCicilan/cicilanedit', $data);
    }
    public function deletecicilan($c_id)
    {
        $cicilan = new CicilanModels();
        $transaksi = new TransaksiModels();
        $datacicilan = $cicilan->findAll();
        $jumlahby_t_id = [];
        foreach ($datacicilan as $tb_cicilan) {
            if ($c_id == $tb_cicilan['c_id']) {
                $databy_t_id = $tb_cicilan['t_id'];
            }
            $databy_c_id = $c_id;
        }
        try {
            $result = $cicilan->delete($databy_c_id);
            if ($result) {
                $datacicilan_by_id = $cicilan->where('t_id', $databy_t_id)->findAll();
                foreach ($datacicilan_by_id as $tb_cicilan_byid) {
                    $jumlahby_t_id[] = $tb_cicilan_byid['t_id'];
                }
                $jumlah_by_c_id = count($jumlahby_t_id);
                if ($jumlah_by_c_id == 0) {
                    $transaksi->update($databy_t_id, [
                        't_approval_by' => null
                    ]);
                }
                session()->setFlashdata('success', 'Data Berhasil Di Hapus!');
            } else {
                session()->setFlashdata('error', 'Data Tidak Berhasil Di Hapus!');
            }
        } catch (\mysqli_sql_exception $e) {
            session()->setFlashdata('error', 'Data Tidak Berhasil Di Hapus! Karena Data ini kemungkinan sudah dipakai di <b>Data Transaksi Log Cicilan</b> sebagai <b>Data Untuk Pembayaran Setiap Periode Cicilan</b>!');
        }
        return redirect('coordinator/datatransaksi/datacicilan');
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
            return redirect('coordinator/datacicilan/datacicilan');
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
        $logcicilan = new LogCicilanModels();
        $transaksi = new TransaksiModels();
        $cicilanModels = new CicilanModels();
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
            'DataTransaksiCicilan' => '',
            'DataTransaksiLogCicilan' => 'datatransaksilogcicilan',
            'DataUser' => $logcicilan->datauser(),
            'DataPaket' => $transaksi->datapaket(),
            'DataTransaksi' => $cicilanModels->datatransaksi(),
            'DataCicilan' => $cicilanModels->findAll(),


        ];
        return view('coordinator/DataTransaksi/TransaksiLogCicilan/logcicilan', $data);
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
        // $jumlah_pembayaran_cicilan = $l_jumlah_bayar / ($p_setoran * $t_qty);
        $harga_bayar_cicilan = $l_jumlah_bayar / $jumlah_pembayaran_cicilan;
        $totalbayar = $l_jumlah_bayar / $t_qty;
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
        return redirect()->to('/coordinator/datatransaksi/logcicilan');
    }
    public function listdatalogcicilan()
    {
        $transaksi = new TransaksiModels();
        $logcicilan = new LogCicilanSementaraModels();
        $UsersModels = new UsersModels();
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
        echo view('coordinator/DataTransaksi/TransaksiLogCicilan/datalogcicilan', $data);
        //berdasarkan login
        // $user = new UsersModels();
        // $data['tb_user'] = $user->where('u_referensi', session('u_id'))->findAll();
        // echo view('coordinator/datauser', $data);
    }
    public function editlogcicilan($l_id)
    {
        $logcicilan = new LogCicilanModels();
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

        ];
        // ambil artikel yang akan diedit

        $data['tb_log_cicilan'] = $logcicilan->where('l_id', $l_id)->first();

        if ($this->validate([
            // 'u_id' => 'required',
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
            $u_id = session()->get('u_id');
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
                'u_id' => $u_id,
                'l_jumlah_bayar' =>  $l_jumlah_bayar,
                'l_approval_by' => session()->get('u_id'),
                'l_foto' => $nama_file
            ]);
            session()->setFlashdata('success', 'Data Berhasil Di Edit!');
            return redirect('coordinator/datatransaksi/datalogcicilan');
        } else {
            // session()->setFlashdata('error', $this->validator->listErrors());
        }
        echo view('coordinator/DataTransaksi/TransaksiLogCicilan/logcicilanedit', $data);
    }
    public function deletelogcicilan($l_id)
    {
        $logcicilan = new LogCicilanModels();
        $logsementaracicilan = new LogCicilanSementaraModels();
        // $logcicilanfoto = $logcicilan->datalogcicilan($l_id);
        $logcicilan->deletelogcicilan_l_id_sementara($l_id);
        $logcicilanfotosementara = $logsementaracicilan->datalogcicilan($l_id);
        if (!empty($logcicilanfotosementara['l_foto'])) {
            unlink('foto-bukti-pembayaran/' . $logcicilanfotosementara['l_foto']);
        }
        $logsementaracicilan->delete($l_id);
        session()->setFlashdata('success', 'Data Berhasil Di Hapus!');
        return redirect('coordinator/datatransaksi/datalogcicilan');
    }
    // public function ImportFileExcellogcicilan()
    // {
    //     $logcicilan = new LogCicilanModels();
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
    //             foreach ($datausers as $setusers) {
    //                 if ($value[1] == $setusers['u_nama']) {
    //                     $user = $setusers['u_id'];
    //                 }
    //                 if ($value[3] == $setusers['u_nama']) {
    //                     $approveuser = $setusers['u_id'];
    //                 }
    //             }

    //             $data = [
    //                 'l_id' => $value[0],
    //                 'u_id' => $user,
    //                 'l_jumlah_bayar' => $value[2],
    //                 'l_approval_by' => $approveuser,

    //             ];
    //             $logcicilan->insert($data);
    //         }
    //         session()->setFlashdata('success', 'Data Berhasil Diimport!');
    //         return redirect('coordinator/datatransaksi/datalogcicilan');
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
        $colomheader->setCellValue('C1', 'Jumlah Bayar Cicilan');
        $colomheader->setCellValue('D1', 'Waktu Pembayaran Cicilan');
        $colomheader->setCellValue('E1', 'Pembayaran Cicilan Kepada');

        $users = new UsersModels();
        $datausers = $users->findAll();
        $colomdata = 2;
        foreach ($datalogcicilan as $setlogcicilan) {
            $colomheader->setCellValue('A' . $colomdata, ($colomdata - 1));
            foreach ($datausers as $setusers) {
                if ($setlogcicilan['u_id'] == $setusers['u_id']) {
                    $colomheader->setCellValue('B' . $colomdata, $setusers['u_nama']);
                }
                if ($setlogcicilan['l_approval_by'] == $setusers['u_id']) {
                    $colomheader->setCellValue('D' . $colomdata, $setusers['u_nama']);
                }
            }
            $colomheader->setCellValue('C' . $colomdata, $setlogcicilan['l_jumlah_bayar']);
            $colomheader->setCellValue('E' . $colomdata, $setlogcicilan['created_at']);
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
        header('Content-Disposition: attachment;filename=Export-Data-LogCicilan.xlsx');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
