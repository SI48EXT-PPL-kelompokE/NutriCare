<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PengeluaranStokFefoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-module', ['stok_gudang', 'edit']) ?? false;
    }

    public function rules(): array
    {
        return [
            'barang_id' => ['required', 'integer', 'exists:barang_pmt,id'],
            'jumlah_keluar' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'barang_id.required' => 'Barang PMT yang akan dikeluarkan wajib dipilih.',
            'barang_id.exists' => 'Barang PMT tidak valid.',
            'jumlah_keluar.required' => 'Jumlah pengeluaran stok wajib diisi.',
            'jumlah_keluar.min' => 'Jumlah pengeluaran stok minimal 0.01.',
        ];
    }
}
