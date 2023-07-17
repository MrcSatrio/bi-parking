<?php

namespace App\Controllers\Operator;

use App\Controllers\BaseController;

class CheckOut extends BaseController
{
    protected $userModel;
    protected $kartuModel;
    protected $logModel;
    protected $transaksiModel;

    public function __construct()
    {
        // Inisialisasi model-model yang diperlukan
        $this->userModel = new \App\Models\UserModel();
        $this->kartuModel = new \App\Models\KartuModel();
        $this->logModel = new \App\Models\LogModel();
        $this->transaksiModel = new \App\Models\TransaksiModel();
    }

    public function index()
{
    $nominal_transaksi = $this->request->getVar('nominal_transaksi');
    $user_parking = $this->userModel
        ->join('kartu', 'kartu.id_kartu = user.id_kartu')
        ->where('kartu.nomor_kartu', $this->request->getVar('nomor_kartu'))
        ->first();
    $approvedBy = $this->userModel
        ->where('npm', session('npm'))
        ->first();
    $validasi = [
        'nomor_kartu' => [
            'rules' => 'required|numeric|is_not_unique[kartu.nomor_kartu]',
            'errors' => [
                'required' => 'Nomor Kartu tidak boleh kosong',
                'numeric' => 'Nomor Kartu harus berupa angka',
                'is_not_unique' => 'Kartu Tidak Terdaftar',
            ]
        ]
    ];

    if (!$this->validate($validasi)) {
        session()->setFlashdata('error', $this->validator->listErrors());
        return redirect()->back()->withInput();
    } elseif ($user_parking['id_status'] == 1) {
        if ($user_parking['saldo'] < $nominal_transaksi) {
            session()->setFlashdata('error', 'Saldo Tidak Cukup');
            return redirect()->back()->withInput();
        } else {
            $nama = $user_parking['nama']; // Ganti 'nama' dengan kolom yang menyimpan nama pada tabel pengguna parkir
            $sisa_saldo = $user_parking['saldo'] - $nominal_transaksi;

            $this->transaksiModel->save([
                'kodebooking_transaksi' => substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 6)), 0, 6),
                'npm' => $user_parking['npm'],
                'id_jenis_transaksi' => $this->request->getVar('id_jenis_transaksi'),
                'saldoawal_transaksi' => $user_parking['saldo'],
                'nominal_transaksi' => $nominal_transaksi,
                'saldoakhir_transaksi' => $user_parking['saldo'] - $nominal_transaksi,
                'id_status_transaksi' => $this->request->getVar('id_status_transaksi'),
                'id_jenis_pembayaran' => $this->request->getVar('id_jenis_pembayaran'),
                'validator' => $approvedBy['nama']
            ]);

            $this->kartuModel->save([
                'id_kartu' => $user_parking['id_kartu'],
                'saldo' => $user_parking['saldo'] - $nominal_transaksi
            ]);

            $logData = [
                'npm' => session('npm'),
                'action' => 'transaksi_parkir',
                'details' => 'Transaksi Parkir sebesar ' . $nominal_transaksi,
                'ip_address' => $this->request->getIPAddress()
            ];
            $this->logModel->insert($logData);

            // Kirim email sebagai bukti pembayaran
            $to = $user_parking['email'];
            $subject = 'Bukti Pembayaran Parkir';

            $message = '<html>
                            <head>
                                <style>
                                    .container {
                                        max-width: 600px;
                                        margin: auto;
                                        padding: 20px;
                                        border: 1px solid #ccc;
                                        border-radius: 10px;
                                        font-family: Arial, sans-serif;
                                    }
                                    h1 {
                                        color: #333;
                                    }
                                    p {
                                        margin-bottom: 10px;
                                    }
                                    .bold {
                                        font-weight: bold;
                                    }
                                    .note {
                                        color: #999;
                                        font-size: 12px;
                                    }
                                    .footer {
                                        margin-top: 20px;
                                        text-align: center;
                                        color: #777;
                                        font-size: 12px;
                                    }
                                </style>
                            </head>
                            <body>
                                <div class="container">
                                    <h1>Bukti Pembayaran Parkir</h1>
                                    <p>Halo <span class="bold">' . $nama . '</span>,</p>
                                    <p>Terima kasih atas pembayaran parkir yang telah dilakukan. Berikut adalah rincian pembayaran:</p>
                                    <p class="bold">Waktu Transaksi:</p>
                                    <p>' . date('Y-m-d H:i:s') . '</p>
                                    <p class="bold">Nominal Pembayaran:</p>
                                    <p>Rp ' . number_format($nominal_transaksi, 0, ',', '.') . '</p>
                                    <p>Simpan email ini sebagai bukti pembayaran Anda.</p>
                                    <p class="note">*Jika Anda tidak melakukan transaksi ini, mohon hubungi tim kami segera.</p>
                                    <p>Salam,</p>
                                    <p class="bold">Biu Parking Management</p>
                                </div>
                                <div class="footer">
                                    Email ini dikirim secara otomatis. Mohon jangan membalas email ini.
                                </div>
                            </body>
                        </html>';

