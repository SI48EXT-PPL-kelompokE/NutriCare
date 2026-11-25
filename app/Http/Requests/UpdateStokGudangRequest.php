<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStokGudangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-module', ['stok_gudang', 'edit']) ?? false;
    }

    public function rules(): array
    {
        $stok = $this->route('stok');
        $stokId = is_object($stok) ? $stok->id : $stok;

        return [
            'barang_id' => ['required', 'integer', 'exists:barang_pmt,id'],
            'no_batch' => [
                'required',
                'string',
                'max:100',
                Rule::unique('stok_gudang', 'no_batch')
                    ->where(fn ($query) => $query->where('barang_id', $this->input('barang_id')))
                    ->ignore($stokId),
            ],
            'date_in' => ['required', 'date'],
            'exp_date' => ['required', 'date', 'after:date_in'],
            'stock_qty' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'barang_id.required' => 'Barang PMT wajib dipilih.',
            'barang_id.exists' => 'Barang PMT yang dipilih tidak ditemukan.',
            'no_batch.required' => 'Nomor batch wajib diisi.',
            'no_batch.unique' => 'Nomor batch tersebut sudah terdaftar untuk barang ini.',
            'date_in.required' => 'Tanggal masuk gudang wajib diisi.',
            'exp_date.required' => 'Tanggal kedaluwarsa (ED) wajib diisi.',
            'exp_date.after' => 'Tanggal kedaluwarsa harus setelah tanggal masuk gudang.',
            'stock_qty.required' => 'Jumlah saldo stok wajib diisi.',
            'stock_qty.min' => 'Jumlah saldo stok tidak boleh negatif.',
        ];
    }
}
