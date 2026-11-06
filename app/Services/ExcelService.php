<?php

namespace App\Services;

use App\Models\Urusan;
use App\Models\Indikator;
use App\Models\Tahun;
use App\Models\DataIndikator;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ExcelService
{
    /**
     * Export Urusan table data to an Excel (.xlsx) file.
     *
     * @param Urusan $urusan
     * @param int $activeTableId
     * @param int $tahunAwal
     * @param int $tahunAkhir
     * @param bool $isTemplate
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Urusan $urusan, int $activeTableId, int $tahunAwal, int $tahunAkhir, bool $isTemplate = false)
    {
        $tables = $urusan->tables;
        $activeTable = $tables[0] ?? ['id' => 1, 'name' => 'Tabel 1', 'columns' => []];
        foreach ($tables as $t) {
            if ((int) ($t['id'] ?? 0) === $activeTableId) {
                $activeTable = $t;
                break;
            }
        }
        $allColumns = $activeTable['columns'] ?? [];
        $activeColumns = $this->filterColumnsByYear($allColumns, $tahunAwal, $tahunAkhir);
        $hasSubColumns = collect($activeColumns)->contains(fn($c) => !empty($c['sub_columns']));

        $flattenedIndicators = $this->getFlattenedIndicators($urusan->id, $activeTableId);
        $hasYearRows = collect($flattenedIndicators)->contains(function ($ind) {
            return preg_match('/^(19|20)\d{2}$/', trim($ind->nama_indikator ?? ''));
        });
        if ($hasYearRows) {
            $flattenedIndicators = array_values(array_filter($flattenedIndicators, function ($ind) use ($tahunAwal, $tahunAkhir) {
                $name = trim($ind->nama_indikator ?? '');
                if (preg_match('/^(19|20)\d{2}$/', $name, $m)) {
                    $y = (int) $m[0];
                    return $y >= $tahunAwal && $y <= $tahunAkhir;
                }
                return true;
            }));
        }
        $tahuns = Tahun::all()->keyBy('tahun');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheetTitle = mb_substr($urusan->nama_urusan ?: 'Data Sektoral', 0, 30);
        $sheetTitle = preg_replace('/[\\\\\/?*\[\]:]/', '_', $sheetTitle);
        $sheet->setTitle($sheetTitle ?: 'Data Sektoral');
        $spreadsheet->getDefaultStyle()->getFont()->setName('Segoe UI')->setSize(10);

        $headerInfo = $this->buildHeader($sheet, $urusan, $activeColumns, $hasSubColumns, $tahuns, $tahunAwal, $tahunAkhir, $isTemplate);
        $flatCols = $headerInfo['flatCols'];
        $lastColLetter = $headerInfo['lastColLetter'];
        $headerRow1 = $headerInfo['headerRow1'];
        $headerEndRow = $headerInfo['headerEndRow'];

        $currentRow = $headerEndRow + 1;
        $dataStartRow = $currentRow;

        $currentRow = $this->writeDataRows($sheet, $flattenedIndicators, $flatCols, $currentRow, $lastColLetter, $isTemplate);

        // Baris Jumlah (Footer Total): Hanya tampilkan jika diaktifkan pada tabel ini atau via parameter
        if (request()->has('show_footer_total')) {
            $showFooterTotal = request()->boolean('show_footer_total');
        } else {
            $showFooterTotal = (bool)($activeTable['show_footer_total'] ?? true);
        }

        $showFooterJumlah = true;
        if (request()->has('show_footer_jumlah')) {
            $showFooterJumlah = request()->boolean('show_footer_jumlah');
        } else {
            $showFooterJumlah = (bool)($activeTable['show_footer_jumlah'] ?? true);
        }

        $showFooterSubtotal = true;
        if (request()->has('show_footer_subtotal')) {
            $showFooterSubtotal = request()->boolean('show_footer_subtotal');
        } else {
            $showFooterSubtotal = (bool)($activeTable['show_footer_subtotal'] ?? true);
        }

        if ($showFooterTotal && ($showFooterJumlah || $showFooterSubtotal) && count($flattenedIndicators) > 0) {
            $currentRow = $this->writeFooterTotal($sheet, $flatCols, $activeTable, $currentRow, $dataStartRow, $lastColLetter, $showFooterJumlah, $showFooterSubtotal);
        }

        $lastDataRow = $currentRow - 1;
        $this->applyStylingAndWidths($sheet, $flatCols, $headerRow1, $lastDataRow, $lastColLetter);

        $slugNama = Str::slug($urusan->nama_urusan ?: 'urusan');
        $filename = ($isTemplate ? 'Template_Import_' : 'Data_Sektoral_') . ($slugNama ?: 'urusan') . '_' . ($tahunAwal . '-' . $tahunAkhir) . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Filter active columns based on start and end year.
     *
     * @param array $allColumns
     * @param int $tahunAwal
     * @param int $tahunAkhir
     * @return array
     */
    private function filterColumnsByYear(array $allColumns, int $tahunAwal, int $tahunAkhir): array
    {
        $filterCols = function($cols) use (&$filterCols, $tahunAwal, $tahunAkhir) {
            $res = [];
            foreach ($cols as $col) {
                $cName = trim($col['name'] ?? '');
                if (preg_match('/^(19|20)\d{2}$/', $cName, $m)) {
                    $colYear = (int) $m[0];
                    if ($colYear >= $tahunAwal && $colYear <= $tahunAkhir) {
                        if (!empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                            $col['sub_columns'] = $filterCols($col['sub_columns']);
                        }
                        $res[] = $col;
                    }
                } else {
                    if (!empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                        $col['sub_columns'] = $filterCols($col['sub_columns']);
                    }
                    $res[] = $col;
                }
            }
            return $res;
        };
        return $filterCols($allColumns);
    }

    /**
     * Recursively load children with indicator data and years.
     *
     * @param iterable $items
     * @return void
     */
    private function loadChildrenRecursively($items): void
    {
        foreach ($items as $item) {
            $item->load(['children' => function ($q) {
                $q->orderBy('urutan')->with(['dataIndikators.tahun']);
            }]);
            if ($item->children && $item->children->count() > 0) {
                $this->loadChildrenRecursively($item->children);
            }
        }
    }

    /**
     * Build flattened indicator hierarchy tree.
     *
     * @param iterable $items
     * @param int $level
     * @param mixed $parentItem
     * @param string|null $parentNo
     * @param array $flattenedIndicators
     * @return void
     */
    private function buildTree($items, int $level, $parentItem, ?string $parentNo, array &$flattenedIndicators): void
    {
        foreach ($items as $idx => $item) {
            $item->tree_level = $level;
            $item->parentItem = $parentItem;
            $itemUrutan = $item->urutan ?: ($idx + 1);
            $currNo = $parentNo ? ($parentNo . '.' . $itemUrutan) : (string)$itemUrutan;
            $item->hierarchical_no = $currNo;
            $flattenedIndicators[] = $item;
            if ($item->children && $item->children->count() > 0) {
                $this->buildTree($item->children, $level + 1, $item, $currNo, $flattenedIndicators);
            }
        }
    }

    /**
     * Retrieve and flatten indicator hierarchy.
     *
     * @param int $urusanId
     * @param int $activeTableId
     * @return array
     */
    private function getFlattenedIndicators(int $urusanId, int $activeTableId): array
    {
        $rootIndikators = Indikator::where('urusan_id', $urusanId)
            ->where(function ($q) use ($activeTableId) {
                if ($activeTableId === 1) {
                    $q->where('table_id', 1)->orWhereNull('table_id');
                } else {
                    $q->where('table_id', $activeTableId);
                }
            })
            ->whereNull('parent_id')
            ->orderBy('urutan')
            ->with(['children.dataIndikators.tahun', 'dataIndikators.tahun'])
            ->get();

        $this->loadChildrenRecursively($rootIndikators);

        $flattenedIndicators = [];
        $this->buildTree($rootIndikators, 0, null, null, $flattenedIndicators);

        return $flattenedIndicators;
    }

    /**
     * Recursive calculation for leaf sub-column metric sum.
     *
     * @param mixed $indicator
     * @param string|int $subId
     * @param int|null $colYearId
     * @return float|null
     */
    private function getExcelLeafSubVal($indicator, $subId, ?int $colYearId): ?float
    {
        if ($indicator->children && $indicator->children->count() > 0) {
            $sum = 0.0;
            $hasVal = false;
            foreach ($indicator->children as $c) {
                $cv = $this->getExcelLeafSubVal($c, $subId, $colYearId);
                if ($cv !== null) {
                    $sum += $cv;
                    $hasVal = true;
                }
            }
            return $hasVal ? $sum : null;
        }

        $di = $colYearId ? $indicator->dataIndikators->firstWhere('tahun_id', $colYearId) : $indicator->dataIndikators->first();
        if ($di) {
            $v = $di->getCustomValue($subId);
            if ($v !== null && $v !== '') {
                $norm = str_replace(',', '.', trim((string)$v));
                if (is_numeric($norm)) {
                    return (float)$norm;
                }
            }
        }

        return null;
    }

    /**
     * Recursive calculation for leaf column metric sum.
     *
     * @param mixed $indicator
     * @param string|int $colId
     * @param int|null $yearId
     * @return float|null
     */
    private function getExcelLeafColVal($indicator, $colId, ?int $yearId): ?float
    {
        if ($indicator->children && $indicator->children->count() > 0) {
            $sum = 0.0;
            $hasVal = false;
            foreach ($indicator->children as $c) {
                $cv = $this->getExcelLeafColVal($c, $colId, $yearId);
                if ($cv !== null) {
                    $sum += $cv;
                    $hasVal = true;
                }
            }
            return $hasVal ? $sum : null;
        }

        $cDi = $yearId ? $indicator->dataIndikators->firstWhere('tahun_id', $yearId) : $indicator->dataIndikators->first();
        if ($cDi) {
            $cv = $cDi->getCustomValue($colId);
            if ($cv === null || $cv === '') {
                $cv = $cDi->nilai;
            }
            if ($cv !== null && $cv !== '') {
                $norm = str_replace(',', '.', trim((string)$cv));
                if (is_numeric($norm)) {
                    return (float)$norm;
                }
            }
        }

        return null;
    }

    /**
     * Build title and table headers on worksheet.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param Urusan $urusan
     * @param array $activeColumns
     * @param bool $hasSubColumns
     * @param mixed $tahuns
     * @param int $tahunAwal
     * @param int $tahunAkhir
     * @param bool $isTemplate
     * @return array
     */
    private function buildHeader($sheet, Urusan $urusan, array $activeColumns, bool $hasSubColumns, $tahuns, int $tahunAwal, int $tahunAkhir, bool $isTemplate): array
    {
        // Title Block
        $sheet->setCellValue('A1', 'PEMERINTAH KOTA TASIKMALAYA');
        $sheet->getStyle('A1')->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('64748B');

        $sheet->setCellValue('A2', 'BAPPERIDA KOTA TASIKMALAYA');
        $sheet->getStyle('A2')->getFont()->setSize(13)->setBold(true)->getColor()->setRGB('0369A1');

        $titlePrefix = $isTemplate ? 'TEMPLATE IMPORT DATA STATISTIK SEKTORAL: ' : 'DATA STATISTIK SEKTORAL: ';
        $sheet->setCellValue('A3', $titlePrefix . strtoupper($urusan->nama_urusan));
        $sheet->getStyle('A3')->getFont()->setSize(11)->setBold(true)->getColor()->setRGB('0F172A');

        $metaInfo = 'Kelompok Urusan: ' . ($urusan->kelompokUrusan->nama ?? '-') .
            '   |   Periode: Tahun ' . $tahunAwal . ' s/d ' . $tahunAkhir;
        $sheet->setCellValue('A4', $metaInfo);
        $sheet->getStyle('A4')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('475569');

        // Setup Headers dynamically supporting multi-level tree
        $getNodeDepth = function($col) use (&$getNodeDepth) {
            if (empty($col['sub_columns']) || !is_array($col['sub_columns'])) {
                return 1;
            }
            $maxSub = 1;
            foreach ($col['sub_columns'] as $sub) {
                $maxSub = max($maxSub, $getNodeDepth($sub));
            }
            return 1 + $maxSub;
        };

        $getLeafCount = function($col) use (&$getLeafCount) {
            if (empty($col['sub_columns']) || !is_array($col['sub_columns'])) {
                return 1;
            }
            $count = 0;
            foreach ($col['sub_columns'] as $sub) {
                $count += $getLeafCount($sub);
            }
            return max(1, $count);
        };

        $maxHeaderDepth = 1;
        foreach ($activeColumns as $c) {
            $maxHeaderDepth = max($maxHeaderDepth, $getNodeDepth($c));
        }
        $hasSubColumns = $maxHeaderDepth > 1;

        $headerRow1 = 6;
        $headerEndRow = $headerRow1 + $maxHeaderDepth - 1;

        $firstDescriptorCol = collect($activeColumns)->first(function ($c) {
            $ln = strtolower(trim($c['name'] ?? ''));
            return !in_array($ln, ['no', 'no.', 'nomor']) && empty($c['sub_columns']) && ($c['type'] ?? 'text') !== 'number';
        });
        $firstDescriptorColId = $firstDescriptorCol['id'] ?? null;

        $currentColIdx = 1;
        $flatCols = [];

        $renderNode = function($node, $depth, $ancestors) use (&$renderNode, &$currentColIdx, &$flatCols, $sheet, $headerRow1, $maxHeaderDepth, $getLeafCount, $tahuns, $firstDescriptorColId) {
            $nodeName = trim($node['name'] ?? '');
            $hasSubs = !empty($node['sub_columns']) && is_array($node['sub_columns']);
            $leafCount = $getLeafCount($node);
            $startCol = $currentColIdx;
            $rowNum = $headerRow1 + $depth;

            if ($hasSubs) {
                $startLetter = Coordinate::stringFromColumnIndex($startCol);
                $endLetter = Coordinate::stringFromColumnIndex($startCol + $leafCount - 1);
                if ($startCol < ($startCol + $leafCount - 1)) {
                    $sheet->mergeCells("{$startLetter}{$rowNum}:{$endLetter}{$rowNum}");
                }
                $sheet->setCellValue("{$startLetter}{$rowNum}", $nodeName);

                $newAncestors = array_merge($ancestors, [$node]);
                foreach ($node['sub_columns'] as $sub) {
                    $renderNode($sub, $depth + 1, $newAncestors);
                }
            } else {
                $curLetter = Coordinate::stringFromColumnIndex($startCol);
                $rowSpan = $maxHeaderDepth - $depth;
                if ($rowSpan > 1) {
                    $endRow = $rowNum + $rowSpan - 1;
                    $sheet->mergeCells("{$curLetter}{$rowNum}:{$curLetter}{$endRow}");
                }
                $sheet->setCellValue("{$curLetter}{$rowNum}", $nodeName);

                // Leaf column metadata
                $lowerName = strtolower($nodeName);
                $isNo = empty($ancestors) && in_array($lowerName, ['no', 'no.', 'nomor']);
                $isNama = empty($ancestors) && ($node['id'] === 'col_nama' || $node['id'] === 'nama_indikator' || in_array($lowerName, ['nama', 'indikator', 'nama indikator', 'uraian', 'nama/uraian']) || ($firstDescriptorColId && $node['id'] === $firstDescriptorColId));
                $isSatuan = empty($ancestors) && ($node['id'] === 'col_satuan' || $lowerName === 'satuan');
                $isTotal = in_array($lowerName, ['jumlah', 'total', 'jml', 'jmlh']) || ($node['is_total'] ?? false);

                // Year detection from leaf or ancestors
                $leafYrNum = null;
                $chain = array_merge([$node], array_reverse($ancestors));
                foreach ($chain as $item) {
                    if (preg_match('/^(19|20)\d{2}$/', trim($item['name'] ?? ''), $mYr)) {
                        $leafYrNum = (int)$mYr[0];
                        break;
                    }
                }
                $leafYearId = ($leafYrNum && isset($tahuns[$leafYrNum])) ? $tahuns[$leafYrNum]->id : null;

                $rootCol = !empty($ancestors) ? $ancestors[0] : $node;

                $flatCols[] = [
                    'col_letter' => $curLetter,
                    'col_index' => $startCol,
                    'col' => $rootCol,
                    'sub' => !empty($ancestors) ? $node : null,
                    'ancestors' => $ancestors,
                    'is_sub' => !empty($ancestors),
                    'col_id' => $rootCol['id'],
                    'sub_id' => !empty($ancestors) ? $node['id'] : null,
                    'year_num' => $leafYrNum,
                    'year_id' => $leafYearId,
                    'is_total' => $isTotal,
                    'is_no' => $isNo,
                    'is_nama' => $isNama,
                    'is_satuan' => $isSatuan,
                ];

                $currentColIdx++;
            }
        };

        foreach ($activeColumns as $col) {
            $renderNode($col, 0, []);
        }

        $lastColLetter = Coordinate::stringFromColumnIndex(max(1, $currentColIdx - 1));

        // Merge title banner rows across the width of table so they do not stretch column A
        if ($currentColIdx > 2) {
            $sheet->mergeCells("A1:{$lastColLetter}1");
            $sheet->mergeCells("A2:{$lastColLetter}2");
            $sheet->mergeCells("A3:{$lastColLetter}3");
            $sheet->mergeCells("A4:{$lastColLetter}4");
        }

        // Header Styling
        // Row 6 Main Header Style
        $sheet->getStyle("A{$headerRow1}:{$lastColLetter}{$headerRow1}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0284C7'], // Primary Ocean Blue
            ],
        ]);

        // Row 7 (Sub-Header Style, depth 2)
        if ($maxHeaderDepth >= 2) {
            $row2 = $headerRow1 + 1;
            $sheet->getStyle("A{$row2}:{$lastColLetter}{$row2}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '0369A1'],
                    'size' => 9,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0F2FE'], // Ice Light Blue
                ],
            ]);
            $sheet->getRowDimension($row2)->setRowHeight(24);
        }

        // Row 8 (Nested Sub-Header Style, depth 3)
        if ($maxHeaderDepth >= 3) {
            $row3 = $headerRow1 + 2;
            $sheet->getStyle("A{$row3}:{$lastColLetter}{$row3}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '0F172A'],
                    'size' => 8.5,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F1F5F9'], // Slate 100
                ],
            ]);
            $sheet->getRowDimension($row3)->setRowHeight(22);
        }

        $sheet->getRowDimension($headerRow1)->setRowHeight(28);

        return [
            'flatCols' => $flatCols,
            'lastColLetter' => $lastColLetter,
            'headerRow1' => $headerRow1,
            'headerRow2' => $headerRow1 + 1,
            'headerEndRow' => $headerEndRow,
        ];
    }

    /**
     * Populate indicator data rows into worksheet.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $flattenedIndicators
     * @param array $flatCols
     * @param int $currentRow
     * @param string $lastColLetter
     * @param bool $isTemplate
     * @return int Next current row
     */
    private function writeDataRows($sheet, array $flattenedIndicators, array $flatCols, int $currentRow, string $lastColLetter, bool $isTemplate): int
    {
        if (count($flattenedIndicators) === 0) {
            $sheet->mergeCells("A{$currentRow}:{$lastColLetter}{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'Belum ada data indikator yang terdaftar pada tabel ini.');
            $sheet->getStyle("A{$currentRow}")->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '94A3B8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($currentRow)->setRowHeight(30);
            return $currentRow + 1;
        }

        foreach ($flattenedIndicators as $loopIdx => $ind) {
            $level = $ind->tree_level ?? 0;
            $hasIndukChildren = $ind->children && $ind->children->count() > 0;
            $valObj = $ind->dataIndikators->first();

            $sheet->getRowDimension($currentRow)->setRowHeight(22);

            foreach ($flatCols as $fCol) {
                $cellCoordinate = $fCol['col_letter'] . $currentRow;
                $val = null;

                if ($fCol['is_sub']) {
                    $subId = $fCol['sub_id'];
                    $isTot = $fCol['is_total'];
                    $colYearId = $fCol['year_id'];

                    if ($hasIndukChildren) {
                        if ($isTot && !empty($fCol['col']['sub_columns'])) {
                            $parentTot = 0.0;
                            $hasAnyTot = false;
                            foreach ($fCol['col']['sub_columns'] as $sibling) {
                                if ($sibling['id'] === $subId) {
                                    break;
                                }
                                if (!in_array(strtolower(trim($sibling['name'])), ['jumlah', 'total', 'jml', 'jmlh'])) {
                                    $sv = $this->getExcelLeafSubVal($ind, $sibling['id'], $colYearId);
                                    if ($sv !== null) {
                                        $parentTot += $sv;
                                        $hasAnyTot = true;
                                    }
                                }
                            }
                            $val = $hasAnyTot ? $parentTot : 0;
                        } else {
                            $cSubSum = $this->getExcelLeafSubVal($ind, $subId, $colYearId);
                            $val = ($cSubSum !== null) ? $cSubSum : 0;
                        }
                    } else {
                        $di = $colYearId 
                            ? $ind->dataIndikators->firstWhere('tahun_id', $colYearId) 
                            : $ind->dataIndikators->first();
                        if ($di) {
                            $v = $di->getCustomValue($subId);
                            if ($v !== null && $v !== '') {
                                $val = is_numeric($v) ? (float) $v : $v;
                            }
                        }

                        // Fallback auto-sum for total column
                        if (($val === null || $val === '') && $isTot && !empty($fCol['col']['sub_columns'])) {
                            $sSum = 0.0;
                            $hasS = false;
                            foreach ($fCol['col']['sub_columns'] as $sibling) {
                                if (!in_array(strtolower(trim($sibling['name'])), ['jumlah', 'total', 'jml', 'jmlh'])) {
                                    $sv = $di ? $di->getCustomValue($sibling['id']) : null;
                                    if ($sv !== null && is_numeric($sv)) {
                                        $sSum += (float) $sv;
                                        $hasS = true;
                                    }
                                }
                            }
                            if ($hasS) {
                                $val = $sSum;
                            }
                        }
                    }

                } else {
                    // Non-sub column
                    if ($fCol['is_nama']) {
                        $val = ($level > 0 ? str_repeat('   ', $level) : '') . $ind->nama_indikator;
                    } elseif ($fCol['is_satuan']) {
                        $val = $ind->satuan ?: ($valObj ? $valObj->satuan : '');
                    } elseif ($fCol['is_no']) {
                        $val = (string) ($ind->hierarchical_no ?? ($ind->urutan ?: ($loopIdx + 1)));
                    } else {
                        if ($hasIndukChildren) {
                            $cSum = $this->getExcelLeafColVal($ind, $fCol['col_id'], $fCol['year_id']);
                            if ($cSum !== null) {
                                $val = $cSum;
                            }
                        } else {
                            $di = $fCol['year_id']
                                ? $ind->dataIndikators->firstWhere('tahun_id', $fCol['year_id'])
                                : $ind->dataIndikators->first();
                            if ($di) {
                                $cv = $di->getCustomValue($fCol['col_id']) ?? $di->nilai;
                                if ($cv !== null && $cv !== '') {
                                    $val = is_numeric($cv) ? (float) $cv : $cv;
                                }
                            }
                        }
                    }
                }

                // Write cell value
                if ($val !== null && $val !== '') {
                    if (is_numeric($val)) {
                        $sheet->setCellValueExplicit($cellCoordinate, $val, DataType::TYPE_NUMERIC);
                        $sheet->getStyle($cellCoordinate)->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    } else {
                        $sheet->setCellValue($cellCoordinate, $val);
                        if ($fCol['is_no'] || $fCol['is_satuan']) {
                            $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        } else {
                            $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        }
                    }
                } else {
                    $sheet->setCellValue($cellCoordinate, $isTemplate ? '' : '—');
                    $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $sheet->getStyle($cellCoordinate)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }

            // Row Highlight for Induk and Sub-Induk
            if ($hasIndukChildren) {
                $bgColor = ($level === 0) ? 'E2EEFB' : 'F0F7FE';
                $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $bgColor],
                    ],
                ]);
            }

            $currentRow++;
        }

        return $currentRow;
    }

    /**
     * Write footer total row if enabled.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $flatCols
     * @param array $activeTable
     * @param int $currentRow
     * @param int $dataStartRow
     * @param string $lastColLetter
     * @return int Next current row
     */
    private function writeFooterTotal($sheet, array $flatCols, array $activeTable, int $currentRow, int $dataStartRow, string $lastColLetter, bool $showFooterJumlah = true, bool $showFooterSubtotal = true): int
    {
        $footerLabel = $activeTable['footer_label'] ?? 'Jumlah';
        $footerSubtotalLabel = $activeTable['footer_subtotal_label'] ?? 'TOTAL';
        $footerOverrides = $activeTable['footer_overrides'] ?? [];
        $dataEndRow = $currentRow - 1;

        // Cek apakah ada Sub-Kolom untuk Baris 2 (Total Gabungan Sub-Kolom)
        $footerParentGroups = [];
        $currentGroup = null;
        $hasGroupWithSubs = false;

        foreach ($flatCols as $fCol) {
            $parent = !empty($fCol['ancestors']) ? end($fCol['ancestors']) : null;
            $parentId = $parent['id'] ?? null;
            $parentName = $parent['name'] ?? null;

            if ($parentId !== null) {
                if ($currentGroup && $currentGroup['type'] === 'parent' && $currentGroup['id'] === $parentId) {
                    $currentGroup['colspan']++;
                    $currentGroup['end_col_letter'] = $fCol['col_letter'];
                    $currentGroup['leaf_cols'][] = $fCol;
                } else {
                    if ($currentGroup) {
                        $footerParentGroups[] = $currentGroup;
                    }
                    $currentGroup = [
                        'type' => 'parent',
                        'id' => $parentId,
                        'name' => $parentName,
                        'colspan' => 1,
                        'start_col_letter' => $fCol['col_letter'],
                        'end_col_letter' => $fCol['col_letter'],
                        'leaf_cols' => [$fCol],
                        'parent_obj' => $parent,
                    ];
                }
            } else {
                if ($currentGroup) {
                    $footerParentGroups[] = $currentGroup;
                    $currentGroup = null;
                }
                $footerParentGroups[] = [
                    'type' => 'leaf',
                    'id' => $fCol['col_id'],
                    'name' => $fCol['col']['name'] ?? '',
                    'colspan' => 1,
                    'start_col_letter' => $fCol['col_letter'],
                    'end_col_letter' => $fCol['col_letter'],
                    'leaf_cols' => [$fCol],
                    'fCol' => $fCol,
                ];
            }
        }
        if ($currentGroup) {
            $footerParentGroups[] = $currentGroup;
        }

        foreach ($footerParentGroups as $g) {
            if ($g['type'] === 'parent' && $g['colspan'] > 1) {
                $hasGroupWithSubs = true;
                break;
            }
        }

        // Jika tidak ada sub-kolom dan user memilih Total Saja:
        $actualJumlahLabel = $footerLabel;
        if (!$hasGroupWithSubs && $showFooterSubtotal && !$showFooterJumlah) {
            $actualJumlahLabel = $footerSubtotalLabel;
            $showFooterJumlah = true;
            $showFooterSubtotal = false;
        }

        $row1Written = false;
        $row1Index = null;

        // 1. Tulis Baris 1: JUMLAH (jika diaktifkan)
        if ($showFooterJumlah) {
            $footerRow = $currentRow;
            $row1Index = $footerRow;
            $row1Written = true;

            foreach ($flatCols as $fCol) {
                $cLetter = $fCol['col_letter'];
                $cellCoord = "{$cLetter}{$footerRow}";
                $colKey = $fCol['is_sub'] ? $fCol['sub_id'] : $fCol['col_id'];

                if ($fCol['is_nama']) {
                    $sheet->setCellValue($cellCoord, $actualJumlahLabel);
                    $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                } elseif ($fCol['is_no']) {
                    $sheet->setCellValue($cellCoord, '');
                } elseif ($fCol['is_satuan']) {
                    $sheet->setCellValue($cellCoord, '—');
                    $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                } else {
                    if (isset($footerOverrides[$colKey]) && $footerOverrides[$colKey] !== '') {
                        $ov = $footerOverrides[$colKey];
                        if (is_numeric($ov)) {
                            $sheet->setCellValueExplicit($cellCoord, (float)$ov, DataType::TYPE_NUMERIC);
                            $sheet->getStyle($cellCoord)->getNumberFormat()->setFormatCode('#,##0');
                        } else {
                            $sheet->setCellValue($cellCoord, $ov);
                        }
                    } else {
                        $sheet->setCellValue($cellCoord, "=SUM({$cLetter}{$dataStartRow}:{$cLetter}{$dataEndRow})");
                        $sheet->getStyle($cellCoord)->getNumberFormat()->setFormatCode('#,##0');
                    }
                    $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            }

            $sheet->getStyle("A{$footerRow}:{$lastColLetter}{$footerRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '0F172A'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F1F5F9'],
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '000000'],
                    ],
                    'bottom' => [
                        'borderStyle' => ($hasGroupWithSubs && $showFooterSubtotal) ? Border::BORDER_THIN : Border::BORDER_DOUBLE,
                        'color' => ($hasGroupWithSubs && $showFooterSubtotal) ? ['rgb' => '94A3B8'] : ['rgb' => '000000'],
                    ],
                ],
            ]);

            $currentRow++;
        }

        // 2. Tulis Baris 2: TOTAL (jika ada sub-kolom dan diaktifkan)
        if ($hasGroupWithSubs && $showFooterSubtotal) {
            $subtotalRow = $currentRow;

            foreach ($footerParentGroups as $g) {
                $startL = $g['start_col_letter'];
                $endL = $g['end_col_letter'];
                $cellCoord = "{$startL}{$subtotalRow}";

                if ($g['type'] === 'leaf') {
                    $fCol = $g['fCol'];
                    if ($fCol['is_nama']) {
                        $sheet->setCellValue($cellCoord, $footerSubtotalLabel);
                        $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    } elseif ($fCol['is_no']) {
                        $sheet->setCellValue($cellCoord, '');
                    } else {
                        $sheet->setCellValue($cellCoord, '—');
                        $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                } else {
                    $pId = $g['id'];
                    $pKey = 'subtotal_' . $pId;

                    if ($g['colspan'] > 1) {
                        $sheet->mergeCells("{$startL}{$subtotalRow}:{$endL}{$subtotalRow}");
                    }

                    if (isset($footerOverrides[$pKey]) && $footerOverrides[$pKey] !== '') {
                        $ov = $footerOverrides[$pKey];
                        if (is_numeric($ov)) {
                            $sheet->setCellValueExplicit($cellCoord, (float)$ov, DataType::TYPE_NUMERIC);
                            $sheet->getStyle($cellCoord)->getNumberFormat()->setFormatCode('#,##0');
                        } else {
                            $sheet->setCellValue($cellCoord, $ov);
                        }
                    } else {
                        if ($row1Written && $row1Index) {
                            $sheet->setCellValue($cellCoord, "=SUM({$startL}{$row1Index}:{$endL}{$row1Index})");
                        } else {
                            $sheet->setCellValue($cellCoord, "=SUM({$startL}{$dataStartRow}:{$endL}{$dataEndRow})");
                        }
                        $sheet->getStyle($cellCoord)->getNumberFormat()->setFormatCode('#,##0');
                    }
                    $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            }

            $sheet->getStyle("A{$subtotalRow}:{$lastColLetter}{$subtotalRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '0369A1'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0F2FE'],
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => $row1Written ? Border::BORDER_THIN : Border::BORDER_MEDIUM,
                        'color' => $row1Written ? ['rgb' => '94A3B8'] : ['rgb' => '000000'],
                    ],
                    'bottom' => [
                        'borderStyle' => Border::BORDER_DOUBLE,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);

            $currentRow++;
        }

        return $currentRow;
    }

    /**
     * Apply table borders, gridlines, and column widths.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $flatCols
     * @param int $headerRow1
     * @param int $lastDataRow
     * @param string $lastColLetter
     * @return void
     */
    private function applyStylingAndWidths($sheet, array $flatCols, int $headerRow1, int $lastDataRow, string $lastColLetter): void
    {
        // Apply All Borders to entire table (Header, Sub-header & Data)
        $sheet->getStyle("A{$headerRow1}:{$lastColLetter}{$lastDataRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $sheet->setShowGridLines(true);

        // Set clean proportional column widths
        foreach ($flatCols as $fCol) {
            $colLetter = $fCol['col_letter'];
            $colDim = $sheet->getColumnDimension($colLetter);
            $colDim->setAutoSize(false);

            if ($fCol['is_no']) {
                $colDim->setWidth(7); // Kolom No kompak dan proporsional
            } elseif ($fCol['is_nama']) {
                $colDim->setWidth(38); // Kolom Uraian leluasa untuk nama indikator
            } elseif ($fCol['is_satuan']) {
                $colDim->setWidth(11); // Kolom Satuan cukup untuk Unit, Org, %, dll
            } elseif ($fCol['is_sub']) {
                $colDim->setWidth(11); // Sub-kolom (Negeri, Swasta, Jumlah)
            } else {
                $colDim->setWidth(13); // Kolom data lainnya
            }
        }
    }

    /**
     * Import data from uploaded Excel/CSV file into the specified table of an Urusan.
     */
    public function import($file, Urusan $urusan, int $activeTableId, array $options = []): array
    {
        $createMissing = $options['create_missing'] ?? true;
        $updateExisting = $options['update_existing'] ?? true;

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColIndex = Coordinate::columnIndexFromString($highestColumn);

        if ($highestRow < 2) {
            return [
                'success' => false,
                'message' => 'File Excel kosong atau tidak memiliki baris data.',
                'processed' => 0,
            ];
        }

        // Get active table config
        $tables = $urusan->tables;
        $activeTable = $tables[0] ?? ['id' => 1, 'name' => 'Tabel 1', 'columns' => []];
        foreach ($tables as $t) {
            if ((int) ($t['id'] ?? 0) === $activeTableId) {
                $activeTable = $t;
                break;
            }
        }
        $tableColumns = $activeTable['columns'] ?? [];

        // 1. Locate Header Row (Search first 15 rows)
        $headerRow1 = null;
        for ($r = 1; $r <= min(15, $highestRow); $r++) {
            for ($c = 1; $c <= $highestColIndex; $c++) {
                $cellVal = strtolower(trim((string) $sheet->getCell([$c, $r])->getValue()));
                if (in_array($cellVal, ['uraian', 'nama', 'indikator', 'nama indikator', 'no', 'nomor'])) {
                    $headerRow1 = $r;
                    break 2;
                }
            }
        }

        if (!$headerRow1) {
            $headerRow1 = 1; // Fallback to row 1
        }

        // 2. Check if sub-header row exists in row $headerRow1 + 1
        $headerRow2 = null;
        $hasSubHeader = false;
        $testRow = $headerRow1 + 1;
        if ($testRow <= $highestRow) {
            for ($c = 1; $c <= $highestColIndex; $c++) {
                $subVal = strtolower(trim((string) $sheet->getCell([$c, $testRow])->getValue()));
                if (in_array($subVal, ['negeri', 'swasta', 'jumlah', 'total', 'l', 'p', 'laki-laki', 'perempuan', 'jml'])) {
                    $hasSubHeader = true;
                    $headerRow2 = $testRow;
                    break;
                }
            }
        }

        $dataStartRow = $hasSubHeader ? ($headerRow2 + 1) : ($headerRow1 + 1);

        // Preload years
        $tahuns = Tahun::all()->keyBy('tahun');

        // 3. Map Excel Columns to Table Model
        $colMappings = [];
        $lastParentName = '';

        for ($c = 1; $c <= $highestColIndex; $c++) {
            $letter = Coordinate::stringFromColumnIndex($c);
            $val1 = trim((string) $sheet->getCell([$c, $headerRow1])->getValue());
            if ($val1 !== '') {
                $lastParentName = $val1;
            }
            $parentColName = $val1 !== '' ? $val1 : $lastParentName;
            $subColName = $hasSubHeader ? trim((string) $sheet->getCell([$c, $headerRow2])->getValue()) : '';

            $lowerParent = strtolower($parentColName);
            $lowerSub = strtolower($subColName);

            $mapping = [
                'col_index' => $c,
                'col_letter' => $letter,
                'parent_name' => $parentColName,
                'sub_name' => $subColName,
                'type' => 'unknown',
            ];

            // Match Descriptors
            if (in_array($lowerParent, ['no', 'no.', 'nomor'])) {
                $mapping['type'] = 'no';
            } elseif (in_array($lowerParent, ['uraian', 'nama', 'indikator', 'nama indikator', 'nama/uraian'])) {
                $mapping['type'] = 'nama_indikator';
            } elseif (in_array($lowerParent, ['satuan', 'col_satuan'])) {
                $mapping['type'] = 'satuan';
            } elseif (preg_match('/^(19|20)\d{2}$/', $parentColName, $mYr)) {
                $yr = (int) $mYr[0];
                $tahunModel = $tahuns[$yr] ?? Tahun::firstOrCreate(['tahun' => $yr], ['status_aktif' => true, 'is_default' => false]);
                $mapping['type'] = 'year';
                $mapping['year_num'] = $yr;
                $mapping['tahun_id'] = $tahunModel->id;

                // Match with table columns
                $matchedTableCol = collect($tableColumns)->first(function ($tc) use ($yr) {
                    return preg_match('/^(19|20)\d{2}$/', trim($tc['name'] ?? ''), $m) && (int)$m[0] === $yr;
                });

                if ($matchedTableCol && !empty($matchedTableCol['sub_columns']) && !empty($subColName)) {
                    // Match sub-column
                    $matchedSub = collect($matchedTableCol['sub_columns'])->first(function ($sc) use ($lowerSub) {
                        return strtolower(trim($sc['name'] ?? '')) === $lowerSub;
                    });
                    if ($matchedSub) {
                        $mapping['sub_id'] = $matchedSub['id'];
                        $mapping['is_sub'] = true;
                    }
                } else if ($matchedTableCol) {
                    $mapping['col_id'] = $matchedTableCol['id'];
                    $mapping['is_sub'] = false;
                }
            } else {
                // Check if matches custom column in table
                $matchedCust = collect($tableColumns)->first(function ($tc) use ($lowerParent) {
                    return strtolower(trim($tc['name'] ?? '')) === $lowerParent;
                });
                if ($matchedCust) {
                    $mapping['type'] = 'custom';
                    $mapping['col_id'] = $matchedCust['id'];
                }
            }

            $colMappings[$c] = $mapping;
        }

        // Find which column is `nama_indikator`
        $namaColIdx = null;
        foreach ($colMappings as $cIdx => $m) {
            if ($m['type'] === 'nama_indikator') {
                $namaColIdx = $cIdx;
                break;
            }
        }
        if (!$namaColIdx) {
            // Fallback: column 2 or column 1
            $namaColIdx = isset($colMappings[2]) ? 2 : 1;
        }

        // 4. Process Data Rows
        $processedCount = 0;
        $createdCount = 0;
        $updatedCount = 0;

        $existingIndikators = Indikator::where('urusan_id', $urusan->id)
            ->where(function ($q) use ($activeTableId) {
                if ($activeTableId === 1) {
                    $q->where('table_id', 1)->orWhereNull('table_id');
                } else {
                    $q->where('table_id', $activeTableId);
                }
            })
            ->get();

        $maxUrutan = $existingIndikators->max('urutan') ?: 0;
        $currentParentId = null;

        $defTahun = Tahun::where('is_default', true)->first() ?: Tahun::first();
        $defaultTahunId = $defTahun ? $defTahun->id : 1;

        DB::transaction(function () use (
            $dataStartRow,
            $highestRow,
            $sheet,
            $namaColIdx,
            $highestColIndex,
            $options,
            $colMappings,
            $existingIndikators,
            &$maxUrutan,
            $createMissing,
            $urusan,
            $activeTableId,
            &$processedCount,
            &$createdCount,
            &$updatedCount,
            $defaultTahunId
        ) {
            for ($r = $dataStartRow; $r <= $highestRow; $r++) {
                $rawNama = trim((string) $sheet->getCell([$namaColIdx, $r])->getValue());

                // Skip empty rows or meta rows
                if ($rawNama === '' || stripos($rawNama, 'belum ada data') !== false || stripos($rawNama, 'total') === 0 && !isset($options['import_total_row'])) {
                    // If entire row is empty, skip
                    $hasAnyVal = false;
                    for ($c = 1; $c <= $highestColIndex; $c++) {
                        if (trim((string) $sheet->getCell([$c, $r])->getValue()) !== '') {
                            $hasAnyVal = true;
                            break;
                        }
                    }
                    if (!$hasAnyVal) continue;
                }

                // Extract Satuan and No
                $satuanVal = null;
                $noVal = null;
                foreach ($colMappings as $cIdx => $m) {
                    if ($m['type'] === 'satuan') {
                        $satuanVal = trim((string) $sheet->getCell([$cIdx, $r])->getValue()) ?: null;
                    } elseif ($m['type'] === 'no') {
                        $noVal = trim((string) $sheet->getCell([$cIdx, $r])->getValue()) ?: null;
                    }
                }

                // Clean nama indikator
                $cleanNama = preg_replace('/^\d+(\.\d+)*\s*[\-\.]*\s*/', '', $rawNama);
                $cleanNama = trim($cleanNama) ?: $rawNama;

                // Match existing indicator
                $indikator = $existingIndikators->first(function ($item) use ($cleanNama, $rawNama) {
                    $iName = strtolower(trim($item->nama_indikator));
                    $cName = strtolower(trim($cleanNama));
                    $rName = strtolower(trim($rawNama));
                    return $iName === $cName || $iName === $rName;
                });

                if (!$indikator) {
                    // Try fuzzy/partial or sub-name match
                    $indikator = $existingIndikators->first(function ($item) use ($cleanNama) {
                        $iName = strtolower(trim($item->nama_indikator));
                        $cName = strtolower(trim($cleanNama));
                        return str_ends_with($cName, $iName) || str_ends_with($iName, $cName);
                    });
                }

                if (!$indikator && $createMissing) {
                    $maxUrutan++;
                    $indikator = Indikator::create([
                        'urusan_id' => $urusan->id,
                        'table_id' => $activeTableId,
                        'nama_indikator' => $cleanNama,
                        'satuan' => $satuanVal,
                        'urutan' => $maxUrutan,
                        'status_aktif' => true,
                    ]);
                    $existingIndikators->push($indikator);
                    $createdCount++;
                } elseif ($indikator) {
                    if ($satuanVal && empty($indikator->satuan)) {
                        $indikator->satuan = $satuanVal;
                        $indikator->save();
                    }
                    $updatedCount++;
                } else {
                    continue;
                }

                // Update/Create Data Values for mapped columns
                foreach ($colMappings as $cIdx => $m) {
                    $rawVal = $sheet->getCell([$cIdx, $r])->getValue();
                    if ($rawVal === null || $rawVal === '' || $rawVal === '—') {
                        continue;
                    }

                    $cleanVal = is_string($rawVal) ? trim($rawVal) : $rawVal;
                    // Normalize indonesian formatted number: e.g. "1.250" or "12,5"
                    if (is_string($cleanVal) && preg_match('/^-?[\d\.,]+$/', $cleanVal)) {
                        if (strpos($cleanVal, ',') !== false && strpos($cleanVal, '.') !== false) {
                            // e.g. "1.250,50" -> "1250.50"
                            $cleanVal = str_replace('.', '', $cleanVal);
                            $cleanVal = str_replace(',', '.', $cleanVal);
                        } elseif (strpos($cleanVal, ',') !== false) {
                            // e.g. "12,5" -> "12.5"
                            $cleanVal = str_replace(',', '.', $cleanVal);
                        }
                    }

                    if ($m['type'] === 'year') {
                        $tahunId = $m['tahun_id'];
                        $di = DataIndikator::firstOrNew([
                            'indikator_id' => $indikator->id,
                            'tahun_id' => $tahunId,
                        ]);

                        $cFields = $di->custom_fields ?? [];
                        if (!is_array($cFields)) $cFields = [];

                        if (!empty($m['is_sub']) && !empty($m['sub_id'])) {
                            $cFields[$m['sub_id']] = (string) $cleanVal;
                        } elseif (!empty($m['col_id'])) {
                            $cFields[$m['col_id']] = (string) $cleanVal;
                            $di->nilai = (string) $cleanVal;
                        } else {
                            $di->nilai = (string) $cleanVal;
                        }

                        $di->custom_fields = $cFields;
                        $di->updated_by = auth()->id() ?? 1;
                        $di->save();

                    } elseif ($m['type'] === 'custom' && !empty($m['col_id'])) {
                        $tahunId = $defaultTahunId;

                        $di = DataIndikator::firstOrNew([
                            'indikator_id' => $indikator->id,
                            'tahun_id' => $tahunId,
                        ]);

                        $cFields = $di->custom_fields ?? [];
                        if (!is_array($cFields)) $cFields = [];
                        $cFields[$m['col_id']] = (string) $cleanVal;

                        $di->custom_fields = $cFields;
                        $di->updated_by = auth()->id() ?? 1;
                        $di->save();
                    }
                }

                $processedCount++;
            }
        });

        ActivityLog::record(
            'import_excel',
            'Mengimpor ' . $processedCount . ' baris data Excel pada urusan ' . $urusan->formatted_nama . ' (Tabel ' . ($activeTable['name'] ?? 'Tabel 1') . ')'
        );

        return [
            'success' => true,
            'message' => "Berhasil memproses {$processedCount} baris data indikator dari file Excel ({$createdCount} baris baru dibuat, {$updatedCount} baris diperbarui).",
            'processed' => $processedCount,
            'created' => $createdCount,
            'updated' => $updatedCount,
        ];
    }
}
