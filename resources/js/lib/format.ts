/**
 * Arabic formatting with Latin digits and the Gregorian calendar, so amounts
 * and dates read the same in reports, the dashboard and the admin panel.
 */
const LOCALE = 'ar-SA-u-nu-latn-ca-gregory';

export function formatNumber(value: number | string, decimals = 0): string {
    return new Intl.NumberFormat(LOCALE, {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(Number(value));
}

export function formatCurrency(
    value: number | string,
    currency: string,
    decimals = 0,
): string {
    return new Intl.NumberFormat(LOCALE, {
        style: 'currency',
        currency,
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(Number(value));
}

export function formatDate(value: string): string {
    return new Intl.DateTimeFormat(LOCALE, { dateStyle: 'medium' }).format(
        new Date(value),
    );
}
