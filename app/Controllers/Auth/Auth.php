<?php

namespace App\Controllers\Auth;

use \App\Controllers\BaseController;

class Auth extends BaseController
{
    protected $userModel;
    public function index()
    {
        $data =
            [
                'title' => 'Parking Management System'
            ];
        return view('auth/login', $data);
    }

    public function login()
    {
        $npm = $this->request->getVar('npm');
        $password = $this->request->getVar('password');
        $user = $this->userModel->where('npm', $npm)->first();
    
        if ($user) {
            if (md5($password) == $user['password']) {
                //Session untuk login
                $session = session();
                $sessionData = [
                    'npm' => $user['npm'],
                    'nama' => $user['nama'],
                    'id_role' => $user['id_role']
                ];
                $session->set($sessionData);
    
                // Catat login ke LogModel
                $logData = [
                    'npm' => $user['npm'],
                    'action' => 'login',
                    'details' => 'User logged in',
                    'ip_address' => $this->request->getIPAddress()
                ];
                $logModel = new \App\Models\LogModel();
                $logModel->insert($logData);
    
                // Redirect sesuai dengan peran user
                if ($user['id_role'] == 1) {
                    return redirect()->to('/admin/dashboard');
                } elseif ($user['id_role'] == 2) {
                    return redirect()->to('/keuangan/dashboard');
                } elseif ($user['id_role'] == 3) {
                    return redirect()->to('/operator/dashboard');
                } else {
                    $popup = session('popup');
                    if ($popup === 'show') {
                        $data['showPopup'] = true;
                        session()->remove('popup'); // Hapus flash data 'popup' setelah ditampilkan
                    } else {
                        $data['showPopup'] = false;
                    }
                    return redirect()->to('/user/dashboard')->with('popup', 'show')->with('data', $data);
                }                
            }
    }
    }

    public function logout()
    {
        // Catat logout ke LogModel
        $session = session();
        $npm = $session->get('npm');
        $logData = [
            'npm' => $npm,
            'action' => 'logout',
            'details' => 'User logged out',
            'ip_address' => $this->request->getIPAddress()
        ];
        $logModel = new \App\Models\LogModel();
        $logModel->insert($logData);
    
        $session->destroy();
        session_write_close();
        return redirect()->to('/');
    }
    

    ///////////////////////FORGOT PASSWORD////////////////////////////
    public function forgot_password()
    {

        $data =
            [
                'title' => 'Parking Management System'
            ];
        return view('auth/forgot_password_view', $data);
    }

    ///////////////////////RESET PASSWORD////////////////////////////
    public function change_password()
    {

        $data =
            [
                'title' => 'Parking Management System'
            ];
        return view('auth/reset_password_view', $data);
    }

    public function password_reset()
{
    // Helper dan rules
    helper(['string']);
    $rules = [
        'email' => 'required|min_length[4]|max_length[100]|valid_email'
    ];

    if ($this->validate($rules)) {
        $token = mt_rand(100000, 999999);

        // Cek email di database
        $userdata = $this->userModel->where('email', $this->request->getVar('email'))->first();

        if (!$userdata) {
            // Jika email tidak ditemukan di database
            echo '<script>alert("Email not found in the database.");</script>';
            echo '<script>window.location.href = "' . base_url('/forgotpassword') . '";</script>';
            exit;
        }

        // Update token pada user data
        $data = [
            'email' => $this->request->getVar('email'),
            'token' => $token,
        ];
        $this->userModel->update($userdata['npm'], $data);

        // Catat reset password ke LogModel
        $logData = [
            'npm' => $userdata['npm'],
            'action' => 'password_reset',
            'details' => 'Token reset request',
            'ip_address' => $this->request->getIPAddress()
        ];
        $logModel = new \App\Models\LogModel();
        $logModel->insert($logData);

        // Kirim email reset password
        $to = $data['email'];
        $subject = 'Reset Password Token';
        $token_no = $token;
        $message = 'Halo ' . $userdata['nama'] . '<br><br>'
            . 'Masukkan Token Dibawah ini untuk melakukan Reset Password.'
            . '<br>' . 'Token Reset Password Anda: <br> <h1>' . $token_no . ' </h1> <br>'
            . '<span style="color: red; font-weight: bold;">⚠️ PERHATIAN !!! JANGAN BERIKAN TOKEN KEPADA ORANG LAIN ⚠️</span>' . '<br>'
            . '<span style="color: red; font-weight: bold;">⚠️ ABAIKAN EMAIL INI JIKA ANDA TIDAK MELAKUKAN RESET PASSWORD ⚠️</span>' . '<br><br>'
            . 'Terima kasih,' . '<br><br>' . ' Biu Parking Management';

        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setFrom('biuparkingmanagement@gmail.com', 'Biu Parking Management');
        $email->setSubject($subject);
        $email->setMessage($message);
        if ($email->send()) {
            $successMessage = 'Token sukses terkirim. Silakan periksa email yang terdaftar.<br> *Jika tidak muncul di kotak masuk, mohon cek folder spam di Gmail.';
            session()->setFlashdata('success', $successMessage);
            return redirect()->to(site_url('/resetpassword'));
        } else {
            $data = $email->printDebugger(['headers']);
            print_r($data);
        }

        return $this->response->redirect(site_url('/resetpassword'));
    }
}


public function update_password()
{
    $rules = [
        'token' => [
            'rules' => 'required|min_length[6]|max_length[6]',
            'errors' => [
                'required' => 'Token tidak boleh kosong',
                'min_length' => 'Token harus terdiri dari 6 angka',
                'max_length' => 'Token tidak lebih dari 6 angka'
            ]
        ],
        'password' => [
            'rules' => 'required|min_length[8]|regex_match[/[A-Z]/]',
            'errors' => [
                'required' => 'Password tidak boleh kosong',
                'min_length' => 'Password harus terdiri dari 8 karakter atau lebih',
                'regex_match' => 'Password harus mengandung setidaknya satu huruf besar',
            ]
        ],
        'confirmpassword' => [
            'rules' => 'matches[password]',
            'errors' => [
                'matches' => 'Konfirmasi password tidak sama dengan password'
            ]
        ],
    ];

    if ($this->validate($rules)) {
        $token = $this->request->getVar('token');
        $password = $this->request->getVar('password');
        $userdata = $this->userModel->where('token', $token)->first();

        if (!empty($userdata)) {
            $data = [
                'password' => md5($password),
                'token' => null,
            ];
            $this->userModel->update($userdata['npm'], $data);

            // Catat pembaruan password ke LogModel
            $logData = [
                'npm' => $userdata['npm'],
                'action' => 'password_reset',
                'details' => 'Password updated',
                'ip_address' => $this->request->getIPAddress()
            ];
            $logModel = new \App\Models\LogModel();
            $logModel->insert($logData);

            session()->setFlashdata('success', 'Silahkan Login Dengan Password Baru');
            return redirect()->to(base_url())->withInput();
        } else {
            // Token tidak ditemukan dalam database
            session()->setFlashdata('errortoken', 'Token tidak valid.');
            return redirect()->back()->withInput();
        }
    } else {
        session()->setFlashdata('error', $this->validator->listErrors());
        return redirect()->back()->withInput();
    }
}
}