@php
    /** @var \App\Services\Reports\ReportFormatter $format */
    $settings = $input['settings'];
    $project = $input['project'];
    $market = $input['market'];
    $swot = $input['swot'];
    $indicators = $result['indicators'];
    $capital = $result['capital'];
    $studyYears = $result['settings']['study_years'];
    $text = fn (?string $value): string => $value !== null && trim($value) !== '' ? nl2br(e($value)) : '<span class="muted">—</span>';
    $years = fn (int|float $count): string => $count >= 3 && $count <= 10 ? 'سنوات' : 'سنة';
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $study->title }}</title>
    <style>
        @foreach ($fonts as $font)
        @font-face {
            font-family: 'Report';
            font-weight: {{ $font['weight'] }};
            src: url(data:font/ttf;base64,{{ $font['data'] }}) format('truetype');
        }
        @endforeach

        :root { --brand: #0f6f7f; --brand-soft: #e6f2f4; --gold: #d4a017; --ink: #0f172a; --muted: #64748b; --line: #e2e8f0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body { font-family: 'Report', sans-serif; color: var(--ink); font-size: 10.5pt; line-height: 1.7; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        h1, h2, h3 { margin: 0; line-height: 1.35; }
        h2 { font-size: 15pt; font-weight: 700; color: var(--brand); padding-bottom: 6px; border-bottom: 2px solid var(--brand-soft); margin-bottom: 12px; }
        h3 { font-size: 11.5pt; font-weight: 600; margin: 14px 0 6px; }
        p { margin: 0 0 6px; }
        .muted { color: var(--muted); }
        /* Numbers read left to right (so "-1,200" keeps its minus sign) but blocks stay aligned to the RTL page. */
        .num { direction: ltr; unicode-bidi: isolate; font-variant-numeric: tabular-nums; white-space: nowrap; }
        td.num, dd.num, div.num, .sub.num { unicode-bidi: plaintext; text-align: right; }
        .negative { color: #b91c1c; }
        .page { break-after: page; }
        section { margin-bottom: 22px; }
        .keep { break-inside: avoid; }

        /* Cover */
        .cover { height: 255mm; display: flex; flex-direction: column; justify-content: space-between; padding: 8mm 4mm; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 14pt; color: var(--brand); }
        .brand svg { width: 34px; height: 34px; fill: var(--brand); }
        .cover-title { border-inline-start: 6px solid var(--gold); padding-inline-start: 18px; }
        .cover-title .kicker { color: var(--brand); font-weight: 600; font-size: 12pt; }
        .cover-title h1 { font-size: 26pt; font-weight: 700; margin: 6px 0; }
        .cover-title .project { font-size: 14pt; color: var(--muted); }
        .cover-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 24px; background: var(--brand-soft); border-radius: 12px; padding: 16px 20px; }
        .cover-meta dt { color: var(--muted); font-size: 9pt; }
        .cover-meta dd { margin: 0; font-weight: 600; }

        /* Indicators */
        .kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
        .kpi { border: 1px solid var(--line); border-radius: 10px; padding: 10px 12px; }
        .kpi .label { color: var(--muted); font-size: 9pt; }
        .kpi .value { font-size: 14pt; font-weight: 700; margin-top: 2px; }
        .financing { display: flex; align-items: center; gap: 24px; }
        .legend { list-style: none; margin: 0; padding: 0; }
        .legend li { display: flex; align-items: center; gap: 8px; margin: 4px 0; }
        .swatch { width: 10px; height: 10px; border-radius: 999px; display: inline-block; }
        .chart { width: 100%; height: auto; }
        .chart-legend { display: flex; gap: 18px; font-size: 9pt; color: var(--muted); margin-top: 4px; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 9.5pt; }
        thead { display: table-header-group; }
        tr { break-inside: avoid; }
        th { background: var(--brand); color: #fff; font-weight: 600; text-align: start; padding: 6px 8px; }
        td { border-bottom: 1px solid var(--line); padding: 5px 8px; vertical-align: top; }
        td.amount, th.amount, td.amount .sub, td.amount .sub.num { text-align: left; }
        tr.strong td { font-weight: 700; background: #f8fafc; }
        tfoot td { font-weight: 700; border-top: 2px solid var(--brand); }
        .sub { display: block; color: var(--muted); font-size: 8.5pt; }

        /* Text blocks */
        .fields { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 24px; margin-bottom: 10px; }
        .fields dt { color: var(--muted); font-size: 9pt; }
        .fields dd { margin: 0 0 4px; font-weight: 600; }
        .block { margin-bottom: 10px; }
        .block .label { font-weight: 600; color: var(--brand); }
        .swot { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .swot > div { border-radius: 10px; padding: 10px 12px; break-inside: avoid; }
        .swot .s { background: #ecfdf5; } .swot .w { background: #fef2f2; } .swot .o { background: #eff6ff; } .swot .t { background: #fffbeb; }
        .disclaimer { font-size: 8.5pt; color: var(--muted); border-top: 1px solid var(--line); padding-top: 8px; margin-top: 16px; }
    </style>
</head>
<body>

{{-- Cover --}}
<div class="cover page">
    <div class="brand">
        <svg viewBox="0 0 32 32" aria-hidden="true"><path fill-rule="evenodd" d="M6 3h20a3 3 0 0 1 3 3v20a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3Zm2.5 18.5a1.5 1.5 0 0 0 2.12 2.12l5.38-5.38 3.38 3.38a1.5 1.5 0 0 0 2.12 0l5-5a1.5 1.5 0 1 0-2.12-2.12L20.44 18.44l-3.38-3.38a1.5 1.5 0 0 0-2.12 0L8.5 21.5ZM9 8.5a1.5 1.5 0 0 0 0 3h6a1.5 1.5 0 0 0 0-3H9Z"/></svg>
        <span>{{ $company }}</span>
    </div>
    <div class="cover-title">
        <div class="kicker">دراسة جدوى اقتصادية</div>
        <h1>{{ $study->title }}</h1>
        <div class="project">{{ $input['project_name'] }}</div>
    </div>
    <dl class="cover-meta">
        <div><dt>رقم الدراسة</dt><dd class="num">{{ $study->reference }}</dd></div>
        <div><dt>إصدار التقرير</dt><dd><span class="num">{{ $report->version }}</span></dd></div>
        <div><dt>مقدّم الدراسة</dt><dd>{{ $customerName }}</dd></div>
        <div><dt>تاريخ الإصدار</dt><dd class="num">{{ $report->created_at?->format('Y-m-d') }}</dd></div>
        <div><dt>مدة الدراسة</dt><dd><span class="num">{{ $studyYears }}</span> {{ $years($studyYears) }}</dd></div>
        <div><dt>العملة</dt><dd>{{ $currency['name'] }} (<span class="num">{{ $currency['code'] }}</span>)</dd></div>
    </dl>
</div>

{{-- Economic feasibility --}}
<section class="keep">
    <h2>الجدوى الاقتصادية</h2>
    <p class="muted">جدوى لمدة <span class="num">{{ $studyYears }}</span> {{ $years($studyYears) }}. جميع المبالغ بـ{{ $currency['name'] }}.</p>
    <div class="kpis">
        <div class="kpi">
            <div class="label">معدل ربح سنوي</div>
            <div class="value num {{ $indicators['average_profit'] < 0 ? 'negative' : '' }}">{{ $format->money($indicators['average_profit']) }}</div>
        </div>
        <div class="kpi">
            <div class="label">فترة استرداد رأس المال</div>
            <div class="value">{{ $indicators['capital_recovery']['label'] }}</div>
        </div>
        <div class="kpi">
            <div class="label">معدل الحصة السوقية</div>
            <div class="value num">{{ $indicators['market_share'] === null ? 'غير متوفر' : $format->percent($indicators['market_share'], 2) }}</div>
        </div>
    </div>

    <h3>تمويل المشروع</h3>
    <div class="financing">
        {!! $financingChart !!}
        <ul class="legend">
            <li><strong>رأس مال المشروع:</strong> <span class="num">{{ $format->money($capital['total']) }}</span></li>
            @foreach ($financingLegend as $item)
                <li><span class="swatch" style="background: {{ $item['color'] }}"></span> {{ $item['label'] }}: <span class="num">{{ $format->money($item['value']) }}</span></li>
            @endforeach
        </ul>
    </div>
</section>

{{-- Project overview --}}
<section>
    <h2>نبذة عن المشروع</h2>
    <dl class="fields">
        <div><dt>اسم المشروع</dt><dd>{{ $input['project_name'] }}</dd></div>
        <div><dt>تاريخ بدء المشروع</dt><dd class="num">{{ $project['start_date'] ?? '—' }}</dd></div>
        <div><dt>القطاع</dt><dd>{{ $input['labels']['sector'] ?? '—' }}</dd></div>
        <div><dt>نوع المشروع</dt><dd>{{ $input['labels']['project_type'] ?? '—' }}</dd></div>
    </dl>
    <div class="block"><div class="label">وصف المشروع</div><p>{!! $text($project['description']) !!}</p></div>
    <div class="block"><div class="label">الجدوى التنظيمية</div><p>{!! $text($project['organizational_feasibility']) !!}</p></div>

    <h3>ملاك المشروع</h3>
    <table>
        <thead><tr><th>الاسم</th><th class="amount">الحصة</th><th>الخبرة</th></tr></thead>
        <tbody>
            @foreach ($input['owners'] as $owner)
                <tr>
                    <td>{{ $owner['name'] }}</td>
                    <td class="amount num">{{ $format->percent($owner['share'], 0) }}</td>
                    <td>{!! $text($owner['experience']) !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>

{{-- Market --}}
<section>
    <h2>دراسة السوق</h2>
    <dl class="fields">
        <div><dt>السوق المستهدف</dt><dd>{{ $market['name'] ?? '—' }}</dd></div>
        <div><dt>حجم السوق</dt><dd class="num">{{ $market['size'] === null ? '—' : $format->money($market['size']) }}</dd></div>
    </dl>
    @foreach ([
        'description' => 'نبذة عن السوق',
        'products' => 'المنتجات والخدمات',
        'target_customers' => 'الفئة المستهدفة',
        'suppliers' => 'الموردون',
        'marketing_plan' => 'خطة التسويق',
        'competition_analysis' => 'تحليل المنافسة',
    ] as $key => $label)
        <div class="block"><div class="label">{{ $label }}</div><p>{!! $text($market[$key]) !!}</p></div>
    @endforeach

    @if (count($input['competitors']) > 0)
        <h3>المنافسون</h3>
        <table>
            <thead><tr><th>اسم المنافس</th><th class="amount">الحصة السوقية</th><th>المزايا التنافسية</th></tr></thead>
            <tbody>
                @foreach ($input['competitors'] as $competitor)
                    <tr>
                        <td>{{ $competitor['name'] }}</td>
                        <td class="amount num">{{ $format->percent($competitor['share'], 0) }}</td>
                        <td>{!! $text($competitor['characteristics']) !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>

{{-- Setup costs --}}
<section>
    <h2>تكاليف التأسيس والانشاء</h2>
    <table>
        <thead><tr><th>نوع التكلفة <span class="sub">(العمر الافتراضي)</span></th><th class="amount">القيمة <span class="sub">(% الاستهلاك السنوي)</span></th></tr></thead>
        <tbody>
            @foreach ($capital['setup_costs'] as $cost)
                <tr>
                    <td>{{ $cost['name'] }} <span class="sub">(<span class="num">{{ $format->number($cost['life_span']) }}</span> {{ $years($cost['life_span']) }})</span></td>
                    <td class="amount"><span class="num">{{ $format->money($cost['value']) }}</span> <span class="sub num">({{ $format->percent($cost['depreciation_rate'], 0) }})</span></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot><tr><td>إجمالي</td><td class="amount num">{{ $format->money($capital['total']) }}</td></tr></tfoot>
    </table>
</section>

{{-- Loans --}}
@if (count($result['financing']['loans']) > 0)
    <section>
        <h2>المديونيات</h2>
        <table>
            <thead><tr><th>الجهة الممولة <span class="sub">(الفائدة السنوية - سنوات السداد)</span></th><th class="amount">القسط الشهري <span class="sub">(المديونية)</span></th><th class="amount">إجمالي السداد</th></tr></thead>
            <tbody>
                @foreach ($result['financing']['loans'] as $loan)
                    <tr>
                        <td>{{ $loan['lender'] }} <span class="sub">(<span class="num">{{ $format->percent($loan['interest_rate'], 2) }}</span> - <span class="num">{{ $format->number($loan['years']) }}</span> {{ $years($loan['years']) }})</span></td>
                        <td class="amount"><span class="num">{{ $format->money($loan['monthly_instalment']) }}</span> <span class="sub num">({{ $format->money($loan['amount']) }})</span></td>
                        <td class="amount num">{{ $format->money($loan['total_payable']) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot><tr><td>إجمالي</td><td class="amount num">{{ $format->money($result['financing']['total_borrowed']) }}</td><td class="amount num">{{ $format->money($result['financing']['total_payable']) }}</td></tr></tfoot>
        </table>
    </section>
@endif

{{-- Employees --}}
@if (count($result['employees']) > 0)
    <section class="keep">
        <h2>الموظفين</h2>
        <table>
            <thead><tr><th>العدد × مسمى وظيفي</th><th class="amount">راتب شهري</th><th class="amount">إجمالي شهري</th></tr></thead>
            <tbody>
                @foreach ($result['employees'] as $employee)
                    <tr>
                        <td><span class="num">{{ $format->number($employee['count']) }}</span> × {{ $employee['title'] }}</td>
                        <td class="amount num">{{ $format->money($employee['monthly_salary']) }}</td>
                        <td class="amount num">{{ $format->money($employee['count'] * $employee['monthly_salary']) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot><tr><td colspan="2">إجمالي الرواتب الشهرية</td><td class="amount num">{{ $format->money($result['summary']['monthly_salaries']) }}</td></tr></tfoot>
        </table>
    </section>
@endif

{{-- Fixed costs --}}
@if (count($result['fixed_costs']) > 0)
    <section class="keep">
        <h2>التكاليف الشهرية الثابتة</h2>
        <table>
            <thead><tr><th>نوع التكلفة <span class="sub">(مدة التكلفة)</span></th><th class="amount">إجمالي <span class="sub">(الضريبة)</span></th></tr></thead>
            <tbody>
                @foreach ($result['fixed_costs'] as $cost)
                    <tr>
                        <td>{{ $cost['name'] }} <span class="sub">({{ $cost['years'] === null ? 'طوال مدة الدراسة' : $format->number($cost['years']).' '.$years($cost['years']) }})</span></td>
                        <td class="amount"><span class="num">{{ $format->money($cost['monthly_amount']) }}</span> <span class="sub num">({{ $format->money($cost['monthly_vat']) }})</span></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot><tr><td>إجمالي</td><td class="amount num">{{ $format->money($result['summary']['monthly_fixed_costs']) }}</td></tr></tfoot>
        </table>
    </section>
@endif

{{-- Products --}}
<section>
    <h2>المنتجات وإيرادها الشهري</h2>
    <table>
        <thead><tr><th>المنتج أو الخدمة <span class="sub">(قدرة بيع) (قدرة إنتاج)</span></th><th class="amount">سعر الوحدة <span class="sub">(تكلفة الوحدة)</span></th><th class="amount">زيادة بيع سنوية</th></tr></thead>
        <tbody>
            @foreach ($input['products'] as $product)
                <tr>
                    <td>{{ $product['name'] }} <span class="sub">(<span class="num">{{ $format->number($product['monthly_quantity'], 2) }}</span>) (<span class="num">{{ $product['monthly_capacity'] === null ? '—' : $format->number($product['monthly_capacity'], 2) }}</span>)</span></td>
                    <td class="amount"><span class="num">{{ $format->money($product['unit_price']) }}</span> <span class="sub num">({{ $format->money($product['unit_cost']) }})</span></td>
                    <td class="amount num">{{ $format->percent($product['annual_growth'], 1) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot><tr><td>الإيراد الشهري <span class="sub">(دون الضريبة)</span></td><td class="amount num" colspan="2">{{ $format->money($result['summary']['monthly_revenue_without_vat']) }}</td></tr></tfoot>
    </table>
</section>

{{-- Annual profitability --}}
<section>
    <h2>الربحية السنوية</h2>
    <div class="keep">
        {!! $profitabilityChart !!}
        <div class="chart-legend">
            @foreach ($chartLegend as $item)
                <span><span class="swatch" style="background: {{ $item['color'] }}"></span> {{ $item['label'] }}</span>
            @endforeach
        </div>
    </div>
    <table style="margin-top: 10px">
        <thead><tr><th>السنة المالية</th><th class="amount">الإيرادات السنوية</th><th class="amount">التكاليف السنوية <span class="sub">(الشهرية)</span></th><th class="amount">الأرباح السنوية <span class="sub">(% من الإيرادات)</span></th></tr></thead>
        <tbody>
            @foreach ($result['yearly_projections'] as $year)
                <tr>
                    <td class="num">{{ $year['year'] }}</td>
                    <td class="amount num">{{ $format->money($year['total_revenue']) }}</td>
                    <td class="amount"><span class="num">{{ $format->money($year['total_expenses']) }}</span> <span class="sub num">({{ $format->money($year['monthly_expenses']) }})</span></td>
                    <td class="amount"><span class="num {{ $year['profit'] < 0 ? 'negative' : '' }}">{{ $format->money($year['profit']) }}</span> <span class="sub num">({{ $format->percent($year['profit_ratio']) }})</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>

{{-- Cost-based pricing --}}
@if (count($pricing) > 0)
    <section class="keep">
        <h2>آلية تسعير بالتكلفة</h2>
        <p class="muted">للسنة المالية الأولى.</p>
        <table>
            <thead><tr><th>المنتج</th><th class="amount">أدنى سعر للربحية</th><th class="amount">الهامش الربحي</th></tr></thead>
            <tbody>
                @foreach ($pricing as $product)
                    <tr>
                        <td>{{ $product['name'] }}</td>
                        <td class="amount num">{{ $format->money($product['break_even_price']) }}</td>
                        @php($margin = match ($product['margin_display']) { 'infinite' => '∞', 'not_available' => 'غير متوفر', default => $format->percent($product['profit_margin']) })
                        <td class="amount num">{{ $margin }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endif

{{-- Financial statements --}}
<section>
    <h2>القوائم المالية</h2>
    @foreach ($statements as $statement)
        <div class="keep">
            <h3>{{ $statement['title'] }}@if (! $loop->first && $statements[$loop->index - 1]['title'] === $statement['title']) <span class="muted">(تابع)</span>@endif</h3>
            <table>
                <thead>
                    <tr>
                        <th>البند</th>
                        @foreach ($statement['years'] as $year)
                            <th class="amount">السنة <span class="num">{{ $year }}</span></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($statement['rows'] as $row)
                        <tr @class(['strong' => $row['strong']])>
                            <td>{{ $row['label'] }}</td>
                            @foreach ($row['values'] as $value)
                                <td class="amount num {{ $value < 0 ? 'negative' : '' }}">{{ $format->money($value) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</section>

{{-- SWOT --}}
<section class="keep">
    <h2>تحليل SWOT</h2>
    <div class="swot">
        <div class="s"><div class="block"><div class="label">نقاط القوة</div><p>{!! $text($swot['strengths']) !!}</p></div></div>
        <div class="w"><div class="block"><div class="label">نقاط الضعف</div><p>{!! $text($swot['weaknesses']) !!}</p></div></div>
        <div class="o"><div class="block"><div class="label">الفرص</div><p>{!! $text($swot['opportunities']) !!}</p></div></div>
        <div class="t"><div class="block"><div class="label">التهديدات</div><p>{!! $text($swot['threats']) !!}</p></div></div>
    </div>
</section>

{{-- Conclusion --}}
<section class="keep">
    <h2>الخلاصة</h2>
    <p>
        يبلغ رأس مال المشروع <span class="num">{{ $format->money($capital['total']) }}</span> {{ $currency['name'] }}،
        ويُتوقع أن تبلغ المبيعات خلال <span class="num">{{ $studyYears }}</span> {{ $years($studyYears) }} نحو
        <span class="num">{{ $format->money($indicators['total_sales']) }}</span>،
        بمعدل ربح سنوي <span class="num {{ $indicators['average_profit'] < 0 ? 'negative' : '' }}">{{ $format->money($indicators['average_profit']) }}</span>،
        وفترة استرداد لرأس المال: {{ $indicators['capital_recovery']['label'] }}.
    </p>
    <p class="disclaimer">
        أُعدّت هذه الدراسة آليًا من البيانات التي أدخلها مقدّم الدراسة، وبالمعادلات المعتمدة في المنصة (إصدار الحساب
        <span class="num">{{ $report->calculation_version }}</span>). الأرقام تقديرية ولا تمثل استشارة مالية أو تقييمًا مهنيًا لمخاطر الاستثمار.
    </p>
</section>

</body>
</html>
