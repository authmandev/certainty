/**
 * Illustrative figures for the landing page only. They are not real customer
 * data or platform statistics, and every place that shows them is labelled
 * "مثال توضيحي".
 */
export const sampleYears = [1, 2, 3, 4, 5];

export const sampleRevenue = [420_000, 515_000, 600_000, 676_000, 748_000];

export const sampleCosts = [395_000, 438_000, 472_000, 508_000, 541_000];

export const sampleProfit = sampleRevenue.map(
    (revenue, index) => revenue - sampleCosts[index],
);

export const sampleCapital = {
    total: 350_000,
    loan: 150_000,
    selfFinanced: 200_000,
};

export const sampleIndicators = {
    averageProfit: 121_000,
    paybackYears: 4,
    marketShare: 2.4,
    profitMargin: 27.7,
};

export const sampleCurrency = 'ر.س';

export type StatementRow = {
    label: string;
    values: number[];
    emphasis?: boolean;
};

export const sampleStatements: Record<
    'income' | 'cashFlow' | 'balance',
    { title: string; rows: StatementRow[] }
> = {
    income: {
        title: 'قائمة الدخل',
        rows: [
            { label: 'إيرادات تشغيلية', values: [420_000, 515_000, 600_000] },
            {
                label: 'تكلفة مواد أولية',
                values: [-168_000, -206_000, -240_000],
            },
            { label: 'رواتب موظفين', values: [-144_000, -144_000, -144_000] },
            { label: 'تكاليف تشغيلية', values: [-62_000, -66_000, -66_000] },
            {
                label: 'تكاليف استهلاك أصول',
                values: [-21_000, -22_000, -22_000],
            },
            {
                label: 'صافي الربح',
                values: [25_000, 77_000, 128_000],
                emphasis: true,
            },
        ],
    },
    cashFlow: {
        title: 'التدفقات النقدية',
        rows: [
            {
                label: 'من العمليات التشغيلية',
                values: [46_000, 99_000, 150_000],
            },
            { label: 'من العمليات الاستثمارية', values: [0, 0, -18_000] },
            {
                label: 'من العمليات التمويلية',
                values: [-36_500, -36_500, -36_500],
            },
            {
                label: 'السيولة في نهاية السنة',
                values: [9_500, 72_000, 167_500],
                emphasis: true,
            },
        ],
    },
    balance: {
        title: 'الميزانية',
        rows: [
            { label: 'أصول قصيرة المدى', values: [9_500, 72_000, 167_500] },
            { label: 'أصول طويلة المدى', values: [329_000, 307_000, 303_000] },
            { label: 'مطلوبات طويلة المدى', values: [120_000, 90_000, 60_000] },
            {
                label: 'حقوق الملاك',
                values: [218_500, 289_000, 410_500],
                emphasis: true,
            },
        ],
    },
};
