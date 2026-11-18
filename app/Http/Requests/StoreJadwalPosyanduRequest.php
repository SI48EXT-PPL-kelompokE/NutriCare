<?php

namespace App\Http\Requests;

use App\Models\JadwalPosyandu;
use Illuminate\Foundation\Http\FormRequest;

class StoreJadwalPosyanduRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', JadwalPosyandu::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'judul' => ['required', 'string', 'max:150'],
            'date' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'location' => ['required', 'string', 'max:255'],
            'kuota_jadwal' => ['required', 'integer', 'min:1', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Tenaga kesehatan penanggung jawab wajib dipilih.',
            'user_id.exists' => 'Tenaga kesehatan tidak valid.',
            'judul.required' => 'Judul kegiatan posyandu wajib diisi.',
            'date.required' => 'Tanggal kegiatan wajib ditentukan.',
            'date.date' => 'Format tanggal tidak valid.',
            'jam_mulai.required' => 'Jam mulai kegiatan wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai harus JJ:MM (contoh: 08:30).',
            'jam_selesai.required' => 'Jam selesai kegiatan wajib diisi.',
            'jam_selesai.date_format' => 'Format jam selesai harus JJ:MM (contoh: 11:00).',
            'jam_selesai.after' => 'Jam selesai kegiatan harus lebih besar dari jam mulai.',
            'location.required' => 'Lokasi kegiatan posyandu wajib diisi.',
            'kuota_jadwal.required' => 'Kuota pengunjung posyandu wajib diisi.',
            'kuota_jadwal.min' => 'Kuota minimal adalah 1.',
        ];
    }
}

