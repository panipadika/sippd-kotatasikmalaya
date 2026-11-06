<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Sektoral - {{ $urusan->formatted_nama }}</title>
    <style>
        @page {
            margin: 1.0cm 1.0cm 1.0cm 1.0cm;
            size: A4 landscape;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #0f172a;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header-box {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .gov-title {
            font-size: 8pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 0 0 2px 0;
        }
        .main-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0 0 3px 0;
            letter-spacing: 0.3px;
        }
        .sub-title {
            font-size: 8.5pt;
            color: #334155;
            margin: 0;
        }
        .meta-table {
            width: 100%;
            margin-top: 6px;
            font-size: 8pt;
        }
        .meta-table td {
            padding: 1px 0;
        }
        .meta-label {
            color: #64748b;
            width: 110px;
        }
        .meta-value {
            font-weight: bold;
            color: #0f172a;
        }

        /* TABLE STYLING */
        table.custom-data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            page-break-inside: auto;
        }
        table.custom-data-table th, table.custom-data-table td {
            border: 0.5pt solid #cbd5e1;
            padding: 4pt 4pt;
            vertical-align: middle;
        }
        table.custom-data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }
        table.custom-data-table th.sub-th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-size: 7pt;
            font-weight: 600;
        }
        table.custom-data-table th.sub-th-total {
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: bold;
        }
        table.custom-data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        table.custom-data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        table.custom-data-table tr.row-induk {
            background-color: #e2eefb !important;
            font-weight: bold;
            color: #0f172a;
        }
        table.custom-data-table tr.row-sub {
            background-color: #ffffff;
        }

        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .font-mono { font-family: monospace; }
        .text-primary { color: #0284c7; }
        .text-muted { color: #64748b; }
    </style>
</head>
<body>
    @php
        if (!isset($logoBase64) || empty($logoBase64)) {
            $logoPath = public_path('logo.png');
            $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
        }

        // Helper: leaf count of a column node
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

        // Helper: max tree depth of a column node
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

        // Calculate maximum header depth across all columns
        $maxHeaderDepth = 1;
        foreach ($activeColumns as $c) {
            $maxHeaderDepth = max($maxHeaderDepth, $getNodeDepth($c));
        }
        $hasSubColumns = $maxHeaderDepth > 1;

        // Helper: gather all leaf columns with ancestor chain
        $leafColumns = [];
        $collectLeaves = function($cols, $ancestors = []) use (&$collectLeaves, &$leafColumns) {
            foreach ($cols as $col) {
                $currAncestors = array_merge($ancestors, [$col]);
                if (!empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                    $collectLeaves($col['sub_columns'], $currAncestors);
                } else {
                    $leaf = $col;
                    $leaf['ancestors'] = $ancestors;
                    $leaf['parent'] = !empty($ancestors) ? end($ancestors) : null;
                    $leaf['root'] = !empty($ancestors) ? $ancestors[0] : $col;
                    $leaf['is_sub'] = !empty($ancestors);
                    $leafColumns[] = $leaf;
                }
            }
        };
        $collectLeaves($activeColumns);

        // Build grid rows for thead
        $headerRows = array_fill(0, $maxHeaderDepth, []);
        $buildHeaderRows = function($cols, $currentDepth, $maxDepth) use (&$buildHeaderRows, &$headerRows, $getLeafCount) {
            foreach ($cols as $c) {
                $hasSubs = !empty($c['sub_columns']) && is_array($c['sub_columns']);
                $leafCount = $getLeafCount($c);
                if ($hasSubs) {
                    $headerRows[$currentDepth][] = [
                        'col' => $c,
                        'colspan' => $leafCount,
                        'rowspan' => 1,
                        'has_subs' => true,
                        'is_leaf' => false,
                        'depth' => $currentDepth,
                    ];
                    $buildHeaderRows($c['sub_columns'], $currentDepth + 1, $maxDepth);
                } else {
                    $headerRows[$currentDepth][] = [
                        'col' => $c,
                        'colspan' => 1,
                        'rowspan' => $maxDepth - $currentDepth,
                        'has_subs' => false,
                        'is_leaf' => true,
                        'depth' => $currentDepth,
                    ];
                }
            }
        };
        $buildHeaderRows($activeColumns, 0, $maxHeaderDepth);

        // Recursive helper for leaf sub-column aggregation
        $getLeafValueForSubCol = function ($indicator, $subId, $colYearId, $yrNum) use (&$getLeafValueForSubCol) {
            if ($indicator->children && $indicator->children->count() > 0) {
                $sum = 0;
                $hasVal = false;
                foreach ($indicator->children as $c) {
                    $cv = $getLeafValueForSubCol($c, $subId, $colYearId, $yrNum);
                    if ($cv !== null) {
                        $sum += $cv;
                        $hasVal = true;
                    }
                }
                return $hasVal ? $sum : null;
            }
            foreach ($indicator->dataIndikators as $di) {
                $matchesYr = false;
                if ($colYearId && (int)$di->tahun_id === (int)$colYearId) {
                    $matchesYr = true;
                } elseif ($yrNum && $di->tahun && (int)$di->tahun->tahun === (int)$yrNum) {
                    $matchesYr = true;
                } elseif (!$colYearId && !$yrNum) {
                    $matchesYr = true;
                }
                if ($matchesYr) {
                    $v = $di->getCustomValue($subId);
                    if ($v !== null && $v !== '') {
                        $norm = str_replace(',', '.', trim((string) $v));
                        if (is_numeric($norm)) return (float) $norm;
                    }
                }
            }
            return null;
        };

        // Recursive helper for leaf column aggregation
        $getLeafValueForCol = function ($indicator, $colId, $colYearId, $yrNum, $isYrCol) use (&$getLeafValueForCol) {
            if ($indicator->children && $indicator->children->count() > 0) {
                $sum = 0;
                $hasVal = false;
                foreach ($indicator->children as $c) {
                    $cv = $getLeafValueForCol($c, $colId, $colYearId, $yrNum, $isYrCol);
                    if ($cv !== null) {
                        $sum += $cv;
                        $hasVal = true;
                    }
                }
                return $hasVal ? $sum : null;
            }
            $cDi = null;
            if ($isYrCol) {
                $cDi = $indicator->dataIndikators->first(function ($di) use ($yrNum, $colYearId) {
                    return ($yrNum && $di->tahun && (int)$di->tahun->tahun === $yrNum) || ($colYearId && $di->tahun_id === $colYearId);
                });
            } else {
                if ($colYearId) {
                    $cDi = $indicator->dataIndikators->firstWhere('tahun_id', $colYearId);
                }
                if (!$cDi) {
                    $cDi = $indicator->dataIndikators->first();
                }
            }
            if ($cDi) {
                $cv = $cDi->getCustomValue($colId);
                if ($cv !== null && $cv !== '') {
                    $norm = str_replace(',', '.', trim((string) $cv));
                    if (is_numeric($norm)) return (float) $norm;
                }
                if ($cDi->nilai !== null && $cDi->nilai !== '') {
                    $norm = str_replace(',', '.', trim((string) $cDi->nilai));
                    if (is_numeric($norm)) return (float) $norm;
                }
            }
            return null;
        };

        $firstDescriptorCol = collect($activeColumns)->first(function ($c) {
            $ln = strtolower(trim($c['name'] ?? ''));
            return !in_array($ln, ['no', 'no.', 'nomor']) && empty($c['sub_columns']) && ($c['type'] ?? 'text') !== 'number';
        });
        $firstDescriptorColId = $firstDescriptorCol['id'] ?? null;
    @endphp

    <div class="header-box">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
            <tr>
                @if(!empty($logoBase64))
                    <td style="width: 50px; vertical-align: middle; padding-right: 12px;">
                        <img src="{{ $logoBase64 }}" alt="Logo Kota Tasikmalaya" style="height: 48px; width: auto; display: block;">
                    </td>
                @endif
                <td style="vertical-align: middle;">
                    <p class="gov-title">BAPPERIDA KOTA TASIKMALAYA</p>
                    <h1 class="main-title">DATA STATISTIK SEKTORAL: {{ $urusan->nama_urusan }}</h1>
                </td>
            </tr>
        </table>

        <table class="meta-table">
            <tr>
                <td class="meta-label">Kelompok Urusan</td>
                <td class="meta-value">: {{ $urusan->kelompokUrusan->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Rentang Periode</td>
                <td class="meta-value">: Tahun {{ $tahunAwal }} - {{ $tahunAkhir }}</td>
            </tr>
        </table>
    </div>

    <table class="custom-data-table">
        <thead>
            @foreach($headerRows as $depth => $cells)
                <tr>
                    @foreach($cells as $item)
                        @php
                            $col = $item['col'];
                            $cName = $col['name'] ?? '';
                            $hasSubs = $item['has_subs'];
                            $colspan = $item['colspan'];
                            $rowspan = $item['rowspan'];
                            $isTot = in_array(strtolower(trim($cName)), ['jumlah', 'total', 'jml', 'jmlh']) || ($col['is_total'] ?? false);
                        @endphp
                        @if($hasSubs)
                            <th colspan="{{ $colspan }}" rowspan="{{ $rowspan }}" style="background-color: {{ $depth === 0 ? '#0284c7' : ($depth === 1 ? '#0369a1' : '#334155') }}; color: #ffffff;">
                                {{ $cName }}
                            </th>
                        @else
                            <th colspan="{{ $colspan }}" rowspan="{{ $rowspan }}" class="{{ $depth > 0 ? 'sub-th' : '' }} {{ $isTot ? 'sub-th-total' : '' }}">
                                {{ $cName }}
                            </th>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </thead>
        @php
            $firstDescriptorCol = collect($leafColumns)->first(function ($c) {
                $ln = strtolower(trim($c['name'] ?? ''));
                return !in_array($ln, ['no', 'no.', 'nomor']) && empty($c['sub_columns']) && ($c['type'] ?? 'text') !== 'number';
            });
            if (!$firstDescriptorCol) {
                $firstDescriptorCol = collect($leafColumns)->first(function ($c) {
                    $ln = strtolower(trim($c['name'] ?? ''));
                    return !in_array($ln, ['no', 'no.', 'nomor']) && empty($c['sub_columns']);
                });
            }
            $firstDescriptorColId = $firstDescriptorCol['id'] ?? null;
        @endphp
        <tbody>
            @forelse($flattenedIndicators as $ind)
                @php
                    $level = $ind->tree_level ?? 0;
                    $hasIndukChildren = $ind->children && $ind->children->count() > 0;
                    $valObj = $ind->dataIndikators->first();
                @endphp
                <tr class="{{ $hasIndukChildren ? 'row-induk' : ($level > 0 ? 'row-sub' : '') }}">
                    @foreach($leafColumns as $col)
                        @if(!empty($col['is_sub']))
                            @php
                                $subId = $col['id'];
                                $subName = $col['name'] ?? '';
                                $isTotal = in_array(strtolower(trim($subName)), ['jumlah', 'total', 'jml', 'jmlh']) || ($col['is_total'] ?? false);
                                $val = null;

                                // Year detection from leaf or ancestors
                                $yrNum = null;
                                $colYearId = null;
                                if (preg_match('/^(19|20)\d{2}$/', trim($subName), $mYr)) {
                                    $yrNum = (int) $mYr[0];
                                } else {
                                    foreach (array_reverse($col['ancestors'] ?? []) as $anc) {
                                        if (preg_match('/^(19|20)\d{2}$/', trim($anc['name'] ?? ''), $mYr)) {
                                            $yrNum = (int) $mYr[0];
                                            break;
                                        }
                                    }
                                }
                                if ($yrNum) {
                                    $thColModel = \App\Models\Tahun::where('tahun', $yrNum)->first();
                                    if ($thColModel) {
                                        $colYearId = $thColModel->id;
                                    }
                                }

                                if ($hasIndukChildren) {
                                    $cSubSum = $getLeafValueForSubCol($ind, $subId, $colYearId, $yrNum);
                                    $val = ($cSubSum !== null) ? ((floor($cSubSum) == $cSubSum) ? (string)(int)$cSubSum : (string)round($cSubSum, 2)) : '0';
                                } else {
                                    $di = $colYearId ? $ind->dataIndikators->firstWhere('tahun_id', $colYearId) : $ind->dataIndikators->first();
                                    if ($di) {
                                        $val = $di->getCustomValue($subId);
                                    }
                                }
                            @endphp
                            <td class="text-center {{ $hasIndukChildren || $isTotal ? 'fw-bold' : '' }} {{ $isTotal ? 'text-primary' : '' }}" style="{{ $isTotal ? 'background-color: #f0f9ff;' : '' }}">
                                {{ ($val !== null && $val !== '') ? $val : '—' }}
                            </td>
                        @else
                            @php
                                $colId = $col['id'];
                                $lowerName = strtolower(trim($col['name']));
                                $isNo = in_array($lowerName, ['no', 'no.', 'nomor']);
                                $isNama = ($colId === 'col_nama' || $colId === 'nama_indikator' || in_array($lowerName, ['nama', 'indikator', 'nama indikator', 'uraian', 'nama/uraian', 'tahun', 'thn', 'periode']) || ($firstDescriptorColId && $colId === $firstDescriptorColId));
                                $isSatuan = ($colId === 'col_satuan' || $lowerName === 'satuan');
                                
                                $val = null;
                                if ($isNama) {
                                    $val = $ind->nama_indikator;
                                } elseif ($isSatuan) {
                                    $val = $ind->satuan;
                                } elseif ($isNo) {
                                    if ($level > 0 && $ind->parent) {
                                        $val = (string) ($ind->parent->urutan . '.' . ($ind->urutan ?: $loop->parent->iteration));
                                    } else {
                                        $val = (string) ($ind->urutan ?: $loop->parent->iteration);
                                    }
                                } else {
                                    if ($hasIndukChildren) {
                                        $cSum = $getLeafValueForCol($ind, $colId, null, null, false);
                                        if ($cSum !== null) {
                                            $val = floor($cSum) == $cSum ? number_format($cSum, 0, ',', '.') : rtrim(rtrim(number_format($cSum, 4, ',', '.'), '0'), ',');
                                        }
                                    } else {
                                        $di = $ind->dataIndikators->first();
                                        if ($di) {
                                            $val = $di->getCustomValue($colId) ?? $di->nilai;
                                        }
                                    }
                                }
                            @endphp
                            <td class="{{ $isNo ? 'text-center' : ($isSatuan ? 'text-center' : 'text-start') }} {{ $hasIndukChildren ? 'fw-bold' : '' }}">
                                @if($isNama)
                                    @if($hasIndukChildren)
                                        <strong>{{ $val }}</strong>
                                    @elseif($level > 0)
                                        <span style="padding-left: {{ $level * 10 }}px;">
                                            {{ $val }}
                                        </span>
                                    @else
                                        {{ $val }}
                                    @endif
                                @else
                                    {{ ($val !== null && $val !== '') ? $val : '—' }}
                                @endif
                            </td>
                        @endif
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($leafColumns) }}" class="text-center" style="padding: 20px; color: #64748b;">
                        Belum ada data indikator yang terdaftar pada tabel ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @php
            $showFooterTotal = request()->has('show_footer_total') ? request()->boolean('show_footer_total') : (bool)($activeTable['show_footer_total'] ?? true);
            $showFooterJumlah = request()->has('show_footer_jumlah') ? request()->boolean('show_footer_jumlah') : (bool)($activeTable['show_footer_jumlah'] ?? true);
            $showFooterSubtotal = request()->has('show_footer_subtotal') ? request()->boolean('show_footer_subtotal') : (bool)($activeTable['show_footer_subtotal'] ?? true);
            $footerLabel = $activeTable['footer_label'] ?? 'Jumlah';
            $footerOverrides = $activeTable['footer_overrides'] ?? [];
        @endphp
        @if($showFooterTotal && ($showFooterJumlah || $showFooterSubtotal) && count($flattenedIndicators) > 0)
            @php
                $footerSubtotalLabel = $activeTable['footer_subtotal_label'] ?? 'TOTAL';
                $leafSums = [];

                $footerParentGroups = [];
                $currentGroup = null;
                $hasGroupWithSubs = false;

                foreach ($leafColumns as $leaf) {
                    $parentId = $leaf['parent']['id'] ?? null;
                    $parentName = $leaf['parent']['name'] ?? null;

                    if ($parentId !== null) {
                        if ($currentGroup && $currentGroup['type'] === 'parent' && $currentGroup['id'] === $parentId) {
                            $currentGroup['colspan']++;
                            $currentGroup['leaf_ids'][] = $leaf['id'];
                            $currentGroup['leaf_names'][] = $leaf['name'];
                        } else {
                            if ($currentGroup) {
                                $footerParentGroups[] = $currentGroup;
                            }
                            $currentGroup = [
                                'type' => 'parent',
                                'id' => $parentId,
                                'name' => $parentName,
                                'colspan' => 1,
                                'leaf_ids' => [$leaf['id']],
                                'leaf_names' => [$leaf['name']],
                                'parent_obj' => $leaf['parent'],
                            ];
                        }
                    } else {
                        if ($currentGroup) {
                            $footerParentGroups[] = $currentGroup;
                            $currentGroup = null;
                        }
                        $footerParentGroups[] = [
                            'type' => 'leaf',
                            'id' => $leaf['id'],
                            'name' => $leaf['name'],
                            'colspan' => 1,
                            'leaf_ids' => [$leaf['id']],
                            'leaf_names' => [$leaf['name']],
                            'parent_obj' => null,
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

                $actualFooterLabel = $footerLabel;
                if (!$hasGroupWithSubs && $showFooterSubtotal && !$showFooterJumlah) {
                    $actualFooterLabel = $footerSubtotalLabel;
                    $showFooterJumlah = true;
                    $showFooterSubtotal = false;
                }
            @endphp
            <tfoot>
                @if($showFooterJumlah)
                <!-- Baris 1: Total Per Leaf Column -->
                <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #334155;">
                    @foreach($leafColumns as $col)
                        @if(!empty($col['is_sub']))
                            @php
                                $subId = $col['id'];
                                $subName = $col['name'] ?? '';
                                $yrNum = null;
                                $colYearId = null;
                                if (preg_match('/^(19|20)\d{2}$/', trim($subName), $mYr)) {
                                    $yrNum = (int) $mYr[0];
                                } else {
                                    foreach (array_reverse($col['ancestors'] ?? []) as $anc) {
                                        if (preg_match('/^(19|20)\d{2}$/', trim($anc['name'] ?? ''), $mYr)) {
                                            $yrNum = (int) $mYr[0];
                                            break;
                                        }
                                    }
                                }
                                if ($yrNum) {
                                    $thColModel = \App\Models\Tahun::where('tahun', $yrNum)->first();
                                    if ($thColModel) {
                                        $colYearId = $thColModel->id;
                                    }
                                }

                                $subSum = 0;
                                $hasSubSum = false;
                                foreach ($flattenedIndicators as $indRow) {
                                    if ($indRow->parent_id !== null) continue;
                                    if ($indRow->children && $indRow->children->count() > 0) {
                                        $cVal = $getLeafValueForSubCol($indRow, $subId, $colYearId, $yrNum);
                                        if ($cVal !== null) {
                                            $subSum += $cVal;
                                            $hasSubSum = true;
                                        }
                                    } else {
                                        $di = $colYearId ? $indRow->dataIndikators->firstWhere('tahun_id', $colYearId) : $indRow->dataIndikators->first();
                                        if ($di) {
                                            $v = $di->getCustomValue($subId);
                                            if ($v !== null && $v !== '' && is_numeric($v)) {
                                                $subSum += (float) $v;
                                                $hasSubSum = true;
                                            }
                                        }
                                    }
                                }
                                $hasSubOv = isset($footerOverrides[$subId]) && $footerOverrides[$subId] !== '';
                                $subTotalVal = $hasSubOv
                                    ? $footerOverrides[$subId]
                                    : ($hasSubSum ? (floor($subSum) == $subSum ? (string)(int)$subSum : (string)round($subSum, 2)) : '0');
                                $leafSums[$subId] = $hasSubOv && is_numeric($footerOverrides[$subId]) ? (float)$footerOverrides[$subId] : ($hasSubSum ? $subSum : 0);
                            @endphp
                            <td class="text-center" style="font-weight: bold; background-color: #f1f5f9;">
                                {{ $subTotalVal }}
                            </td>
                        @else
                            @php
                                $colId = $col['id'];
                                $lowerName = strtolower(trim($col['name']));
                                $isNo = in_array($lowerName, ['no', 'no.', 'nomor']);
                                $isDesc = ($colId === 'col_nama' || $colId === 'nama_indikator' || in_array($lowerName, ['nama', 'indikator', 'nama indikator', 'uraian', 'nama/uraian']) || ($firstDescriptorColId && $colId === $firstDescriptorColId));
                                $isNumeric = ($colId === 'col_nilai' || $lowerName === 'nilai' || ($col['type'] ?? 'text') === 'number');
                            @endphp
                            @if($isNo)
                                <td class="text-center" style="background-color: #f1f5f9;"></td>
                            @elseif($isDesc)
                                <td class="text-start" style="font-weight: bold; padding-left: 8px; background-color: #f1f5f9;">
                                    <strong>{{ $actualFooterLabel }}</strong>
                                </td>
                            @elseif($isNumeric)
                                @php
                                    $colSum = 0;
                                    $hasColSum = false;
                                    foreach ($flattenedIndicators as $indRow) {
                                        if ($indRow->parent_id !== null) continue;
                                        if ($indRow->children && $indRow->children->count() > 0) {
                                            $cVal = $getLeafValueForCol($indRow, $colId, null, null, false);
                                            if ($cVal !== null) {
                                                $colSum += $cVal;
                                                $hasColSum = true;
                                            }
                                        } else {
                                            $di = $indRow->dataIndikators->first();
                                            if ($di) {
                                                $v = $di->getCustomValue($colId) ?? $di->nilai;
                                                if ($v !== null && $v !== '' && is_numeric($v)) {
                                                    $colSum += (float) $v;
                                                    $hasColSum = true;
                                                }
                                            }
                                        }
                                    }
                                    $hasColOv = isset($footerOverrides[$colId]) && $footerOverrides[$colId] !== '';
                                    $colTotalVal = $hasColOv
                                        ? $footerOverrides[$colId]
                                        : ($hasColSum ? (floor($colSum) == $colSum ? (string)(int)$colSum : (string)round($colSum, 2)) : '0');
                                    $leafSums[$colId] = $hasColOv && is_numeric($footerOverrides[$colId]) ? (float)$footerOverrides[$colId] : ($hasColSum ? $colSum : 0);
                                @endphp
                                <td class="text-center" style="font-weight: bold; background-color: #f1f5f9;">
                                    {{ $colTotalVal }}
                                </td>
                            @else
                                <td class="text-center" style="background-color: #f1f5f9; color: #94a3b8;">—</td>
                            @endif
                        @endif
                    @endforeach
                </tr>
                @endif

                @if($hasGroupWithSubs && $showFooterSubtotal)
                    <!-- Baris 2: Total Gabungan Sub-Kolom (Grup Parent) -->
                    <tr style="background-color: #e2e8f0; font-weight: bold; border-top: 1px solid #cbd5e1; border-bottom: 2px solid #334155;">
                        @foreach($footerParentGroups as $g)
                            @if($g['type'] === 'leaf')
                                @php
                                    $colId = $g['id'];
                                    $lowerName = strtolower(trim($g['name'] ?? ''));
                                    $isNo = in_array($lowerName, ['no', 'no.', 'nomor']);
                                    $isDesc = ($colId === 'col_nama' || $colId === 'nama_indikator' || in_array($lowerName, ['nama', 'indikator', 'nama indikator', 'uraian', 'nama/uraian']) || ($firstDescriptorColId && $colId === $firstDescriptorColId));
                                @endphp
                                @if($isNo)
                                    <td class="text-center" style="background-color: #e2e8f0;"></td>
                                @elseif($isDesc)
                                    <td class="text-start" style="font-weight: bold; padding-left: 8px; background-color: #e2e8f0;">
                                        <strong>{{ $footerSubtotalLabel }}</strong>
                                    </td>
                                @else
                                    <td class="text-center" style="background-color: #e2e8f0; color: #94a3b8;">—</td>
                                @endif
                            @else
                                @php
                                    $pId = $g['id'];
                                    $pColspan = $g['colspan'];
                                    $pKey = 'subtotal_' . $pId;
                                    $hasPOverride = isset($footerOverrides[$pKey]) && $footerOverrides[$pKey] !== '';
                                    $pOverrideVal = $hasPOverride ? $footerOverrides[$pKey] : null;

                                    $groupSum = 0;
                                    $hasGroupVal = false;
                                    foreach ($g['leaf_ids'] as $lId) {
                                        if (isset($leafSums[$lId])) {
                                            $groupSum += $leafSums[$lId];
                                            $hasGroupVal = true;
                                        }
                                    }
                                    $groupTotalVal = $hasPOverride ? $pOverrideVal : ($hasGroupVal ? (floor($groupSum) == $groupSum ? (string)(int)$groupSum : (string)round($groupSum, 2)) : '0');
                                @endphp
                                <td colspan="{{ $pColspan }}" class="text-center" style="font-weight: bold; background-color: #e0f2fe; color: #0369a1; border-left: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1;">
                                    {{ $groupTotalVal }}
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @endif
            </tfoot>
        @endif
    </table>
</body>
</html>
