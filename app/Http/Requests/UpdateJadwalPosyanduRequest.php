<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJadwalPosyanduRequest extends FormRequest
{
    public function authorize(): bool
    {
        $jadwal = $this->route('jadwal');
        return $this->user()?->can('update', $jadwal) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'judul' => ['sometimes', 'required', 'string', 'max:150'],
            'date' => ['sometimes', 'required', 'date'],
            'jam_mulai' => ['sometimes', 'required', 'date_format:H:i'],
            'jam_selesai' => ['sometimes', 'required', 'date_format:H:i', 'after:jam_mulai'],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'kuota_jadwal' => ['sometimes', 'required', 'integer', 'min:1', 'max:500'],
            'status' => ['sometimes', 'required', 'in:aktif,dibatalkan'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Tenaga kesehatan penanggung jawab wajib dipilih.',
            'judul.required' => 'Judul kegiatan posyandu wajib diisi.',
            'date.required' => 'Tanggal kegiatan wajib ditentukan.',
            'jam_mulai.required' => 'Jam mulai kegiatan wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai harus JJ:MM (contoh: 08:30).',
            'jam_selesai.required' => 'Jam selesai kegiatan wajib diisi.',
            'jam_selesai.date_format' => 'Format jam selesai harus JJ:MM (contoh: 11:00).',
            'jam_selesai.after' => 'Jam selesai kegiatan harus lebih besar dari jam mulai.',
            'location.required' => 'Lokasi kegiatan posyandu wajib diisi.',
            'kuota_jadwal.required' => 'Kuota pengunjung posyandu wajib diisi.',
            'status.in' => 'Status jadwal harus bernilai aktif atau dibatalkan.',
        ];
    }
}

