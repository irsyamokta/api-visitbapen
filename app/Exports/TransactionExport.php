<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class TransactionExport implements FromArray, WithHeadings, WithStyles
{
    protected $transactions;
    protected $summary;

    public function __construct($transactions, $summary)
    {
        $this->transactions = $transactions;
        $this->summary = $summary;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->transactions as $trx) {
            $rows[] = [
                $trx->title,
                ucfirst($trx->type),
                $trx->category,
                Carbon::parse($trx->transaction_date)->setTimezone(config('app.timezone'))->format('d/m/Y'),
                $trx->amount,
            ];
        }

        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            $this->summary['balance']
        ];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Judul',
            'Tipe',
            'Kategori',
            'Tanggal',
            'Jumlah'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->transactions) + 2;
        return [
            1 => ['font' => ['bold' => true]],
            $lastRow => ['font' => ['bold' => true]],
        ];
    }
}
