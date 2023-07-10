<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Transaksi extends BaseController
{
    protected $userModel;
    protected $kartuModel;
    protected $transaksiModel;
    protected $rekeningModel;
    protected $statustransaksiModel;
    protected $JenisPembayaranModel;
    protected $hargaModel;
    protected $pager;

    public function __construct()
    {
        $this->userModel = new \App\Models\UserModel();
        $this->kartuModel = new \App\Models\KartuModel();
        $this->transaksiModel = new \App\Models\TransaksiModel();
        $this->rekeningModel = new \App\Models\RekeningModel();
        $this->statustransaksiModel = new \App\Models\StatusTransaksiModel();
        $this->JenisPembayaranModel = new \App\Models\JenisPembayaranModel();
        $this->hargaModel = new \App\Models\HargaModel();
        $this->pager = \Config\Services::pager();
    }
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

        return view('r_admin/transaksi_inputkodebooking', $data);
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
        } elseif ($status_nama == 'COMPLETE') {
            session()->setFlashdata('error', 'Maaf, Kode Booking Tidak Ditemukan. Harap masukkan data dengan benar atau periksa kembali kode yang dimasukkan.');
            return redirect()->back();
        }elseif ($status_nama == 'CANCEL') {
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

        return view('r_admin/transaksi_validasiinputkodebooking', $data);
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
            return redirect()->to("admin/transaksi_inputkodebooking");
        }
    
        $kodebooking_transaksi = $this->request->getVar('kode_booking');
        $nomor_kartu = $this->request->getVar('nomor_kartu');
    
        $approvedBy = $this->userModel
            ->where('npm', session('npm'))
            ->first();
    
        $transaksi = $this->transaksiModel
            ->join('user', 'user.npm = transaksi.npm')
            ->join('kartu', 'kartu.id_kartu = user.id_kartu')
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
    
        return view('r_admin/transaksi_approve', $data);
    }
    
    

    public function topup()
    {
        if (!$this->validate([
            'nominal' => [
                'rules' => 'numeric',
                'errors' => [
                    'numeric' => 'Pilih Nominal Saldo!',
                    
                ]
            ],
            'jenis_pembayaran' => [
                'rules' => 'numeric|required',
                'errors' => [
                    'numeric' => 'Pilih Metode Pembayaran!',
                    'required' => 'Pilih Metode Pembayaran!'
                ]
            ],
            
        ])) {
            session()->setFlashdata('error', $this->validator->listErrors());
            return redirect()->back()->withInput();
        }
    
        $user = $this->userModel->where('npm', session('npm'))->first();
        $transaksiData = $this->transaksiModel->where('npm', session('npm'))->findAll();
        $rekening = $this->rekeningModel->findAll();
        
    
        $transaksiJenis = array_column($transaksiData, 'id_status_transaksi');
    
        if ($user['id_status'] == 1 && in_array(1, $transaksiJenis)) {
            session()->setFlashdata('error', 'Transaksi gagal. Anda sudah memiliki transaksi yang sedang diproses.');
            return redirect()->back()->withInput();
        } elseif ($user['id_status'] == 2) {
            $masaBerlaku = strtotime($user['masa_berlaku']);
            $currentTime = time();
    
            if ($masaBerlaku > $currentTime || in_array(1, $transaksiJenis)) {
                session()->setFlashdata('error', 'Transaksi gagal. Anda sudah memiliki transaksi yang sedang diproses atau masa berlaku Member belum habis.');
                return redirect()->back()->withInput();
            }
        }
    
        // Lanjutkan dengan proses topup jika kondisi di atas tidak terpenuhi
    
        $kodebooking_transaksi = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 6)), 0, 6);
        $npm = $this->request->getVar('npm');
        $nominal_saldo = $this->request->getVar('nominal');
        $saldo_awal = $this->request->getVar('saldoawal');
        $id_jenis_transaksi = $this->request->getVar('jenis_transaksi');
        $id_jenis_pembayaran = $this->request->getVar('jenis_pembayaran');
        $id_status_transaksi = $this->request->getVar('status_transaksi');
        $saldo_akhir = $saldo_awal + $nominal_saldo;
    
        $transaksi = [
            'kodebooking_transaksi' => $kodebooking_transaksi,
            'npm' => $npm,
            'id_jenis_transaksi' => $id_jenis_transaksi,
            'saldoawal_transaksi' => $saldo_awal,
            'nominal_transaksi' => $nominal_saldo,
            'saldoakhir_transaksi' => $saldo_akhir,
            'id_jenis_pembayaran' => $id_jenis_pembayaran,
            'id_status_transaksi' => $id_status_transaksi
        ];
        $this->transaksiModel->save($transaksi);
        $logData = [
            'npm' => session('npm'),
            'action' => 'transaksi_Topup',
            'details' => 'Topup dengan kode booking ' . $kodebooking_transaksi . ' sebesar ' . $nominal_saldo ,
            'ip_address' => $this->request->getIPAddress()
        ];
        $logModel = new \App\Models\LogModel();
        $logModel->insert($logData);
        
    
        return redirect()->to("user/transaksi_result/$kodebooking_transaksi/$nominal_saldo/$id_jenis_pembayaran");
    }
    


    
    



    public function transaksi_kartuHilang()
{
    // Pengambilan data hasil input dari form
    $user = $this->userModel
        ->join('kartu', 'kartu.id_kartu = user.id_kartu')
        ->where('npm', session('npm'))
        ->first();
    $rekening = $this->rekeningModel->findAll();
    $nominal_transaksi = $this->request->getVar('nominal_transaksi');
    $idjenis = $this->request->getVar('id_jenis_transaksi');
    $status = $this->request->getVar('id_status_transaksi');
    $id_jenis_pembayaran = $this->request->getVar('jenis_pembayaran');
    $saldoawal_transaksi = $this->request->getVar('saldoawal_transaksi');

    // Validasi input
    if (!$this->validate([
        'nominal_transaksi' => [
            'rules' => 'numeric',
            'errors' => [
                'numeric' => 'Pilih Nominal Saldo!',
            ]
        ],
        'jenis_pembayaran' => [
            'rules' => 'numeric',
            'errors' => [
                'numeric' => 'Pilih Metode Pembayaran!',
            ]
        ],
    ])) {
        session()->setFlashdata('error', $this->validator->listErrors());
        return redirect()->back()->withInput();
    }

    $transaksiData = $this->transaksiModel->where('npm', session('npm'))->findAll();
    $transaksiJenis = array_column($transaksiData, 'id_status_transaksi');

    if ($user['id_status'] == 1 && in_array(1, $transaksiJenis)) {
        session()->setFlashdata('error', 'Transaksi gagal. Anda sudah memiliki transaksi yang sedang diproses.');
        return redirect()->back()->withInput();
    } elseif ($user['id_status'] == 2) {
        $masaBerlaku = strtotime($user['masa_berlaku']);
        $currentTime = time();

        if ($masaBerlaku > $currentTime || in_array(1, $transaksiJenis)) {
            session()->setFlashdata('error', 'Transaksi gagal. Anda sudah memiliki transaksi yang sedang diproses atau masa berlaku Member belum habis.');
            return redirect()->back()->withInput();
        }
    }

    // Generate kode booking secara acak dengan panjang 6 karakter
    $kodebooking_transaksi = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 6)), 0, 6);
    // Tambahkan operasi penjumlahan antara saldo awal dan nominal
    $saldoakhir_transaksi = $nominal_transaksi + $saldoawal_transaksi;

    // total harga yang harus dibayar, saldo dan kartu baru
    $harga = $this->hargaModel->where('nama_harga', 'kartu_hilang')->first();
    $total_harga = $nominal_transaksi + $harga['nominal'];

    // Simpan data booking ke dalam database
    $this->transaksiModel->save([
        'kodebooking_transaksi' =>  $kodebooking_transaksi,
        'npm' => $user['npm'],
        'id_jenis_transaksi' => $idjenis,
        'saldoawal_transaksi' => $saldoawal_transaksi,
        'nominal_transaksi' => $nominal_transaksi,
        'saldoakhir_transaksi' => $saldoakhir_transaksi,
        'id_jenis_pembayaran' => $id_jenis_pembayaran,
        'id_status_transaksi' => $status
    ]);
    $this->kartuModel->save([
        'id_kartu' => $user['id_kartu'],
        'nomor_kartu' => null,
    ]);
    $logData = [
        'npm' => session('npm'),
        'action' => 'transaksi_KartuHilang',
        'details' => 'Transaksi kartu hilang dengan kode booking ' . $kodebooking_transaksi . ' sebesar ' . $nominal_transaksi,
        'ip_address' => $this->request->getIPAddress()
    ];
    $logModel = new \App\Models\LogModel();
    $logModel->insert($logData);
    

    // Perpindahan ke fungsi transaksi_result untuk menampilkan kodebooking dan nominal
    return redirect()->to("user/transaksi_result/ $kodebooking_transaksi/$total_harga/$id_jenis_pembayaran");
}

    
    

