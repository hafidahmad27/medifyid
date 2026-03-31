<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MasterItemExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $data = DB::table('master_items')
            ->leftJoin('kategori_items', 'kategori_items.id', '=', 'master_items.kategori')
            ->select(
                'kategori_items.nama as nama_kategori',
                'master_items.nama as nama_item',
                'master_items.supplier',
                'master_items.harga_beli',
                'master_items.laba',
                DB::raw('(master_items.harga_beli + master_items.laba) as harga_jual')
            )
            ->get();

        return $data->map(function ($item, $index) {
            return [
                $index + 1,
                $item->nama_kategori,
                $item->nama_item,
                $item->supplier,
                $item->harga_beli,
                $item->laba,
                $item->harga_jual,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Kategori',
            'Nama Item',
            'Nama Supplier',
            'Harga',
            'Laba',
            'Harga Jual'
        ];
    }
}