            $email = \Config\Services::email();
            $email->setTo($to);
            $email->setFrom('biuparkingmanagement@gmail.com', 'Biu Parking Management');
            $email->setSubject($subject);
            $email->setMessage($message);
            $email->setMailType('html'); // Set tipe email menjadi HTML
            $email->send();

            session()->setFlashdata('success', 'Transaksi Berhasil, Silahkan Buka Portal');
            session()->setFlashdata('nama', $nama); // Menyimpan data nama dalam flash data
            session()->setFlashdata('sisa_saldo', $sisa_saldo); // Menyimpan data sisa saldo dalam flash data

            return redirect()->back()->withInput();
        }
    } elseif ($user_parking['id_status'] == 2) {
        $masaBerlaku = strtotime($user_parking['masa_berlaku']);
        $currentTime = time();

        if ($masaBerlaku <= $currentTime) {
            if ($user_parking['saldo'] < $nominal_transaksi) {
                session()->setFlashdata('error', 'Saldo Tidak Cukup');
                return redirect()->back()->withInput();
            } else {
                $this->transaksiModel->save([
                    'kodebooking_transaksi' => substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 6)), 0, 6),
                    'npm' => $user_parking['npm'],
                    'id_jenis_transaksi' => $this->request->getVar('id_jenis_transaksi'),
                    'saldoawal_transaksi' => $user_parking['saldo'],
                    'nominal_transaksi' => $nominal_transaksi,
                    'saldoakhir_transaksi' => $user_parking['saldo'] - $nominal_transaksi,
                    'id_status_transaksi' => $this->request->getVar('id_status_transaksi'),
                    'id_jenis_pembayaran' => $this->request->getVar('id_jenis_pembayaran'),
                    'validator' => $approvedBy['nama']
                ]);

                $this->kartuModel->save([
                    'id_kartu' => $user_parking['id_kartu'],
                    'saldo' => $user_parking['saldo'] - $nominal_transaksi
                ]);

                $logData = [
                    'npm' => session('npm'),
                    'action' => 'transaksi_parkir',
                    'details' => 'Transaksi Parkir sebesar ' . $nominal_transaksi,
                    'ip_address' => $this->request->getIPAddress()
                ];
                $this->logModel->insert($logData);

                // Kirim email sebagai bukti pembayaran
                $to = $user_parking['email'];
                $subject = 'Bukti Pembayaran Parkir';

                $message = '<html>
                                <head>
                                    <style>
                                        .container {
                                            max-width: 600px;
                                            margin: auto;
                                            padding: 20px;
                                            border: 1px solid #ccc;
                                            border-radius: 10px;
                                            font-family: Arial, sans-serif;
                                        }
                                        h1 {
                                            color: #333;
                                        }
                                        p {
                                            margin-bottom: 10px;
                                        }
                                        .bold {
                                            font-weight: bold;
                                        }
                                        .note {
                                            color: #999;
                                            font-size: 12px;
                                        }
                                        .footer {
                                            margin-top: 20px;
                                            text-align: center;
                                            color: #777;
                                            font-size: 12px;
                                        }
                                    </style>
                                </head>
                                <body>
                                    <div class="container">
                                        <h1>Bukti Pembayaran Parkir</h1>
                                        <p>Halo <span class="bold">' . $user_parking['nama'] . '</span>,</p>
                                        <p>Terima kasih atas pembayaran parkir yang telah dilakukan. Berikut adalah rincian pembayaran:</p>
                                        <p class="bold">Waktu Transaksi:</p>
                                        <p>' .  date('Y-m-d H:i:s') . '</p>
                                        <p class="bold">Nominal Pembayaran:</p>
                                        <p>Rp ' . number_format($nominal_transaksi, 0, ',', '.') . '</p>
                                        <p>Simpan email ini sebagai bukti pembayaran Anda.</p>
                                        <p class="note">*Jika Anda tidak melakukan transaksi ini, mohon hubungi tim kami segera.</p>
                                        <p>Salam,</p>
                                        <p class="bold">Biu Parking Management</p>
                                    </div>
                                    <div class="footer">
                                        Email ini dikirim secara otomatis. Mohon jangan membalas email ini.
                                    </div>
                                </body>
                            </html>';

                $email = \Config\Services::email();
                $email->setTo($to);
                $email->setFrom('biuparkingmanagement@gmail.com', 'Biu Parking Management');
                $email->setSubject($subject);
                $email->setMessage($message);
                $email->setMailType('html'); // Set tipe email menjadi HTML
                $email->send();

                session()->setFlashdata('success', 'Transaksi Berhasil, Silahkan Buka Portal');
                return redirect()->back()->withInput();
            }
        } else {
            session()->setFlashdata('error', 'Ini Kartu Member');
            return redirect()->back()->withInput();
        }
    }
}
}