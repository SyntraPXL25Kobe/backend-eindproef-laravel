import { Link } from '@inertiajs/react';
import { cannotApplyMessage } from '@/components/public-event/cannot-apply-message';
import type { AuthUser, Shift, Zone } from '@/components/public-event/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatDateTimeNl } from '@/lib/format-date-time';

function ShiftCard({
    shift,
    user,
    activeShiftId,
    onOpenApply,
    onCancelApplication,
}: {
    shift: Shift;
    user: AuthUser;
    activeShiftId: number | null;
    onOpenApply: (shift: Shift) => void;
    onCancelApplication: (applicationId: number, shiftId: number) => void;
}) {
    const reasonText = cannotApplyMessage(shift);

    return (
        <div className="rounded-2xl border border-border/70 bg-muted/30 p-4">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div className="space-y-2">
                    <div className="flex flex-wrap gap-2">
                        <Badge variant="outline">{shift.status}</Badge>
                        {shift.application && (
                            <Badge>{shift.application.status}</Badge>
                        )}
                        {shift.required_skill_name && (
                            <Badge variant="secondary">
                                Skill: {shift.required_skill_name}
                            </Badge>
                        )}
                    </div>
                    <h3 className="text-lg font-medium text-foreground">
                        {shift.title}
                    </h3>
                    <p className="text-sm text-muted-foreground">
                        {shift.description ||
                            'Geen extra shiftbeschrijving beschikbaar.'}
                    </p>
                </div>
                <div className="text-right text-sm text-muted-foreground">
                    <p>
                        {formatDateTimeNl(
                            shift.starts_at,
                            'Tijdstip volgt nog',
                        )}
                    </p>
                    <p>
                        tot{' '}
                        {formatDateTimeNl(shift.ends_at, 'Tijdstip volgt nog')}
                    </p>
                    <p>{shift.capacity} plaatsen</p>
                </div>
            </div>

            <CardFooter className="mt-4 flex items-center gap-3 px-0 pb-0">
                {!user && (
                    <Button asChild variant="outline">
                        <Link href="/login">Log in om te applien</Link>
                    </Button>
                )}

                {user && shift.can_apply && (
                    <Button
                        type="button"
                        disabled={activeShiftId === shift.id}
                        onClick={() => onOpenApply(shift)}
                    >
                        {activeShiftId === shift.id
                            ? 'Verwerken...'
                            : 'Apply voor deze shift'}
                    </Button>
                )}

                {user && shift.application && shift.can_cancel && (
                    <Button
                        type="button"
                        variant="outline"
                        disabled={activeShiftId === shift.id}
                        onClick={() =>
                            onCancelApplication(shift.application!.id, shift.id)
                        }
                    >
                        {activeShiftId === shift.id
                            ? 'Verwerken...'
                            : 'Annuleer applicatie'}
                    </Button>
                )}

                {user &&
                    !shift.can_apply &&
                    !shift.can_cancel &&
                    reasonText && (
                        <span className="text-sm text-muted-foreground">
                            {reasonText}
                        </span>
                    )}
            </CardFooter>
        </div>
    );
}

export function ZoneCard({
    zone,
    user,
    activeShiftId,
    onOpenApply,
    onCancelApplication,
}: {
    zone: Zone;
    user: AuthUser;
    activeShiftId: number | null;
    onOpenApply: (shift: Shift) => void;
    onCancelApplication: (applicationId: number, shiftId: number) => void;
}) {
    return (
        <Card className="border-border/70 bg-card/95">
            <CardHeader>
                <CardTitle>{zone.name}</CardTitle>
                <CardDescription>
                    {zone.description ||
                        'Geen extra zonebeschrijving beschikbaar.'}
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                {zone.shifts.length === 0 ? (
                    <div className="rounded-2xl border border-dashed border-border/70 bg-muted/40 p-4 text-sm text-muted-foreground">
                        Voor deze zone zijn nog geen shifts gepubliceerd.
                    </div>
                ) : (
                    zone.shifts.map((shift) => (
                        <ShiftCard
                            key={shift.id}
                            shift={shift}
                            user={user}
                            activeShiftId={activeShiftId}
                            onOpenApply={onOpenApply}
                            onCancelApplication={onCancelApplication}
                        />
                    ))
                )}
            </CardContent>
        </Card>
    );
}