public function transaksi_result($booking_code, $nominal_saldo, $id_jenis_pembayaran)
{
    $rekening = $this->rekeningModel->findAll();

    $data = [
        'title' => 'Parking Management System',
        'user' => $this->userModel
            ->join('role', 'role.id_role = user.id_role')
            ->where('npm', session('npm'))
            ->first(),
        'rekening' => $rekening,
        'booking_code' => $booking_code,
        'nominal_saldo' => $nominal_saldo,
        'jenis_pembayaran' => $id_jenis_pembayaran
    ];

    // Menampilkan hasil data booking code dan nominal saldo ke form transaksi_formResult
    return view('r_user/transaksi_formResult', $data);
}

    public function riwayat()
    {
        $limit = 9; // Jumlah item per halaman
        $currentPage = $this->request->getVar('page_pagination') ? $this->request->getVar('page_pagination') : 1;

        $data =
            [
                'title' => 'Parking Management System',
                'user' => $this->userModel
                    ->join('role', 'role.id_role = user.id_role')
                    ->where('npm', session('npm'))
                    ->first(),
                'transaksi' => $this->transaksiModel
                    ->join('user', 'user.npm = transaksi.npm')
                    ->join('jenis_transaksi', 'jenis_transaksi.id_jenis_transaksi = transaksi.id_jenis_transaksi')
                    ->join('status_transaksi', 'status_transaksi.id_status_transaksi = transaksi.id_status_transaksi')
                    ->join('jenis_pembayaran', 'jenis_pembayaran.id_jenis_pembayaran = transaksi.id_jenis_pembayaran')
                    ->orderBy('transaksi.updated_at', 'DESC')
                    ->paginate($limit, 'pagination'),
                'pager' => $this->transaksiModel
                    ->join('user', 'user.npm = transaksi.npm')
                    ->join('jenis_transaksi', 'jenis_transaksi.id_jenis_transaksi = transaksi.id_jenis_transaksi')
                    ->join('status_transaksi', 'status_transaksi.id_status_transaksi = transaksi.id_status_transaksi')
                    ->join('jenis_pembayaran', 'jenis_pembayaran.id_jenis_pembayaran = transaksi.id_jenis_pembayaran')
                    ->orderBy('transaksi.updated_at', 'DESC')
                    ->pager,
                'currentPage' => $currentPage,
                'limit' => $limit,
            ];

        return view('r_admin/transaksi_riwayat', $data);
    }
    public function cancel($id_transaksi)
{
    $logData = [
        'npm' => session('npm'),
        'action' => 'Transaksi_Cancel',
        'details' => 'Transaksi dengan ID ' . $id_transaksi . ' dibatalkan',
        'ip_address' => $this->request->getIPAddress()
    ];
    $logModel = new \App\Models\LogModel();
    $logModel->insert($logData);

    $data = $this->request->getPost();
    if (isset($data['id_transaksi']) && $data['id_transaksi'] !== $id_transaksi) {
        session()->setFlashdata('error', 'ID Transaksi tidak dapat diubah');
        return redirect()->back();
    }

    // Cek apakah id_status_transaksi bukan 4
    $id_status_transaksi = $data['id_status_transaksi'];
    if ($id_status_transaksi != 4) {
        session()->setFlashdata('error', 'Transaksi Tidak dapat Dibatalkan');
        return redirect()->back();
    }

    $user = $this->transaksiModel
        ->join('jenis_transaksi', 'jenis_transaksi.id_jenis_transaksi = transaksi.id_jenis_transaksi')
        ->join('status_transaksi', 'status_transaksi.id_status_transaksi = transaksi.id_status_transaksi')
        ->where('id_transaksi', $id_transaksi)
        ->first();

    $this->transaksiModel->update($user['id_transaksi'], $data);

    session()->setFlashdata('success', 'Transaksi Berhasil Dibatalkan.');
    return redirect()->back()->withInput();
}

    public function bukti($id_transaksi)
    {
        $data = $this->request->getFile('bukti_pembayaran');
        
        // Validasi ukuran file
        if ($data->getSize() > 4 * 1024 * 1024) {
            session()->setFlashdata('error', 'Ukuran file melebihi batas maksimum (4MB).');
            return redirect()->back()->withInput();
        }
    
        $fileName = date('YmdHis') . '_' . $data->getRandomName();
        $uploadDir = 'uploads/bukti/';
    
        $data->move($uploadDir, $fileName);
    
        $user = $this->transaksiModel
            ->join('jenis_transaksi', 'jenis_transaksi.id_jenis_transaksi = transaksi.id_jenis_transaksi')
            ->join('status_transaksi', 'status_transaksi.id_status_transaksi = transaksi.id_status_transaksi')
            ->where('id_transaksi', $id_transaksi)
            ->first();
    
        $this->transaksiModel->update($user['id_transaksi'], ['bukti_pembayaran' => $fileName]);
    
        session()->setFlashdata('success', 'Bukti Berhasil diupload.');
        return redirect()->back()->withInput();
    }
    
    public function cetak($id_transaksi)
    {
        $transaksi = $this->transaksiModel
            ->join('user', 'user.npm = transaksi.npm')
            ->join('kartu', 'kartu.id_kartu = user.id_kartu')
            ->join('status_transaksi', 'status_transaksi.id_status_transaksi = transaksi.id_status_transaksi')
            ->join('jenis_transaksi', 'jenis_transaksi.id_jenis_transaksi = transaksi.id_jenis_transaksi')
            ->where('id_transaksi', $id_transaksi)
            ->first();
    
        $data = [
            'title' => 'Parking Management System',
            'user' => $this->userModel
                ->join('role', 'role.id_role = user.id_role')
                ->where('npm', session('npm'))
                ->first(),
            'transaksi' => $transaksi
        ];
    
        // Cek apakah id_jenis_transaksi = 2
        if ($transaksi['id_jenis_transaksi'] == 2) {
            $harga = $this->hargaModel->where('nama_harga', 'kartu_hilang')->first();
            $total_harga = $transaksi['nominal_transaksi'] + $harga['nominal'];
            $data['harga'] = $total_harga;
        } else if ($transaksi['id_jenis_transaksi'] == 1) {
            $data['harga'] = $transaksi['nominal_transaksi'];
        }
    
        // Menampilkan hasil data booking code dan nominal saldo ke form transaksi_formResult
        return view('r_keuangan/cetak', $data);
    }
    public function rekening()
    {
        $data = [
            'title' => 'Parking Management System',
            'user' => $this->userModel
                ->join('role', 'role.id_role = user.id_role')
                ->where('npm', session('npm'))
                ->first(),
            'rekening' => $this->rekeningModel->findAll()
        ];

        return view('r_admin/rekening', $data);
    }
    public function edit_rekening($id)
    {
        // Ambil data rekening berdasarkan $id
        $rekening = $this->rekeningModel->find($id);

        if ($rekening === null) {
            // Jika rekening tidak ditemukan, tampilkan pesan error atau lakukan redirect
            return redirect()->back()->with('error', 'Rekening tidak ditemukan.');
        }

        // Tampilkan view edit_rekening dengan mengirimkan data rekening
        $data = [
            'title' => 'Edit Rekening',
            'user' => $this->userModel
                ->join('role', 'role.id_role = user.id_role')
                ->where('npm', session('npm'))
                ->first(),
            'rekening' => $rekening
        ];

        return view('r_admin/edit_rekening', $data);
    }
    public function update_rekening()
{
    $id = $this->request->getPost('id');
    $bank = $this->request->getPost('bank');
    $no_rekening = $this->request->getPost('no_rekening');
    $nama_rekening = $this->request->getPost('nama_rekening');

    $data = [
        'bank' => $bank,
        'no_rekening' => $no_rekening,
        'nama_rekening' => $nama_rekening
    ];

    $this->rekeningModel->update($id, $data);

    session()->setFlashdata('success', 'Rekening berhasil diperbarui.');
    return redirect()->to('keuangan/transaksi_rekening');
}


    // ...
}
