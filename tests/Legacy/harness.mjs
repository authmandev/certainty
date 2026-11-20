/**
 * Runs the verbatim legacy Cassandra calculation (cassandra-legacy.js) in Node
 * against a minimal fake jQuery, and writes its raw results as JSON fixtures
 * for the PHP regression tests in tests/Feature/Calculation.
 *
 * Usage: node tests/Legacy/harness.mjs
 *
 * The legacy code is not modified, except for two injected lines that copy
 * internal variables out (they do not change any value). The two helpers
 * that add rows to the page while calculating are re-implemented with the
 * same effect on the inputs the calculation reads back.
 */
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import vm from 'node:vm';
import { scenarios } from './scenarios.mjs';

const here = dirname(fileURLToPath(import.meta.url));
const outputDir = join(here, '..', 'Fixtures', 'legacy');

function instrument(source) {
    const hooks = [
        {
            anchor: 'productElement[t]["ProfitMargin"] = (productElement[t]["BreakEvenPrice"] > 0)',
            after: true,
            code: '__capture.pricing.push({ year: i, name: productElement[t]["Name"], breakEven: productElement[t]["BreakEvenPrice"], margin: productElement[t]["ProfitMargin"], quantity: productElement[t]["Quantity"] });',
        },
        {
            anchor: '// Showing Charts',
            after: false,
            code: '__capture.result = { OD: Object.assign({}, OD), years: yearFigures.map((y) => Object.assign({}, y)), assets: assetElement.map((a) => Object.assign({}, a)), liabilities: liabilityElement.map((l) => Object.assign({}, l)), fixedCosts: fcElement.map((f) => Object.assign({}, f)), products: productElement.map((p) => Object.assign({}, p)), chartProfitibility: chartProfitibility, sy: _sy, cvat: _cvat };',
        },
    ];

    let instrumented = source;

    for (const hook of hooks) {
        const lines = instrumented.split('\n');
        const index = lines.findIndex((line) => line.includes(hook.anchor));

        if (index === -1) {
            throw new Error(`Anchor not found: ${hook.anchor}`);
        }

        lines.splice(hook.after ? index + 1 : index, 0, hook.code);
        instrumented = lines.join('\n');
    }

    return instrumented;
}

/**
 * A tiny jQuery stand-in: elements by id, and ordered input lists by name.
 */
