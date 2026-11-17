<?php

namespace App\Notifications;

use App\Models\SesiKonseling;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Schema;

class StatusKonselingNotification extends Notification
{
    use Queueable;

    public SesiKonseling $sesiKonseling;
    public string $status;
    public string $pesan;

    /**
     * Inisialisasi data notifikasi status konseling gizi.
     */
    public function __construct(SesiKonseling $sesiKonseling, string $status)
    {
        $this->sesiKonseling = $sesiKonseling;
        $this->status = $status;
        $this->pesan = $this->buildPesan();
    }

    /**
     * Menentukan kanal pengiriman notifikasi.
     */
    public function via(object $notifiable): array
    {
        if (Schema::hasTable('notifications')) {
            return ['database'];
        }

        return ['mail'];
    }

    /**
     * Representasi email notifikasi.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Update Status Konseling Gizi: " . ucfirst($this->status))
            ->greeting("Halo " . ($notifiable->name ?? 'Keluarga Balita'))
            ->line($this->pesan);
    }

    /**
     * Representasi array data notifikasi.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'sesi_konseling_id' => $this->sesiKonseling->id,
            'topik' => $this->sesiKonseling->topik,
            'status' => $this->status,
            'pesan' => $this->pesan,
            'jadwal' => $this->sesiKonseling->jadwal ? Carbon::parse($this->sesiKonseling->jadwal)->format('Y-m-d H:i:s') : null,
            'nakes_name' => $this->sesiKonseling->nakes?->name,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Membentuk pesan notifikasi kontekstual berdasarkan transisi status (KF-49).
     */
    protected function buildPesan(): string
    {
        $balitaName = $this->sesiKonseling->orangTua?->balita?->first()?->nama_balita 
            ?? $this->sesiKonseling->orangTua?->balita?->first()?->name 
            ?? 'Anak';

        return match ($this->status) {
            'diajukan' => "Pengajuan sesi konseling gizi topik '{$this->sesiKonseling->topik}' atas balita {$balitaName} berhasil dikirim dan menunggu penetapan jadwal oleh nakes.",
            'dijadwalkan' => "Jadwal konseling gizi topik '{$this->sesiKonseling->topik}' telah ditetapkan bersama " . ($this->sesiKonseling->nakes?->name ?? 'Tenaga Kesehatan') . " pada " . ($this->sesiKonseling->jadwal ? Carbon::parse($this->sesiKonseling->jadwal)->format('d M Y, H:i') . ' WIB' : 'waktu yang ditentukan') . ".",
            'selesai' => "Sesi konseling gizi topik '{$this->sesiKonseling->topik}' telah selesai dilaksanakan. Catatan evaluasi klinis dan rekomendasi gizi telah dicatat.",
            default => "Status sesi konseling gizi telah diperbarui menjadi '{$this->status}'.",
        };
    }
}

