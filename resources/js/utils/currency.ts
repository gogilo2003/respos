import { usePage } from '@inertiajs/vue3';

export function formatCurrency(
    amount: number | string | null | undefined,
): string {
    const numericAmount = Number(amount) || 0;
    let symbol = 'KES ';

    try {
        const page = usePage();
        const currencyProp = (page.props as any)?.currency;
        if (
            currencyProp?.symbol !== undefined &&
            currencyProp?.symbol !== null
        ) {
            symbol = currencyProp.symbol;
        }
    } catch (e) {
        // Fallback when outside Vue/Inertia context (e.g. unit tests or store init)
    }

    const formatted = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numericAmount);

    return `${symbol}${formatted}`;
}
