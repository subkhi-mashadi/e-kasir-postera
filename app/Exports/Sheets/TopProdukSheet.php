<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TopProdukSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(protected $topProduk) {}

    public function title(): string
    {
        return 'Top Produk';
    }

    public function headings(): array
    {
        return ['Produk', 'Qty Terjual', 'Pendapatan'];
    }

    public function collection(): Collection
    {
        return collect($this->topProduk)->map(fn ($row) => [
            'product_name' => $row->product_name,
            'qty'          => $row->qty,
            'pendapatan'   => $row->pendapatan,
        ]);
    }
}
