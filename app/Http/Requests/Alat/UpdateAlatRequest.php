<?php

namespace App\Http\Requests\Alat;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'kategori_id'   => 'sometimes|exists:kategori,id',
            'nama_alat'     => 'sometimes|string|max:255',
            'stok'          => 'sometimes|integer|min:0',
            'status_kondisi'=> 'sometimes|string',
            'deskripsi'     => 'nullable|string',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}