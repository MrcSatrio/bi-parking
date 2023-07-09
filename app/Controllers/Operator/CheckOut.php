<?php
namespace App\Controllers\Operator;

use \App\Controllers\BaseController;

class CheckOut extends BaseController
{
    protected $userModel;
    protected $kartuModel;
    protected $logModel;
    protected $transaksiModel;

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
                $logModel = new \App\Models\LogModel();
                $logModel->insert($logData);

                session()->setFlashdata('success', 'Transaksi Berhasil, Silahkan Buka Portal');
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
                    $logModel = new \App\Models\LogModel();
                    $logModel->insert($logData);

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
