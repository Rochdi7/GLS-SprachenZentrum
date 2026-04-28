<?php

namespace App\Exports;

use App\Models\PresenceImport;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exports a single PresenceImport as an Excel sheet ready to send
 * to the manager: header info + daily presence grid + per-week amounts.
 */
class PresenceImportExport implements FromArray, WithEvents, WithStyles, WithTitle
{
    public function __construct(
        protected PresenceImport $import,
    ) {
        $this->import->loadMissing(['students.records', 'group.teacher', 'paymentSummary']);
    }

    public function title(): string
    {
        return 'Présence v'.$this->import->version;
    }

    public function array(): array
    {
        $import = $this->import;
        $group = $import->group;
        $summary = $import->paymentSummary;
        $rate = (float) ($import->getEffectivePaymentPerStudent() ?? 0);

        $dates = $import->students
            ->flatMap(fn ($s) => $s->records->pluck('date'))
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->sort()
            ->values();

        $rows = [];

        // Header block
        $rows[] = ['Paiement Professeur — Détail Présence'];
        $rows[] = ['Groupe',          $group?->name ?? '—'];
        $rows[] = ['Professeur',      $group?->teacher?->name ?? '—'];
        $rows[] = ['Niveau',          $group?->level ?? '—'];
        $rows[] = ['Mois',            $import->month?->translatedFormat('F Y') ?? '—'];
        $rows[] = ['Période',         $import->date_start->format('d/m/Y').' — '.$import->date_end->format('d/m/Y')];
        $rows[] = ['Version import',  'v'.$import->version];
        $rows[] = ['Taux mensuel',    number_format($rate, 2, ',', ' ').' DH'];
        if ($summary) {
            $rows[] = ['TOTAL À PAYER', number_format((float) $summary->total_payment, 2, ',', ' ').' DH'];
            $rows[] = ['Étudiants actifs', $summary->total_students];
            $rows[] = ['Statut', $summary->isApproved() ? 'Approuvé' : 'En attente'];
        }
        $rows[] = ['']; // spacer (single-cell so Excel doesn't collapse it)

        // Table header
        $header = ['#', 'Etudiant'];
        foreach ($dates as $d) {
            $c = Carbon::parse($d);
            $header[] = mb_strtoupper(mb_substr($c->isoFormat('dd'), 0, 2)).' '.$c->format('d/m');
        }
        $header[] = 'P';
        $header[] = 'A';
        for ($w = 1; $w <= 4; $w++) {
            $header[] = "S{$w} prés.";
            $header[] = "S{$w} montant";
        }
        $header[] = 'Total étudiant';
        $rows[] = $header;

        // Body
        foreach ($import->students->sortBy('row_number') as $student) {
            $byDate = $student->records->keyBy(fn ($r) => $r->date->format('Y-m-d'));

            $row = [
                $student->row_number,
                $student->student_name.($student->isCancelled() ? ' [Annulé]' : ($student->isTransferred() ? ' [Transféré]' : '')),
            ];
            foreach ($dates as $d) {
                $rec = $byDate[$d] ?? null;
                $row[] = match ($rec?->status) {
                    'present' => 'P',
                    'absent' => 'A',
                    default => '-',
                };
            }
            $row[] = (int) $student->total_present;
            $row[] = (int) $student->total_absent;
            for ($w = 1; $w <= 4; $w++) {
                $row[] = $student->getWeekPresence($w);
                $row[] = round($student->getWeekEffectiveAmount($w), 2);
            }
            $row[] = round($student->getTotalAmount(), 2);
            $rows[] = $row;
        }

        // Totals footer
        if ($summary) {
            $weekTotals = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            foreach ($import->students as $s) {
                foreach ([1, 2, 3, 4] as $w) {
                    $weekTotals[$w] += $s->getWeekEffectiveAmount($w);
                }
            }

            $footer = ['', 'TOTAL'];
            foreach ($dates as $d) {
                $footer[] = '';
            }
            $footer[] = '';
            $footer[] = '';
            for ($w = 1; $w <= 4; $w++) {
                $footer[] = '';
                $footer[] = round($weekTotals[$w], 2);
            }
            $footer[] = round((float) $summary->total_payment, 2);
            $rows[] = $footer;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $summary = $this->import->paymentSummary;
                // Replace the empty spacer row in array() with a real blank row
                // so layout math stays simple. Without this the spacer gets
                // collapsed and the table header lands one row too high.
                $tableHeaderRow = $summary ? 13 : 10;
                $headerRowsBeforeTable = $tableHeaderRow - 1;
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();
                $highestColIndex = $this->letterToIndex($highestCol);

                $dateCount = $this->import->students
                    ->flatMap(fn ($s) => $s->records->pluck('date'))
                    ->unique()
                    ->count();

                $firstDateCol = 3;                          // C
                $lastDateCol = $firstDateCol + $dateCount - 1;
                $totalPresentCol = $lastDateCol + 1;        // P
                $totalAbsentCol = $lastDateCol + 2;         // A
                $week1PresCol = $lastDateCol + 3;           // S1 prés.
                $totalCol = $highestColIndex;               // Total étudiant

                // Title styling — span over the meta block
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D6EFD']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Metadata key column bold
                $metaEndRow = $headerRowsBeforeTable - 1;
                $sheet->getStyle('A2:A'.$metaEndRow)->getFont()->setBold(true);
                $sheet->getStyle('A2:A'.$metaEndRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('B2:B'.$metaEndRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Highlight TOTAL À PAYER line (always row 9 since it's the first summary row)
                if ($summary) {
                    $sheet->getStyle('A9:B9')->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '0F5132']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1E7DD']],
                    ]);
                    $sheet->getRowDimension(9)->setRowHeight(22);
                }

                // Table header row styling
                $sheet->getStyle("A{$tableHeaderRow}:{$highestCol}{$tableHeaderRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F3F5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BBBBBB']]],
                ]);
                $sheet->getRowDimension($tableHeaderRow)->setRowHeight(34);

                // Data area
                $dataStart = $tableHeaderRow + 1;
                if ($highestRow >= $dataStart) {
                    $sheet->getStyle("A{$dataStart}:{$highestCol}{$highestRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                        'font' => ['size' => 10],
                    ]);
                    $sheet->getStyle("A{$dataStart}:{$highestCol}{$highestRow}")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // Footer (TOTAL) row highlight
                    if ($summary) {
                        $sheet->getStyle("A{$highestRow}:{$highestCol}{$highestRow}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3CD']],
                        ]);
                        $sheet->getRowDimension($highestRow)->setRowHeight(22);
                    }

