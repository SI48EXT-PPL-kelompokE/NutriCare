<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePaketPmtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_paket_pmt' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'min_age_target' => ['required', 'integer', 'min:6', 'max:59'],
            'max_age_target' => ['required', 'integer', 'min:6', 'max:59', 'gte:min_age_target'],
            'unit_type' => ['required', 'string', 'max:50'],
            'status' => ['nullable', 'boolean'],
            'komposisi' => ['required', 'array', 'min:1'],
            'komposisi.*.barang_id' => ['required', 'integer', 'exists:barang_pmt,id'],
            'komposisi.*.jumlah' => ['required', 'numeric', 'min:0.01'],
            'komposisi.*.unit_type' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_paket_pmt.required' => 'Nama paket PMT wajib diisi.',
            'min_age_target.required' => 'Usia sasaran minimal wajib diisi.',
            'min_age_target.min' => 'Usia sasaran minimal tidak boleh kurang dari 6 bulan (periode ASI eksklusif).',
            'min_age_target.max' => 'Usia sasaran minimal tidak boleh lebih dari 59 bulan.',
            'max_age_target.required' => 'Usia sasaran maksimal wajib diisi.',
            'max_age_target.min' => 'Usia sasaran maksimal tidak boleh kurang dari 6 bulan.',
            'max_age_target.max' => 'Usia sasaran maksimal tidak boleh lebih dari 59 bulan.',
            'max_age_target.gte' => 'Usia sasaran maksimal harus lebih besar atau sama dengan usia minimal.',
            'unit_type.required' => 'Satuan paket wajib diisi.',
            'komposisi.required' => 'Komposisi bahan makanan wajib disertakan.',
            'komposisi.min' => 'Paket minimal harus memiliki 1 komposisi bahan makanan.',
            'komposisi.*.barang_id.required' => 'Pilihan bahan makanan wajib diisi.',
            'komposisi.*.barang_id.exists' => 'Bahan makanan yang dipilih tidak valid.',
            'komposisi.*.jumlah.required' => 'Jumlah/takaran bahan wajib diisi.',
            'komposisi.*.jumlah.min' => 'Jumlah bahan minimal 0.01.',
            'komposisi.*.unit_type.required' => 'Satuan bahan wajib diisi.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $komposisi = $this->input('komposisi', []);
                if (is_array($komposisi)) {
                    $barangIds = [];
                    foreach ($komposisi as $index => $item) {
                        $barangId = $item['barang_id'] ?? null;
                        if ($barangId) {
                            if (in_array($barangId, $barangIds)) {
                                $validator->errors()->add(
                                    "komposisi.{$index}.barang_id",
                                    'Bahan makanan ini sudah ditambahkan sebelumnya. Harap gabungkan jumlahnya atau pilih bahan lain.'
                                );
                            } else {
                                $barangIds[] = $barangId;
                            }
                        }
                    }
                }
            }
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->boolean('status', false),
        ]);
    }
}
