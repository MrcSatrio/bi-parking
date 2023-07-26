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
        $captcha = $this->request->getVar('captcha');
        $user = $this->userModel->where('npm', $npm)->first();

        if (!$this->checkCaptcha($captcha)) {
            // Jika CAPTCHA salah, tampilkan pesan kesalahan
            return redirect()->to('/')->with('msg', 'CAPTCHA yang Anda masukkan salah.');
        }
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
            } else {
                return redirect()->to('/')->withInput()->with('msg', 'Username atau Password Salah');
            }
        } else {
            return redirect()->to('/')->withInput()->with('msg', 'Username atau Password Salah');
        }
    }
    private function checkCaptcha($userCaptcha)
    {
        $session = session();
        $captchaCode = $session->get('captcha_code');

        // Jika sesi CAPTCHA tidak ada atau CAPTCHA yang dimasukkan tidak sesuai, kembalikan false
        if (!$captchaCode || strtolower($userCaptcha) !== strtolower($captchaCode)) {
            return false;
        }

        // Jika CAPTCHA sesuai, hapus sesi CAPTCHA dan kembalikan true
        $session->remove('captcha_code');
        return true;
    }
    public function generateCaptcha()
    {
        $length = 5;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $captcha = '';
        for ($i = 0; $i < $length; $i++) {
            $captcha .= $characters[rand(0, strlen($characters) - 1)];
        }

        $session = session();
        $session->set('captcha_code', $captcha);

        // Buat gambar CAPTCHA
        $width = 100;
        $height = 40;
        $image = imagecreatetruecolor($width, $height);

        $background_color = imagecolorallocate($image, 255, 255, 255);
        $text_color = imagecolorallocate($image, 0, 0, 0);

        imagefilledrectangle($image, 0, 0, $width, $height, $background_color);

        // Tambahkan noise untuk meningkatkan keamanan
        for ($i = 0; $i < 50; $i++) {
            $x = rand(0, $width - 1);
            $y = rand(0, $height - 1);
            imagesetpixel($image, $x, $y, $text_color);
        }

        // Tambahkan teks CAPTCHA
        $font = FCPATH . 'text\Itim-Regular.ttf'; // Ganti dengan path font yang sesuai
        imagettftext($image, 20, 0, 10, 30, $text_color, $font, $captcha);

        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
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

        return redirect()->to('/')->withInput()->with('berhasil', 'Berhasil Logout');
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
                return redirect()->to(base_url('forgotpassword'))->withInput()->with('msg', 'Email Tidak Terdaftar');
            }

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
                $successMessage = 'Token Sukses Terkirim. Silakan Periksa Email yang Terdaftar';
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
        // Assuming this code is part of a larger function or controller method
    
        // Function to set error message and redirect
        $setErrorAndRedirect = function ($message) {
            session()->setFlashdata('error', $message);
            return redirect()->to(site_url('/resetpassword'));
        };
    
        // Function to set success message and return JSON response
        $setSuccessAndReturnJSON = function ($message) {
            session()->setFlashdata('success', $message);
            return $this->response->setJSON(['status' => 'success', 'message' => $message]);
        };
    
        // Check 'token'
        $token = $this->request->getVar('token');
        if (empty($token)) {
            return $setErrorAndRedirect('Token tidak boleh kosong.');
        } elseif (strlen($token) !== 6) {
            return $setErrorAndRedirect('Token harus terdiri dari 6 angka.');
        }
    
        // Check 'password'
        $password = $this->request->getVar('password');
        if (empty($password)) {
            return $setErrorAndRedirect('Password tidak boleh kosong.');
        } elseif (strlen($password) < 8) {
            return $setErrorAndRedirect('Password harus terdiri dari 8 karakter atau lebih.');
        } elseif (!preg_match('/[A-Z]/', $password)) {
            return $setErrorAndRedirect('Password harus mengandung setidaknya satu huruf besar.');
        }
    
        // Check if the 'confirmpassword' field matches the 'password' field
        $confirmpassword = $this->request->getVar('confirmpassword');
        if ($confirmpassword !== $password) {
            return $setErrorAndRedirect('Konfirmasi Password Tidak Sesuai');
        }
    
        // Find the user data by token
        $userdata = $this->userModel->where('token', $token)->first();
    
        if (!empty($userdata)) {
            // Update the user's password in the database using MD5 for hashing
            $data = [
                'password' => md5($password), // Using MD5 for hashing (Note: This is not recommended)
                'token' => null,
            ];
            $this->userModel->update($userdata['npm'], $data);
    
            // Log the password reset event
            $logData = [
                'npm' => $userdata['npm'],
                'action' => 'password_reset',
                'details' => 'Password updated',
                'ip_address' => $this->request->getIPAddress(),
            ];
            $logModel = new \App\Models\LogModel();
            $logModel->insert($logData);
    
            // Set success message in session and return JSON response
            return $setSuccessAndReturnJSON('Silahkan Login Dengan Password Baru');
        } else {
            // Set error message in session and return JSON response
            return $setErrorAndRedirect('Token Tidak Valid');
        }
    }}
    