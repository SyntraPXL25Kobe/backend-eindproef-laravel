export function formatDateTimeNl(
    value: string | null,
    missingFallback = 'Nog niet gepland',
): string {
    if (!value) {
        return missingFallback;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return 'Onbekende datum';
    }

    return new Intl.DateTimeFormat('nl-BE', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
}