                    $bodyEndRow = $summary ? $highestRow - 1 : $highestRow;

                    // Center the # column
                    $sheet->getStyle("A{$dataStart}:A{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Date cells: centered + color-coded P / A
                    if ($lastDateCol >= $firstDateCol) {
                        $startLetter = $this->colLetter($firstDateCol);
                        $endLetter = $this->colLetter($lastDateCol);

                        $sheet->getStyle("{$startLetter}{$dataStart}:{$endLetter}{$bodyEndRow}")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        for ($r = $dataStart; $r <= $bodyEndRow; $r++) {
                            for ($c = $firstDateCol; $c <= $lastDateCol; $c++) {
                                $coord = $this->colLetter($c).$r;
                                $val = $sheet->getCell($coord)->getValue();
                                if ($val === 'P') {
                                    $sheet->getStyle($coord)->applyFromArray([
                                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D4EDDA']],
                                        'font' => ['color' => ['rgb' => '155724'], 'bold' => true, 'size' => 10],
                                    ]);
                                } elseif ($val === 'A') {
                                    $sheet->getStyle($coord)->applyFromArray([
                                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8D7DA']],
                                        'font' => ['color' => ['rgb' => '721C24'], 'bold' => true, 'size' => 10],
                                    ]);
                                }
                            }
                        }
                    }

                    // Center P / A totals + S{n} présences columns
                    $centeredCols = [$totalPresentCol, $totalAbsentCol];
                    for ($w = 0; $w < 4; $w++) {
                        $centeredCols[] = $week1PresCol + ($w * 2);
                    }
                    foreach ($centeredCols as $colIdx) {
                        $L = $this->colLetter($colIdx);
                        $sheet->getStyle("{$L}{$dataStart}:{$L}{$highestRow}")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // Right-align money columns + format with 2 decimals
                    $moneyCols = [];
                    for ($w = 0; $w < 4; $w++) {
                        $moneyCols[] = $week1PresCol + 1 + ($w * 2);  // S{w} montant
                    }
                    $moneyCols[] = $totalCol;
                    foreach ($moneyCols as $colIdx) {
                        $L = $this->colLetter($colIdx);
                        $sheet->getStyle("{$L}{$dataStart}:{$L}{$highestRow}")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle("{$L}{$dataStart}:{$L}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00');
                    }

                    // Total étudiant column — bold
                    $totalLetter = $this->colLetter($totalCol);
                    $sheet->getStyle("{$totalLetter}{$dataStart}:{$totalLetter}{$highestRow}")
                        ->getFont()->setBold(true);

                    // Body row height for readability
                    for ($r = $dataStart; $r <= $bodyEndRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(18);
                    }
                }

                // Explicit column widths — better than auto-size for many narrow date cols
                $sheet->getColumnDimension('A')->setWidth(5);   // #
                $sheet->getColumnDimension('B')->setWidth(28);  // Etudiant

                // Date columns — narrow & uniform
                for ($c = $firstDateCol; $c <= $lastDateCol; $c++) {
                    $sheet->getColumnDimension($this->colLetter($c))->setWidth(5);
                }
                // P / A totals
                $sheet->getColumnDimension($this->colLetter($totalPresentCol))->setWidth(6);
                $sheet->getColumnDimension($this->colLetter($totalAbsentCol))->setWidth(6);
                // 4 × (présences col, montant col)
                for ($w = 0; $w < 4; $w++) {
                    $presCol = $week1PresCol + ($w * 2);
                    $amtCol = $presCol + 1;
                    $sheet->getColumnDimension($this->colLetter($presCol))->setWidth(7);
                    $sheet->getColumnDimension($this->colLetter($amtCol))->setWidth(12);
                }
                // Total étudiant
                $sheet->getColumnDimension($this->colLetter($totalCol))->setWidth(14);

                // Freeze panes: keep header & student name visible while scrolling
                $sheet->freezePane('C'.($tableHeaderRow + 1));
            },
        ];
    }

    private function colLetter(int $index): string
    {
        // 1 → A, 2 → B, ..., 27 → AA
        $letters = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod).$letters;
            $index = intdiv($index - 1, 26);
        }

        return $letters;
    }

    private function letterToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;
        for ($i = 0, $n = strlen($letters); $i < $n; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return $index;
    }
}
