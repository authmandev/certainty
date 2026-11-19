/**
 * Legacy-form inputs for the regression fixtures (BRD FR-028). Values are what
 * the old form fields would hold; null means an empty field.
 */
const base = {
    currency_symbol: 'ر.س.',
    decimals: 2,
    vat: 0,
    study_years: 3,
    market_value: null,
    loans: [],
    assets: [],
    employees: [],
    fixed_costs: [],
    products: [],
};

const cafeAssets = [
    { name: 'آلة قهوة', value: 60000, life_span: 5, depreciation: 20, includes_vat: false },
    { name: 'ديكورات', value: 90000, life_span: 10, depreciation: 10, includes_vat: false },
];

const cafeProducts = [
    { name: 'قهوة', vc: 6, quantity: 3000, price: 18, capacity: 4500, yearly_increase: 10 },
];

export const scenarios = [
    {
        name: 'no-loans',
        description: 'Self-financed project, no VAT, one product with growth.',
        form: {
            ...base,
            assets: cafeAssets,
            employees: [{ title: 'باريستا', total: 2, salary: 5000 }],
            fixed_costs: [{ name: 'إيجار', amount: 8000, includes_vat: false }],
            products: cafeProducts,
        },
    },
    {
        name: 'interest-free-loan',
        description: 'A zero-interest loan repaid over three years.',
        form: {
            ...base,
            assets: cafeAssets,
            loans: [{ bank: 'صندوق التنمية', amount: 100000, interest: 0, years: 3 }],
            fixed_costs: [{ name: 'إيجار', amount: 8000, includes_vat: false }],
            products: cafeProducts,
        },
    },
    {
        name: 'interest-bearing-loan',
        description: 'A 6% loan with VAT on sales and on a fixed cost.',
        form: {
            ...base,
            vat: 15,
            assets: cafeAssets,
            loans: [{ bank: 'البنك الأهلي', amount: 120000, interest: 6, years: 4 }],
            employees: [{ title: 'باريستا', total: 3, salary: 4500 }],
            fixed_costs: [
                { name: 'إيجار', amount: 8000, includes_vat: false },
                { name: 'كهرباء', amount: 1500, includes_vat: true },
            ],
            products: cafeProducts,
        },
    },
    {
        name: 'multiple-loans-with-unused-funds',
        description: 'Two loans whose total exceeds the setup costs, creating unused cash.',
        form: {
            ...base,
            study_years: 5,
            assets: [{ name: 'معدات', value: 100000, life_span: 5, depreciation: 20, includes_vat: false }],
            loans: [
                { bank: 'بنك أ', amount: 90000, interest: 5, years: 3 },
                { bank: 'بنك ب', amount: 40000, interest: 0, years: 2 },
            ],
            products: [{ name: 'خدمة', vc: 20, quantity: 400, price: 120, capacity: 500, yearly_increase: 5 }],
        },
    },
    {
        name: 'multiple-products-capacity-limits',
        description: 'Several products, one capped by capacity and one with capacity below quantity.',
        form: {
            ...base,
            vat: 15,
            study_years: 5,
            assets: cafeAssets,
            employees: [{ title: 'عامل', total: 4, salary: 4000 }],
            products: [
                { name: 'قهوة', vc: 6, quantity: 3000, price: 18, capacity: 3500, yearly_increase: 20 },
                { name: 'حلى', vc: 9, quantity: 800, price: 22, capacity: 500, yearly_increase: 15 },
                { name: 'عصير', vc: 4.5, quantity: 1250.5, price: 14, capacity: null, yearly_increase: 7.5 },
            ],
        },
    },
    {
        name: 'zero-sales-negative-profit',
        description: 'No sales at all, so every year is a loss.',
        form: {
            ...base,
            assets: cafeAssets,
            employees: [{ title: 'مدير', total: 1, salary: 9000 }],
            fixed_costs: [{ name: 'إيجار', amount: 8000, includes_vat: false }],
            products: [{ name: 'قهوة', vc: 6, quantity: 0, price: 18, capacity: 4500, yearly_increase: 10 }],
        },
    },
    {
        name: 'asset-replacement',
        description: 'Assets with short lives are sold and repurchased; a zero-life asset is never replaced.',
        form: {
            ...base,
            study_years: 5,
            assets: [
                { name: 'حاسب', value: 12000, life_span: 2, depreciation: 30, includes_vat: true },
                { name: 'سيارة', value: 80000, life_span: 3, depreciation: 25, includes_vat: false },
                { name: 'أرض', value: 200000, life_span: 0, depreciation: 0, includes_vat: false },
            ],
            products: cafeProducts,
        },
    },
    {
        name: 'vat-on-everything',
        description: 'Non-zero VAT with VAT flags on assets and fixed costs.',
        form: {
            ...base,
            vat: 15,
            study_years: 2,
            assets: [{ name: 'تجهيزات', value: 50000, life_span: 4, depreciation: 25, includes_vat: true }],
            employees: [{ title: 'كاشير', total: 2, salary: 4000 }],
            fixed_costs: [
                { name: 'صيانة', amount: 1000, includes_vat: true },
                { name: 'تسويق', amount: 2500, includes_vat: true },
            ],
            products: cafeProducts,
        },
    },
    {
        name: 'ten-years-market-share',
        description: 'A ten-year study with a market size, for market share.',
        form: {
            ...base,
            vat: 15,
            study_years: 10,
            market_value: 50000000,
            assets: cafeAssets,
            loans: [{ bank: 'بنك', amount: 80000, interest: 4.5, years: 5 }],
            employees: [{ title: 'باريستا', total: 2, salary: 5000 }],
            fixed_costs: [{ name: 'إيجار', amount: 8000, includes_vat: false }],
            products: cafeProducts,
        },
    },
    {
        name: 'study-fee-setup-cost',
        description: 'The study fee as a non-depreciable, never-replaced setup cost (BRD A-05 Option A).',
        form: {
            ...base,
            assets: [
                ...cafeAssets,
                { name: 'رسوم إعداد دراسة الجدوى', value: 1200, life_span: 0, depreciation: 0, includes_vat: false },
            ],
            fixed_costs: [{ name: 'إيجار', amount: 8000, includes_vat: false }],
            products: cafeProducts,
        },
    },
    {
        name: 'incomplete-rows-dropped',
        description: 'Rows the legacy form silently ignores: no name, no bank, zero repayment years.',
        form: {
            ...base,
            assets: [...cafeAssets, { name: null, value: 5000, life_span: 2, depreciation: 10, includes_vat: false }],
            loans: [
                { bank: null, amount: 50000, interest: 5, years: 3 },
                { bank: 'بنك', amount: 50000, interest: 5, years: 0 },
                { bank: 'بنك صالح', amount: null, interest: null, years: 2 },
            ],
            employees: [{ title: null, total: 1, salary: 3000 }],
            fixed_costs: [{ name: null, amount: 1000, includes_vat: false }],
            products: [...cafeProducts, { name: null, vc: 1, quantity: 10, price: 2, capacity: 10, yearly_increase: 0 }],
        },
    },
    {
        name: 'payback-in-first-year',
        description: 'Capital recovered within the first year shows as not available (BRD Q-05).',
        form: {
            ...base,
            study_years: 2,
            assets: [{ name: 'معدات بسيطة', value: 10000, life_span: 5, depreciation: 10, includes_vat: false }],
            products: [{ name: 'خدمة', vc: 10, quantity: 1000, price: 60, capacity: 1000, yearly_increase: 0 }],
        },
    },
    {
        name: 'one-year-study',
        description: 'The shortest study length.',
        form: {
            ...base,
            study_years: 1,
            assets: cafeAssets,
            fixed_costs: [{ name: 'إيجار', amount: 8000, includes_vat: false }],
            products: cafeProducts,
        },
    },
];
