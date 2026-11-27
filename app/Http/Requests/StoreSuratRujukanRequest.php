<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratRujukanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'balita_id' => ['required', 'integer', 'exists:balita,id'],
            'faskes_tujuan' => ['required', 'string', 'max:255'],
            'alasan' => ['required', 'string', 'max:2000'],
            'status_follow_up' => ['nullable', 'string', 'in:belum,proses,selesai'],
        ];
    }

    public function messages(): array
    {
        return [
            'balita_id.required' => 'Balita wajib dipilih.',
            'balita_id.exists' => 'Data balita yang dipilih tidak ditemukan.',
            'faskes_tujuan.required' => 'Fasilitas kesehatan rujukan tujuan wajib diisi.',
            'alasan.required' => 'Alasan atau diagnosa sementara rujukan wajib diisi.',
            'status_follow_up.in' => 'Status tindak lanjut tidak valid.',
        ];
    }

    /**
     * Tegakkan prasyarat KMS sebelum penerbitan surat rujukan.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('balita_id')) {
                $hasKms = \App\Models\PengukuranKms::where('balita_id', $this->balita_id)->exists();
                if (!$hasKms) {
                    $validator->errors()->add(
                        'balita_id',
                        'Surat rujukan tidak dapat diterbitkan karena balita yang dipilih belum memiliki data hasil pengukuran KMS.'
                    );
                }
            }
        });
    }
}
