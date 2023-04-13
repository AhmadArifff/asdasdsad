<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModels;

class LoginControllers extends BaseController
{
    public function index()
    {
        if (session()->get('u_role') == "admin") {
            return redirect()->to('admin/dashboard');
        } else if (session()->get('u_role') == "coordinator") {
            return redirect()->to('coordinator/dashboard');
        }
        // else if (session()->get('u_role') == "owner") {
        //     return redirect()->to('owner/dashboard');
        // } else if (session()->get('u_role') == "anggota") {
        //     return redirect()->to('anggota/dashboard');
        // }
        return view('login');
    }
    // public function login()
    // {
    //     $data = [];

    //     if ($this->request->getMethod() == 'post') {

    //         $rules = [
    //             'u_username' => 'required|min_length[4]|max_length[50]',
    //             'u_password' => 'required|min_length[4]|max_length[255]|validateUser[u_username,u_password]',
    //         ];

    //         $errors = [
    //             'u_password' => [
    //                 'validateUser' => "username dan Password Salah",
    //             ],
    //         ];

    //         if (!$this->validate($rules, $errors)) {

    //             return view('login', [
    //                 "validation" => $this->validator,
    //             ]);
    //         } else {
    //             $model = new UsersModels();

    //             $user = $model->where('u_username', $this->request->getVar('u_username'))->first();

    //             // Stroing session values
    //             $this->setUserSession($user);

    //             // Redirecting to dashboard after login
    //             if ($user['u_role'] == "admin") {
    //                 $data['AdminDashboard'] = 'dashboard';
    //                 return redirect()->to(base_url('admin/dashboard'));
    //             } elseif ($user['u_role'] == "coordinator") {
    //                 return redirect()->to(base_url('coordinator/dashboard'));
    //             } elseif ($user['u_role'] == "anggota") {
    //                 return redirect()->to(base_url('anggota'));
    //             } elseif ($user['u_role'] == "owner") {
    //                 return redirect()->to(base_url('owner/dashboard'));
    //             }
    //         }
    //     }
    //     return view('/login');
    // }

    public function login()
    {
        $db = \Config\Database::connect();
        $post = $this->request->getPost();
        $query = $db->table('tb_user')->getWhere(['u_username' => $post['u_username']]);
        $users = $query->getRow();
        if ($users) {
            if (password_verify($post['u_password'], $users->u_password)) {
                $model = new UsersModels();

                $user = $model->where('u_username', $this->request->getVar('u_username'))->first();
                // Stroing session values
                $this->setUserSession($user);

                // Redirecting to dashboard after login
                if ($user['u_role'] == "admin") {
                    $data['AdminDashboard'] = 'dashboard';
                    return redirect()->to(base_url('admin/dashboard'));
                } else if ($user['u_role'] == "coordinator") {
                    return redirect()->to(base_url('coordinator/dashboard'));
                }
                // else if ($user['u_role'] == "anggota") {
                //     return redirect()->to(base_url('anggota/dashboard'));
                // } else if ($user['u_role'] == "owner") {
                //     return redirect()->to(base_url('owner/dashboard'));
                // }
            } else {
                return redirect()->back()->with('error', 'Password tidak ditemukan!');
            }
        } else {
            return redirect()->back()->with('error', 'Username tidak ditemukan!');
        }
    }
    private function setUserSession($user)
    {
        $data = [
            'u_id' => $user['u_id'],
            'u_fullname' => $user['u_fullname'],
            'u_username' => $user['u_username'],
            'isLoggedIn' => true,
            "u_role" => $user['u_role'],
        ];

        session()->set($data);
        return true;
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
