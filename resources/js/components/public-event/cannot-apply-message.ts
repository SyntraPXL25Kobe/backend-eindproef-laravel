import type { Shift } from '@/components/public-event/types';

export function cannotApplyMessage(shift: Shift): string | null {
    if (!shift.cannot_apply_reason) {
        return null;
    }

    const statusLabel: Record<string, string> = {
        pending: 'in behandeling',
        approved: 'goedgekeurd',
        rejected: 'afgewezen',
    };

    const messages: Record<
        NonNullable<Shift['cannot_apply_reason']>,
        string
    > = {
        overlap:
            'Je hebt al een aanvraag voor een shift met overlappende uren.',
        rejected:
            'Je aanvraag voor deze shift is afgewezen. Opnieuw aanmelden is niet mogelijk.',
        already_applied: `Je status voor deze shift is ${statusLabel[shift.application?.status ?? ''] ?? 'onbekend'}.`,
        shift_closed: 'Deze shift staat momenteel niet open voor aanvragen.',
    };

    return messages[shift.cannot_apply_reason];
}
