<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSuratRujukanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'faskes_tujuan' => ['required', 'string', 'max:255'],
            'alasan' => ['required', 'string', 'max:2000'],
            'status_follow_up' => ['required', 'string', 'in:belum,proses,selesai'],
        ];
    }

    public function messages(): array
    {
        return [
            'faskes_tujuan.required' => 'Fasilitas kesehatan rujukan tujuan wajib diisi.',
            'alasan.required' => 'Alasan atau diagnosa rujukan wajib diisi.',
            'status_follow_up.required' => 'Status tindak lanjut rujukan wajib dipilih.',
            'status_follow_up.in' => 'Status tindak lanjut tidak valid.',
        ];
    }
}
