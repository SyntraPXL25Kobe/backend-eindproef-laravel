import type {
    EventDashboardAssignment,
    EventDashboardCrewMember,
} from '@/components/event-dashboard/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { formatDateTimeNl } from '@/lib/format-date-time';

function crewMemberStatusBadge(crewMember: EventDashboardCrewMember) {
    const hasOpenCheckIn = crewMember.assignments.some(
        (assignment) =>
            assignment.check_in_at !== null && assignment.check_out_at === null,
    );
    const hasCheckedOut = crewMember.assignments.some(
        (assignment) => assignment.check_out_at !== null,
    );
    const hasNoShow = crewMember.assignments.some(
        (assignment) => assignment.no_show,
    );

    if (hasOpenCheckIn) {
        return <Badge className="bg-emerald-600 text-white">Ingecheckt</Badge>;
    }

    if (hasCheckedOut) {
        return <Badge className="bg-sky-600 text-white">Uitgecheckt</Badge>;
    }

    if (hasNoShow) {
        return <Badge variant="destructive">No-show</Badge>;
    }

    return <Badge variant="outline">Wacht op check-in</Badge>;
}

export function EventDashboardCrewMemberDialog({
    crewMember,
    open,
    activeAssignmentId,
    onOpenChange,
    onCheckIn,
    onCheckOut,
    onOpenNoShow,
    onClearNoShow,
}: {
    crewMember: EventDashboardCrewMember | null;
    open: boolean;
    activeAssignmentId: number | null;
    onOpenChange: (open: boolean) => void;
    onCheckIn: (assignmentId: number) => void;
    onCheckOut: (assignmentId: number) => void;
    onOpenNoShow: (assignment: EventDashboardAssignment) => void;
    onClearNoShow: (assignmentId: number) => void;
}) {
    const primaryAssignmentId = crewMember?.assignments[0]?.id ?? null;
    const canManageCheckIn =
        crewMember?.assignments.some((assignment) => assignment.can_check_in) ??
        false;
    const hasOpenCheckIn =
        crewMember?.assignments.some(
            (assignment) =>
                assignment.check_in_at !== null &&
                assignment.check_out_at === null,
        ) ?? false;
    const crewActionBusy =
        crewMember !== null &&
        activeAssignmentId !== null &&
        crewMember.assignments.some(
            (assignment) => assignment.id === activeAssignmentId,
        );

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-4xl">
                <DialogHeader>
                    <DialogTitle>
                        {crewMember ? crewMember.name : 'Crew member'}
                    </DialogTitle>
                    <DialogDescription>
                        Overzicht van shiften en no-show status per shift.
                    </DialogDescription>
                </DialogHeader>

                {crewMember ? (
                    <div className="space-y-4">
                        <div className="rounded-xl border border-border/70 bg-muted/20 p-3 text-sm text-muted-foreground">
                            <div className="mb-2">
                                {crewMemberStatusBadge(crewMember)}
                            </div>
                            <p>{crewMember.email}</p>
                            <p>{crewMember.phone || 'Geen telefoonnummer'}</p>
                        </div>

                        {primaryAssignmentId && canManageCheckIn && (
                            <div className="flex flex-wrap gap-2">
                                {!hasOpenCheckIn ? (
                                    <Button
                                        type="button"
                                        disabled={crewActionBusy}
                                        onClick={() =>
                                            onCheckIn(primaryAssignmentId)
                                        }
                                    >
                                        {crewActionBusy
                                            ? 'Verwerken...'
                                            : 'Crew member inchecken'}
                                    </Button>
                                ) : (
                                    <Button
                                        type="button"
                                        variant="default"
                                        disabled={crewActionBusy}
                                        onClick={() =>
                                            onCheckOut(primaryAssignmentId)
                                        }
                                    >
                                        {crewActionBusy
                                            ? 'Verwerken...'
                                            : 'Crew member uitchecken'}
                                    </Button>
                                )}
                            </div>
                        )}

                        <div className="space-y-3">
                            {crewMember.assignments.map((assignment) => {
                                const isBusy =
                                    activeAssignmentId === assignment.id;

                                return (
                                    <div
                                        key={assignment.id}
                                        className="rounded-xl border border-border/70 p-3"
                                    >
                                        <div className="mb-3">
                                            <div>
                                                <p className="font-medium">
                                                    {assignment.shift.title}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {assignment.shift.zone_name}{' '}
                                                    ·{' '}
                                                    {formatDateTimeNl(
                                                        assignment.shift
                                                            .starts_at,
                                                        'Onbekende start',
                                                    )}{' '}
                                                    -{' '}
                                                    {formatDateTimeNl(
                                                        assignment.shift
                                                            .ends_at,
                                                        'Onbekend einde',
                                                    )}
                                                </p>
                                            </div>
                                        </div>

                                        {assignment.no_show &&
                                            assignment.no_show_reason && (
                                                <p className="mb-3 text-sm text-muted-foreground">
                                                    Reden no-show:{' '}
                                                    {assignment.no_show_reason}
                                                </p>
                                            )}

                                        <div className="flex flex-wrap gap-2">
                                            {!assignment.no_show &&
                                                assignment.can_mark_no_show && (
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        disabled={isBusy}
                                                        onClick={() =>
                                                            onOpenNoShow(
                                                                assignment,
                                                            )
                                                        }
                                                    >
                                                        No-show
                                                    </Button>
                                                )}

                                            {assignment.no_show &&
                                                assignment.can_mark_no_show && (
                                                    <Button
                                                        size="sm"
                                                        variant="secondary"
                                                        disabled={isBusy}
                                                        onClick={() =>
                                                            onClearNoShow(
                                                                assignment.id,
                                                            )
                                                        }
                                                    >
                                                        Wis no-show
                                                    </Button>
                                                )}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                ) : null}
            </DialogContent>
        </Dialog>
    );
}
