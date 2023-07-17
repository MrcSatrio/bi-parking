<?php

namespace App\Controllers\Keuangan;

use \App\Controllers\BaseController;

class User extends BaseController
{
    protected $userModel;
    protected $kartuModel;
    protected $roleModel;
    protected $statusmodel;
    protected $pager;
    public function create()
    {
        $data = [
            'title' => 'Parking Management System',
            'user' => $this->userModel
                ->join('role', 'role.id_role = user.id_role')
                ->where('npm', session('npm'))
                ->first()
        ];

        return view('r_keuangan/create', $data);
    }
    public function userRead()
    {
        $limit = 9; // Jumlah item per halaman
        $currentPage = $this->request->getVar('page_pagination') ? $this->request->getVar('page_pagination') : 1;
        $totalRows = $this->userModel->countAllResults();

        $data = [
            'title' => 'Parking Management System',
            'user' => $this->userModel
                ->join('role', 'role.id_role = user.id_role')
                ->where('npm', session('npm'))
                ->first(),
            'users' => $this->userModel->join('kartu', 'kartu.id_kartu = user.id_kartu')->paginate($limit, 'pagination'),
            'pager' => $this->userModel->pager,
            'currentPage' => $currentPage,
            'limit' => $limit,
        ];
        return view('r_keuangan/userRead', $data);
    }
    public function userInsert()
{
    $validationRules = [
        'npm' => [
            'rules' => 'required|numeric|exact_length[10]|is_unique[user.npm]',
            'errors' => [
                'required' => 'Nomor Pokok tidak boleh kosong',
                'numeric' => 'Nomor Pokok harus berupa angka',
                'exact_length' => 'Nomor Pokok harus terdiri dari 10 angka',
                'is_unique' => 'Nomor Pokok telah digunakan',
            ]
        ],
        'email' => [
            'rules' => 'required|valid_email|is_unique[user.email]',
            'errors' => [
                'required' => 'Email tidak boleh kosong',
                'valid_email' => 'Email tidak valid',
                'is_unique' => 'Email telah digunakan',
            ]
        ],
        'password' => [
            'rules' => 'required|min_length[8]|max_length[255]',
            'errors' => [
                'required' => 'Password tidak boleh kosong',
                'min_length' => 'Password harus terdiri dari 8 karakter atau lebih',
                'max_length' => 'Password harus terdiri dari 255 karakter atau lebih',
            ]
        ],
        'nomor_kartu' => [
            'rules' => 'is_unique[kartu.nomor_kartu]',
            'errors' => [
                'is_unique' => 'Nomor Kartu ini Telah Digunakan Sebelumnya',
                'required' => 'Harus Di Isi'
            ]
        ]
    ];

    if ($this->request->getVar('npm') && strlen($this->request->getVar('npm')) > 10) {
        $validationRules['npm']['rules'] .= '|max_length[10]';
        $validationRules['npm']['errors']['max_length'] = 'Nomor Pokok tidak lebih dari 10 angka';
    }

    if (!$this->validate($validationRules)) {
        session()->setFlashdata('error', $this->validator->listErrors());
        return redirect()->back()->withInput();
    }

    $kodebooking_transaksi = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 6)), 0, 6);
    $loggedInNpm = session('npm');
    $npm = $this->request->getVar('npm');
    $password =  $npm;
    $hashedPassword = md5($password);
    $saldo = $this->request->getVar('saldo');

    // Jika berhasil, simpan data ke database
    $this->kartuModel->save([
        'nomor_kartu' => $this->request->getVar('nomor_kartu'),
        'saldo' => $saldo,
    ]);

    $approvedBy = $this->userModel
        ->where('npm', session('npm'))
        ->first();

    $this->transaksiModel->save([
        'npm' => $npm,
        'kodebooking_transaksi' => $kodebooking_transaksi,
        'saldoakhir_transaksi' => $saldo,
        'nominal_transaksi' => $saldo,
        'id_jenis_pembayaran' => 1,
        'id_status_transaksi' => 3,
        'id_jenis_transaksi' => 1,
        'validator' => $approvedBy['nama']
    ]);

    $datauser = [
        'npm' => $npm,
        'id_kartu' => $this->kartuModel->getInsertID(),
        'id_role' => $this->request->getVar('id_role'),
        'id_status' => $this->request->getVar('id_status'),
        'nama' => $this->request->getVar('nama'),
        'email' => $this->request->getVar('email'),
        'password' => $hashedPassword
    ];
    $this->userModel->insert($datauser);

    $logModel = new \App\Models\LogModel();
    $logData = [
        'npm' => $loggedInNpm,
        'action' => 'User_Add',
        'details' => 'Pengguna ' . $datauser['npm'] . ' Ditambahkan',
        'ip_address' => $this->request->getIPAddress()
    ];
    $logModel->insert($logData);

    // Tampilkan pesan berhasil
    session()->setFlashdata('success', '<br>');
    return redirect()->to(base_url('keuangan/create'));
}

}
