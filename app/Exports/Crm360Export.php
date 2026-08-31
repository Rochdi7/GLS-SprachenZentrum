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
use PhpOffice\PhpSpreadsheet\Style\Conditional;
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

    /**
     * Per-column presentation spec: width in Excel character units, the data
     * kind (drives number format + alignment), and whether it wraps.
     *
     * Widths are FIXED rather than auto-sized on purpose. PhpSpreadsheet's
     * setAutoSize() measures every cell of every autosized column at write
     * time (Worksheet::calculateColumnWidths), which is roughly a 2x penalty
     * on the whole write — measured at ~10s extra for 10k rows x 18 columns,
     * and it degrades faster than linearly. On a full-year export that alone
     * exhausts the PHP time limit. These widths are tuned to the real data
     * (CRM references, Moroccan phone numbers, group names like "A1-Soir")
     * so the result is the same readable sheet at a fraction of the cost.
     *
     * @var array<string,array{w:int,kind:string}>
     */
    private const COLUMN_SPEC = [
        'student_ref' => ['w' => 14, 'kind' => 'text'],
        'student_name' => ['w' => 26, 'kind' => 'text'],
        'student_phone' => ['w' => 14, 'kind' => 'phone'],
        'student_email' => ['w' => 28, 'kind' => 'text'],
        'site_name' => ['w' => 20, 'kind' => 'text'],
        'class_name' => ['w' => 20, 'kind' => 'text'],
        'class_level' => ['w' => 9, 'kind' => 'center'],
        'registration_id' => ['w' => 13, 'kind' => 'int'],
        'registration_date' => ['w' => 15, 'kind' => 'date'],
        'registration_status' => ['w' => 17, 'kind' => 'center'],
        'payment_reference' => ['w' => 16, 'kind' => 'text'],
        'payment_amount' => ['w' => 13, 'kind' => 'money'],
        'payment_rest' => ['w' => 14, 'kind' => 'money'],
        'payment_date' => ['w' => 14, 'kind' => 'date'],
        'payment_due_date' => ['w' => 14, 'kind' => 'date'],
        'payment_method' => ['w' => 14, 'kind' => 'center'],
        'payment_type' => ['w' => 14, 'kind' => 'center'],
        'payment_created_by' => ['w' => 22, 'kind' => 'text'],
        // Summary-sheet-only columns
        'nb_paiements' => ['w' => 13, 'kind' => 'int'],
        'total_paye' => ['w' => 14, 'kind' => 'money'],
        'total_reste' => ['w' => 14, 'kind' => 'money'],
        'premier_paiement' => ['w' => 16, 'kind' => 'date'],
        'dernier_paiement' => ['w' => 16, 'kind' => 'date'],
    ];

    /** Money cells are written as real numbers; this is how Excel renders them. */
    private const MONEY_FORMAT = '#,##0.00';

    /** @return array<string,string> field => header label */
    protected function columns(): array
    {
        return $this->mode === self::MODE_SUMMARY
            ? Unified360Service::SUMMARY_COLUMNS
            : Unified360Service::COLUMNS;
    }

    /** The spec for one field, defaulting to a plain medium-width text column. */
    private function spec(string $field): array
    {
        return self::COLUMN_SPEC[$field] ?? ['w' => 16, 'kind' => 'text'];
    }

    /**
     * Column letters grouped by kind, e.g. ['money' => ['L','M'], ...].
     * Lets registerEvents() style each group in one ranged call instead of
     * touching cells individually.
     *
     * @return array<string,list<string>>
     */
    private function columnGroups(): array
    {
        $groups = [];
        foreach (array_keys($this->columns()) as $i => $field) {
            $groups[$this->spec($field)['kind']][] = Coordinate::stringFromColumnIndex($i + 1);
        }

        return $groups;
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
            } elseif ($this->spec($field)['kind'] === 'phone' && $value !== null) {
                // Keep the leading zero. The TEXT number format alone is not
                // enough — PhpSpreadsheet infers the cell type from the PHP
                // value, so a numeric-looking string still lands as a number
                // and "0612345678" is written as 612345678.
                $value = (string) $value;
            }

            $out[] = $value;
        }

        return $out;
    }

    public function columnFormats(): array
    {
        $formats = [];

        foreach (array_keys($this->columns()) as $i => $field) {
            $letter = Coordinate::stringFromColumnIndex($i + 1);

            $formats[$letter] = match ($this->spec($field)['kind']) {
                'money' => self::MONEY_FORMAT,
                'int' => NumberFormat::FORMAT_NUMBER,
                // Phones and CRM references are digit strings with meaningful
                // leading zeros ("0612345678"). Forcing the text format stops
                // Excel dropping the zero and re-rendering them as numbers.
                'phone' => NumberFormat::FORMAT_TEXT,
                default => null,
            };

            if ($formats[$letter] === null) {
                unset($formats[$letter]);
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

                // Freeze the identity columns too, not just the header: with 18
                // columns the reader scrolls right past the student's name and
                // loses track of whose payment they are looking at. B = Étudiant.
                $sheet->freezePane('C2');
                $sheet->setAutoFilter("A1:{$lastCol}{$lastRow}");

                // Fixed widths — see COLUMN_SPEC for why this is not autosize.
                foreach (array_keys($this->columns()) as $i => $field) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))
                        ->setWidth($this->spec($field)['w']);
                }

                // Nothing below the header row exists on an empty result set.
                if ($lastRow < 2) {
                    return;
                }

                // Align each kind of column in one ranged call per group.
                $groups = $this->columnGroups();
                $alignments = [
                    'money' => Alignment::HORIZONTAL_RIGHT,
                    'int' => Alignment::HORIZONTAL_RIGHT,
                    'date' => Alignment::HORIZONTAL_CENTER,
                    'center' => Alignment::HORIZONTAL_CENTER,
                    'phone' => Alignment::HORIZONTAL_LEFT,
                ];

                foreach ($alignments as $kind => $horizontal) {
                    foreach ($groups[$kind] ?? [] as $letter) {
                        $sheet->getStyle("{$letter}2:{$letter}{$lastRow}")
                            ->getAlignment()->setHorizontal($horizontal);
                    }
                }

                // "Reste à payer" > 0 is the number the recouvrement team acts
                // on, so make it findable at a glance instead of hunting the
                // column. Conditional formatting is stored once as a rule —
                // unlike per-cell styling, its cost does not grow with rows.
                foreach (['payment_rest', 'total_reste'] as $field) {
                    $index = array_search($field, array_keys($this->columns()), true);
                    if ($index === false) {
                        continue;
                    }

                    $letter = Coordinate::stringFromColumnIndex($index + 1);
                    $range = "{$letter}2:{$letter}{$lastRow}";

                    $rule = new Conditional;
                    $rule->setConditionType(Conditional::CONDITION_CELLIS)
                        ->setOperatorType(Conditional::OPERATOR_GREATERTHAN)
                        ->addCondition('0');
                    $rule->getStyle()->getFont()->setBold(true)->getColor()->setRGB('B02A37');
                    $rule->getStyle()->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FDE2E4');

                    $sheet->getStyle($range)->setConditionalStyles([$rule]);
                }
            },
        ];
    }
}
