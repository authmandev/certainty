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

/**
 * Short amounts for chart axes, such as "1.2 مليون".
 */
export function formatCompact(value: number): string {
    return new Intl.NumberFormat(LOCALE, {
        notation: 'compact',
        maximumFractionDigits: 1,
    }).format(value);
}

export function formatPercent(value: number, decimals = 1): string {
    return `${formatNumber(value, decimals)}%`;
}

export function formatDateTime(value: string): string {
    return new Intl.DateTimeFormat(LOCALE, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

export function formatFileSize(bytes: number): string {
    return bytes >= 1024 * 1024
        ? `${formatNumber(bytes / (1024 * 1024), 1)} ميجابايت`
        : `${formatNumber(Math.max(1, Math.round(bytes / 1024)))} كيلوبايت`;
}
