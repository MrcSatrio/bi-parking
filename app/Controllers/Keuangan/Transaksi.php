<?php

namespace App\Controllers\Keuangan;

use App\Controllers\BaseController;

class Transaksi extends BaseController
{
    protected $userModel;
    protected $kartuModel;
    protected $transaksiModel;
    protected $statustransaksiModel;
    protected $hargaModel;
    protected $pager;

    public function transaksi_inputkodebooking()
    {
        $data =
            [
                'title' => 'Parking Management System',
                'user' => $this->userModel
                    ->join('role', 'role.id_role = user.id_role')
                    ->where('npm', session('npm'))
                    ->first()
            ];

        return view('r_keuangan/transaksi_inputkodebooking', $data);
    }

    public function transaksi_validasiinputkodebooking()
    {
        $kodebooking = $this->request->getVar('kodebooking_transaksi');
        $databook = $this->transaksiModel->where('kodebooking_transaksi', $kodebooking)->first();

        if (empty($databook)) {
            session()->setFlashdata('error', 'Maaf, Kode Booking Tidak Ditemukan. Harap masukkan data dengan benar atau periksa kembali kode yang dimasukkan.');
            return redirect()->back();
        }

        $id_status_transaksi = $databook['id_status_transaksi'];
        $status_transaksi = $this->statustransaksiModel->where('id_status_transaksi', $id_status_transaksi)->first();
        $status_nama = $status_transaksi['nama_status_transaksi'];

        if ($status_nama == 'APPROVED') {
            session()->setFlashdata('error', 'Maaf, Kode Booking Sudah Tervalidasi');
            return redirect()->back();
        }
        elseif ($status_nama == 'COMPLETE') {
            session()->setFlashdata('error', 'Maaf, Kode Booking Tidak Ditemukan. Harap masukkan data dengan benar atau periksa kembali kode yang dimasukkan.');
            return redirect()->back();
        }
        elseif ($status_nama == 'CANCEL') {
            session()->setFlashdata('error', 'Maaf, Kode Booking Sudah Dibatalkan.');
            return redirect()->back();
        }

        $harga = $this->hargaModel->where('nama_harga', 'kartu_hilang')->first();
        $total_harga = $databook['nominal_transaksi'] + $harga['nominal'];

        $data = [
            'title' => 'Parking Management System',
            'user' => $this->userModel
                ->join('role', 'role.id_role = user.id_role')
                ->where('npm', session('npm'))
                ->first(),
            'transaksi' => $this->transaksiModel
                ->join('user', 'user.npm = transaksi.npm')
                ->join('kartu', 'kartu.id_kartu = user.id_kartu')
                ->join('status_transaksi', 'status_transaksi.id_status_transaksi = transaksi.id_status_transaksi')
                ->join('jenis_transaksi', 'jenis_transaksi.id_jenis_transaksi = transaksi.id_jenis_transaksi')
                ->where('kodebooking_transaksi', $kodebooking)
                ->first(),
            'harga' => $total_harga
        ];

        return view('r_keuangan/transaksi_validasiinputkodebooking', $data);
    }

