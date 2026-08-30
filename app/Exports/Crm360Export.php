<?php

namespace App\Exports;

use App\Services\Crm\Unified360Service;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * One sheet of the Vue 360 export.
 *
 * FromQuery (not FromCollection) so maatwebsite chunks the result set — a
 * 100k-row export streams instead of being materialised in memory at once.
 *
 * The same class serves both sheets; $mode picks which query and which column
 * map to use. See Crm360Workbook for the multi-sheet wrapper.
 */
class Crm360Export implements FromQuery, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles, WithTitle
{
    use Exportable;

    public const MODE_DETAIL = 'detail';

    public const MODE_SUMMARY = 'summary';

    /** @param  array<string,mixed>  $filters */
    public function __construct(
        protected Unified360Service $service,
        protected array $filters = [],
        protected string $mode = self::MODE_DETAIL,
    ) {}

    /** @return array<string,string> field => header label */
    protected function columns(): array
    {
        return $this->mode === self::MODE_SUMMARY
            ? Unified360Service::SUMMARY_COLUMNS
            : Unified360Service::COLUMNS;
    }

    public function query(): Builder
    {
        return $this->mode === self::MODE_SUMMARY
            ? $this->service->summaryQuery($this->filters)
            : $this->service->query($this->filters);
    }

    public function title(): string
    {
        return $this->mode === self::MODE_SUMMARY ? 'Résumé inscriptions' : 'Détail paiements';
    }

    public function headings(): array
    {
        return array_values($this->columns());
    }

    /** @param  object  $row */
    public function map($row): array
    {
        $out = [];
        foreach (array_keys($this->columns()) as $field) {
            $value = $row->{$field} ?? null;

            // Dates → d/m/Y so Excel shows them the way the team reads them.
            // Matched on the value shape, not the field name, so `nb_paiements`
            // and other non-date fields are never touched.
            if ($value && preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $value)) {
                $value = \Carbon\Carbon::parse($value)->format('d/m/Y');
            }

            // Money → float so Excel can sum the column. Cast before the null
            // check on aggregates: a summary row with no payments must show a
            // real 0, not an empty cell that looks like missing data.
            if (in_array($field, ['payment_amount', 'payment_rest'], true)) {
                $value = $value === null ? null : (float) $value;
            } elseif (in_array($field, ['total_paye', 'total_reste'], true)) {
                $value = (float) $value;
            } elseif ($field === 'nb_paiements') {
                $value = (int) $value;
            }

            $out[] = $value;
        }

        return $out;
    }

    public function columnFormats(): array
    {
        $formats = [];
        $money = ['payment_amount', 'payment_rest', 'total_paye', 'total_reste'];

        foreach (array_keys($this->columns()) as $i => $field) {
            if (in_array($field, $money, true)) {
                $formats[Coordinate::stringFromColumnIndex($i + 1)] = NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1;
            }
        }

        return $formats;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(count($this->columns()));
                $lastRow = max($sheet->getHighestRow(), 1);

                // Header band
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D6EFD']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(26);

                // Freeze the header + autofilter so the sheet is usable as-is.
                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$lastCol}{$lastRow}");

                foreach (range(1, count($this->columns())) as $i) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
                }
            },
        ];
    }
}
