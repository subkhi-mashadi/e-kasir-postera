<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PerHariSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(protected $perHari) {}

    public function title(): string
    {
        return 'Per Hari';
    }

    public function headings(): array
    {
        return ['Tanggal', 'Transaksi', 'Pendapatan'];
    }

    public function collection(): Collection
    {
        return collect($this->perHari)->map(fn ($row) => [
            'tanggal'    => $row->tanggal,
            'transaksi'  => $row->transaksi,
            'pendapatan' => $row->pendapatan,
        ]);
    }
}