    public function transaksi_approve()
{
    $validationRules = [
        'nomor_kartu' => [
            'rules' => 'is_unique[kartu.nomor_kartu]',
            'errors' => [
                'is_unique' => 'Nomor Kartu ini Telah Digunakan Sebelumnya',
                'required' => 'Harus Di Isi'
            ]
        ]
    ];
    if (!$this->validate($validationRules)) {
        session()->setFlashdata('error', $this->validator->listErrors());
        return redirect()->to("keuangan/transaksi_inputkodebooking");
    }
    $kodebooking_transaksi = $this->request->getVar('kode_booking');
    $nomor_kartu = $this->request->getVar('nomor_kartu');
    $approvedBy = $this->userModel
        ->where('npm', session('npm'))
        ->first();
    $pengguna = $this->userModel
    ->join('transaksi', 'transaksi.npm = user.npm')
    ->join('kartu', 'kartu.id_kartu = user.id_kartu')
    ->where('kodebooking_transaksi', $kodebooking_transaksi)
    ->first();

    $transaksi = $this->transaksiModel
        ->join('user', 'user.npm = transaksi.npm')
        ->join('kartu', 'kartu.id_kartu = user.id_kartu')
        ->join('jenis_pembayaran', 'jenis_pembayaran.id_jenis_pembayaran = transaksi.id_jenis_pembayaran')
        ->where('kodebooking_transaksi', $kodebooking_transaksi)
        ->first();

    $this->transaksiModel->save([
        'id_transaksi' => $transaksi['id_transaksi'],
        'saldoawal_transaksi' => $transaksi['saldo'],
        'saldoakhir_transaksi' => $transaksi['saldo'] + $transaksi['nominal_transaksi'],
        'id_status_transaksi' => 3,
        'validator' => $approvedBy['nama']
    ]);

    $kartuData = [
        'id_kartu' => $transaksi['id_kartu'],
        'saldo' => $transaksi['saldo'] + $transaksi['nominal_transaksi']
    ];

    if ($nomor_kartu) {
        $kartuData['nomor_kartu'] = $nomor_kartu;
    }

    $this->kartuModel->save($kartuData);

    $logData = [
        'npm' => session('npm'),
        'action' => 'transaksi_approve',
        'details' => 'Transaksi dengan kode booking ' . $kodebooking_transaksi . ' disetujui',
        'ip_address' => $this->request->getIPAddress()
    ];

    $logModel = new \App\Models\LogModel();
    $logModel->insert($logData);
    $to = $pengguna['email'];
    $subject = 'Bukti Pembayaran Topup';
    $message = '
        <html>
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
                <h1>Bukti Pembayaran Topup</h1>
                <p>Halo <span class="bold">' . $pengguna['nama'] . '</span>,</p>
                <p>Terima kasih atas pembayaran Topup yang telah dilakukan. Berikut adalah rincian pembayaran:</p>
                <p class="bold">Waktu Transaksi:</p>
                <p>' . date('Y-m-d H:i:s') . '</p>
                <p class="bold">Nominal Pembayaran:</p>
                <p>Rp ' . number_format($transaksi['nominal_transaksi'], 0, ',', '.') . '</p>
                <p class="bold">Sisa Saldo:</p>
                <p>Rp ' . number_format($transaksi['saldoakhir_transaksi'], 0, ',', '.') . '</p>
                <p class="bold">Metode Pembayaran:</p>
                <p> ' . $transaksi['nama_jenis_pembayaran'] . '</p>
                <p>Simpan email ini sebagai bukti pembayaran Anda.</p>
                <p class="note">*Jika Anda tidak melakukan transaksi ini, mohon hubungi tim kami segera.</p>
                <p>Salam,</p>
                <p class="bold">Biu Parking Management</p>
            </div>
            <div class="footer">
                Email ini dikirim secara otomatis. Mohon jangan membalas email ini.
            </div>
        </body>
        </html>
    ';

    $email = \Config\Services::email();
    $email->setTo($to);
    $email->setFrom('biuparkingmanagement@gmail.com', 'Biu Parking Management');
    $email->setSubject($subject);
    $email->setMessage($message);
    $email->setMailType('html'); // Mengatur jenis email sebagai HTML

    if ($email->send()) {
        $data = [
            'title' => 'Parking Management System',
            'user' => $this->userModel
                ->join('role', 'role.id_role = user.id_role')
                ->where('npm', session('npm'))
                ->first(['user.*', 'role.nama_role']),
            'transaksi' => $this->transaksiModel
                ->join('user', 'user.npm = transaksi.npm')
                ->join('kartu', 'kartu.id_kartu = user.id_kartu')
                ->join('status_transaksi', 'status_transaksi.id_status_transaksi = transaksi.id_status_transaksi')
                ->join('jenis_transaksi', 'jenis_transaksi.id_jenis_transaksi = transaksi.id_jenis_transaksi')
                ->where('kodebooking_transaksi', $kodebooking_transaksi)
                ->first(),
            'harga' => $this->request->getVar('total_harga'),
        ];

    return view('r_keuangan/transaksi_approve', $data);
}
}
public function riwayat()
{
    $limit = 9; // Jumlah item per halaman
    $currentPage = $this->request->getVar('page_pagination') ? $this->request->getVar('page_pagination') : 1;

    $data = [
    'title' => 'Parking Management System',
    'user' => $this->userModel
        ->join('role', 'role.id_role = user.id_role')
        ->where('npm', session('npm'))
        ->first(),
        'transaksi' => $this->transaksiModel
    ->select('transaksi.*, user.nama, jenis_transaksi.nama_jenis_transaksi, status_transaksi.nama_status_transaksi, jenis_pembayaran.nama_jenis_pembayaran')
    ->join('user', 'user.npm = transaksi.npm')
    ->join('jenis_transaksi', 'jenis_transaksi.id_jenis_transaksi = transaksi.id_jenis_transaksi')
    ->join('status_transaksi', 'status_transaksi.id_status_transaksi = transaksi.id_status_transaksi')
    ->join('jenis_pembayaran', 'jenis_pembayaran.id_jenis_pembayaran = transaksi.id_jenis_pembayaran')
    ->where('transaksi.id_status_transaksi !=', 2)
    ->orderBy('transaksi.created_at', 'DESC')
    ->paginate($limit, 'pagination'),
'pager' => $this->transaksiModel
    ->select('transaksi.*, user.nama, jenis_transaksi.nama_jenis_transaksi, status_transaksi.nama_status_transaksi, jenis_pembayaran.nama_jenis_pembayaran')
    ->join('user', 'user.npm = transaksi.npm')
    ->join('jenis_transaksi', 'jenis_transaksi.id_jenis_transaksi = transaksi.id_jenis_transaksi')
    ->join('status_transaksi', 'status_transaksi.id_status_transaksi = transaksi.id_status_transaksi')
    ->join('jenis_pembayaran', 'jenis_pembayaran.id_jenis_pembayaran = transaksi.id_jenis_pembayaran')
    ->where('transaksi.id_status_transaksi !=', 2)
    ->orderBy('transaksi.created_at', 'DESC')
    ->pager,
    'currentPage' => $currentPage,
    'limit' => $limit,
];  
    return view('r_keuangan/transaksi_riwayat', $data);
}

}