function createDom(form) {
    const byId = new Map();
    const byName = new Map();

    const element = (id) => {
        if (!byId.has(id)) {
            byId.set(id, { value: '', html: '', checked: false });
        }

        return byId.get(id);
    };

    const setValue = (id, value) => {
        element(id).value = value === null || value === undefined ? '' : String(value);
    };

    setValue('currencyName', form.currency_symbol);
    setValue('currencyDecimals', form.decimals);
    setValue('currencyVAT', form.vat);
    setValue('studyYears', form.study_years);
    setValue('marketValue', form.market_value);

    const rows = (name) => {
        if (!byName.has(name)) {
            byName.set(name, []);
        }

        return byName.get(name);
    };

    const field = (value, checked = false) => ({
        value: value === null || value === undefined ? '' : String(value),
        checked,
        html: '',
    });

    for (const loan of form.loans) {
        rows('debtBank').push(field(loan.bank));
        rows('debtAmount').push(field(loan.amount));
        rows('debtInterest').push(field(loan.interest));
        rows('settlementYears').push(field(loan.years));
    }

    for (const asset of form.assets) {
        rows('setupCostName').push(field(asset.name));
        rows('setupCostValue').push(field(asset.value));
        rows('setupCostIsCash').push(field(0));
        rows('setupCostLifeSpan').push(field(asset.life_span));
        rows('setupCostDepreciation').push(field(asset.depreciation));
        rows('setupCostIncludesVAT').push(field('', Boolean(asset.includes_vat)));
    }

    for (const employee of form.employees) {
        rows('employeeTitle').push(field(employee.title));
        rows('employeeTotal').push(field(employee.total));
        rows('employeeSalary').push(field(employee.salary));
    }

    for (const cost of form.fixed_costs) {
        rows('fixedCostName').push(field(cost.name));
        rows('fixedCostPaymentPeriod').push(field(0));
        rows('fixedCostAmount').push(field(cost.amount));
        rows('fixedCostIncludesVAT').push(field('', Boolean(cost.includes_vat)));
        rows('fixedCostIsDebtPayment').push(field(0));
    }

    for (const product of form.products) {
        rows('revenueName').push(field(product.name));
        rows('revenueVariableCost').push(field(product.vc));
        rows('revenueQuantity').push(field(product.quantity));
        rows('revenueCapacity').push(field(product.capacity));
        rows('revenueYearlyIncrease').push(field(product.yearly_increase));
        rows('revenuePrice').push(field(product.price));
    }

    const wrap = (target) => ({
        val(value) {
            if (value === undefined) {
                return target.value;
            }

            target.value = String(value);

            return this;
        },
        html(value) {
            if (value === undefined) {
                return target.html;
            }

            target.html = String(value);

            return this;
        },
        append(value) {
            target.html += String(value);

            return this;
        },
        prepend(value) {
            target.html = String(value) + target.html;

            return this;
        },
        is(selector) {
            return selector === ':checked' ? target.checked : true;
        },
        show() {
            return this;
        },
        hide() {
            return this;
        },
        attr() {
            return this;
        },
        remove() {
            return this;
        },
    });

    const $ = (selector) => {
        if (typeof selector === 'object') {
            return wrap(selector);
        }

        const named = /^(input|table)\[name=([A-Za-z_]+)\]$/.exec(selector);

        if (named) {
            const list = named[1] === 'input' ? rows(named[2]) : [];

            return {
                each(callback) {
                    list.forEach((item, index) => callback.call(item, index, item));
                },
            };
        }

        if (selector.startsWith('#')) {
            return wrap(element(selector.slice(1)));
        }

        throw new Error(`Unsupported selector: ${selector}`);
    };

    const addDebtPaymentFixedCost = (name, payment, period) => {
        // Same inputs the legacy helper prepends to #fixedCosts.
        rows('fixedCostName').unshift(field(`قسط مديونية: ${name}`));
        rows('fixedCostPaymentPeriod').unshift(field(period));
        rows('fixedCostAmount').unshift(field(payment));
        rows('fixedCostIncludesVAT').unshift(field('', false));
        rows('fixedCostIsDebtPayment').unshift(field(1));
    };

    return { $, addDebtPaymentFixedCost };
}

function run(scenario, source) {
    const dom = createDom(scenario.form);
    const capture = { pricing: [], charts: [] };

    const context = vm.createContext({
        $: dom.$,
        __capture: capture,
        // Resolves to the <select id="currencyDecimals"> element in the browser (BRD Q-01).
        currencyDecimals: {},
        readableAmount: (value) => String(value),
        htmlspecialchars: (value) => value,
        Math,
        Number,
        String,
        Object,
        isNaN,
        parseFloat,
        JSON,
    });

    vm.runInContext(source, context);

    context.addDebtPaymentFixedCost = dom.addDebtPaymentFixedCost;
    context.addDebtOverflowSetupCost = () => {};
    context.feasibilityEdited = () => {};
    context.drawChart = (type, div, title, data) => {
        capture.charts.push({ type, div, data: JSON.parse(JSON.stringify(data)) });
    };

    context.fieldsUpdated();

    return {
        ...capture.result,
        pricing: capture.pricing,
        charts: capture.charts,
    };
}

const source = instrument(readFileSync(join(here, 'cassandra-legacy.js'), 'utf8'));
mkdirSync(outputDir, { recursive: true });

for (const scenario of scenarios) {
    const expected = run(scenario, source);
    const fixture = {
        name: scenario.name,
        description: scenario.description,
        generated_by: 'tests/Legacy/harness.mjs from the legacy Cassandra source; not client-verified (BRD A-12)',
        form: scenario.form,
        expected,
    };

    writeFileSync(
        join(outputDir, `${scenario.name}.json`),
        `${JSON.stringify(fixture, null, 2)}\n`,
    );
    console.log(`wrote ${scenario.name}.json`);
}
