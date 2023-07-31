<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class CallbackController extends Controller
{
    protected $userModel;
    protected $kartuModel;
    protected $transaksiModel;
    protected $rekeningModel;
    protected $statustransaksiModel;
    protected $JenisPembayaranModel;
    protected $hargaModel;
    private $xenditCallbackToken = 'cYJxEJ38axyZym1Wjl3ua9aTiYfryiORBZGCJSTXNDsFhgz6';

    public function xenditCallback()
    {
        // Menerima data callback dari Xendit
        $rawData = file_get_contents('php://input');
        $callbackData = json_decode($rawData, true);

        // Pastikan callbackData bukan null dan verifikasi Callback Verification Token
        if ($callbackData) {
            $xenditCallbackToken = $_SERVER['HTTP_X_CALLBACK_TOKEN'];
            if ($xenditCallbackToken === $this->xenditCallbackToken) {
                // Token sesuai, callback valid dari Xendit
                // Lanjutkan dengan penanganan sesuai status pembayaran
                $invoiceId = $callbackData['id'];
                $status = $callbackData['status'];
                $amount = $callbackData['amount'];
                $externalId = $callbackData['external_id'];

                if ($status === 'PAID') {
                    // Lakukan tindakan sesuai dengan pembayaran yang berhasil
                    // Misalnya, perbarui status pembayaran di database Anda dan berikan saldo ke pengguna
                    // Contoh:
                    $pengguna = $this->userModel
                        ->join('transaksi', 'transaksi.npm = user.npm')
                        ->join('kartu', 'kartu.id_kartu = user.id_kartu')
                        ->where('kodebooking_transaksi', $externalId)
                        ->first();
                    $transaksi = $this->transaksiModel
                        ->join('user', 'user.npm = transaksi.npm')
                        ->join('kartu', 'kartu.id_kartu = user.id_kartu')
                        ->join('jenis_pembayaran', 'jenis_pembayaran.id_jenis_pembayaran = transaksi.id_jenis_pembayaran')
                        ->where('kodebooking_transaksi', $externalId)
                        ->first();
                    
                    $this->transaksiModel->save([
                        'id_transaksi' => $transaksi['id_transaksi'],
                        'saldoawal_transaksi' => $transaksi['saldo'],
                        'saldoakhir_transaksi' => $transaksi['saldo'] + $amount,
                        'id_status_transaksi' => 3,
                        'validator' => 'XENDIT'
                    ]);
                    
                    $kartuData = [
                        'id_kartu' => $transaksi['id_kartu'],
                        'saldo' => $transaksi['saldo'] + $amount,
                    ];
                    // Lakukan tindakan lain yang diperlukan sesuai dengan kebijakan bisnis Anda
                    // ...

                    echo "Pembayaran berhasil! Invoice ID: " . $invoiceId . ", Jumlah Pembayaran: " . $amount;
                } elseif ($status === 'EXPIRED') {
                    // Lakukan tindakan sesuai dengan pembayaran yang kadaluarsa
                    // Misalnya, perbarui status pembayaran di database Anda atau batalkan pesanan
                    // Contoh:
                    echo "Pembayaran kadaluarsa. Invoice ID: " . $invoiceId;
                } elseif ($status === 'FAILED') {
                    // Lakukan tindakan sesuai dengan pembayaran yang gagal
                    // Misalnya, perbarui status pembayaran di database Anda atau tampilkan pesan kesalahan
                    // Contoh:
                    echo "Pembayaran gagal. Invoice ID: " . $invoiceId;
                }
            } else {
                // Callback tidak valid, tangani sesuai kebijakan Anda
                // ...
            }
        }
    }
}
