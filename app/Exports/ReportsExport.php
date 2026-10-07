<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class ReportsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithDefaultStyles
{
    protected Collection $records;
    protected array $filters;
    protected Carbon $generatedAt;
    protected string $generatedBy;

    public function __construct(Collection $records, array $filters, Carbon $generatedAt, string $generatedBy)
    {
        $this->records = $records;
        $this->filters = $filters;
        $this->generatedAt = $generatedAt;
        $this->generatedBy = $generatedBy;
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            ['National Aviation Academy of the Philippines — Lost & Found Management System'],
            ['Generated: ' . $this->generatedAt->format('M d, Y h:i A') . ' | By: ' . $this->generatedBy . ' | Records: ' . $this->records->count() . ' item(s)'],
            ['Filters — Type: ' . ($this->filters['type'] ?? 'All') . ' | Status: ' . ($this->filters['status'] ?? 'All') . ' | Category: ' . ($this->filters['category'] ?? 'All') . ' | Location: ' . ($this->filters['location'] ?? 'All') . ' | Search: ' . ($this->filters['search'] ?? '—') . ' | Date From: ' . ($this->filters['date_from'] ?? '—') . ' | Date To: ' . ($this->filters['date_to'] ?? '—')],
            [],
            [
                '#',
                'Ref No.',
                'Item Name',
                'Description',
                'Brand / Model',
                'Color',
                'Type',
                'Category',
                'Location',
                'Incident Date',
                'Report Date',
                'Status',
                'Reporter Name',
                'Reporter Email',
                'Contact Override',
                'Circumstances',
            ],
        ];
    }

    public function map($row): array
    {
        static $index = 0;
        $index++;

        // $row is ItemReport model
        $incident = $row->incident_date ? Carbon::parse($row->incident_date)->format('M d, Y') : '—';
        if ($row->incident_time) $incident .= ' ' . $row->incident_time;

        $reportDate = $row->created_at ? $row->created_at->format('M d, Y h:i A') : '—';
        $reporterName = $row->reporter?->profile?->full_name ?? $row->reporter?->name ?? '—';
        $reporterEmail = $row->reporter?->email ?? '—';

        return [
            $index,
            'LF-' . str_pad($row->id, 6, '0', STR_PAD_LEFT),
            $row->item_name ?? '—',
            $row->item_description ?? '—',
            $row->brand_model ?? '—',
            $row->color ?? '—',
            ucfirst($row->report_type ?? '—'),
            $row->category?->name ?? '—',
            $row->location?->name ?? '—',
            $incident,
            $reportDate,
            ucfirst($row->status ?? '—'),
            $reporterName,
            $reporterEmail,
            $row->contact_override ?? '—',
            $row->circumstances ?? '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Title rows
        $sheet->mergeCells('A1:P1');
        $sheet->mergeCells('A2:P2');
        $sheet->mergeCells('A3:P3');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0041C7']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 8, 'color' => ['rgb' => '64748B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['size' => 8, 'color' => ['rgb' => '64748B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(3)->setRowHeight(30);

        // Header row (row 5)
        $sheet->getStyle('A5:P5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0041C7']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(18);

        // Data rows styling - alternating + borders
        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 5) {
            $sheet->getStyle('A6:P' . $highestRow)->applyFromArray([
                'font' => ['size' => 8, 'color' => ['rgb' => '1E293B']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ]);
            // Freeze pane
            $sheet->freezePane('A6');
            // Auto filter
            $sheet->setAutoFilter('A5:P5');
            // Set column widths - ShouldAutoSize will handle mostly but set some limits
        }

        // Footer row after data (if needed) - add summary at bottom
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Lost & Found Report';
    }

    public function defaultStyles(\PhpOffice\PhpSpreadsheet\Style\Style $defaultStyle)
    {
        return [
            'font' => ['name' => 'Calibri', 'size' => 9],
        ];
    }
}
