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

function historyActionLabel(action: string): string {
    if (action === 'checked_in') {
        return 'Ingecheckt';
    }

    if (action === 'checked_out') {
        return 'Uitgecheckt';
    }

    return action;
}

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

function shiftStatusBadge(assignment: EventDashboardAssignment) {
    if (assignment.check_in_at !== null && assignment.check_out_at === null) {
        return <Badge className="bg-emerald-600 text-white">Actief</Badge>;
    }

    if (assignment.check_out_at !== null) {
        return <Badge className="bg-sky-600 text-white">Afgerond</Badge>;
    }

    if (assignment.no_show) {
        return <Badge variant="destructive">No-show</Badge>;
    }

    return <Badge variant="outline">Gepland</Badge>;
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
            <DialogContent className="max-h-[90vh] max-w-5xl overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>
                        {crewMember ? crewMember.name : 'Crew member'}
                    </DialogTitle>
                    <DialogDescription>
                        Eventstatus, historiek en no-show beheer per shift.
                    </DialogDescription>
                </DialogHeader>

                {crewMember ? (
                    <div className="space-y-4">
                        <div className="grid gap-3 rounded-xl border border-border/70 bg-muted/20 p-4 md:grid-cols-2">
                            <div className="space-y-2 text-sm text-muted-foreground">
                                <p className="text-xs font-medium tracking-wide text-muted-foreground/80 uppercase">
                                    Contact
                                </p>
                                <p>{crewMember.email}</p>
                                <p>
                                    {crewMember.phone || 'Geen telefoonnummer'}
                                </p>
                            </div>

                            <div className="space-y-2 text-sm">
                                <p className="text-xs font-medium tracking-wide text-muted-foreground/80 uppercase">
                                    Eventstatus
                                </p>
                                {crewMemberStatusBadge(crewMember)}
                                <div className="flex flex-wrap gap-2 text-xs text-muted-foreground">
                                    <Badge variant="outline">
                                        {crewMember.assignments.length} shift
                                        {crewMember.assignments.length === 1
                                            ? ''
                                            : 's'}
                                    </Badge>
                                    <Badge variant="outline">
                                        {crewMember.attendance_history.length}{' '}
                                        historiekitems
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        {primaryAssignmentId && canManageCheckIn && (
                            <div className="rounded-xl border border-border/70 p-3">
                                <p className="mb-2 text-sm font-medium">
                                    Snelle actie
                                </p>
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
                            </div>
                        )}

                        <div className="rounded-xl border border-border/70 p-3">
                            <p className="mb-3 text-sm font-medium">
                                Check-in/check-out historiek
                            </p>

                            {crewMember.attendance_history.length > 0 ? (
                                <div className="space-y-2">
                                    {crewMember.attendance_history.map(
                                        (entry, index) => (
                                            <div
                                                key={`${entry.performed_at}-${index}`}
                                                className="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-border/60 bg-muted/20 p-2 text-sm"
                                            >
                                                <span className="font-medium text-foreground">
                                                    {historyActionLabel(
                                                        entry.action,
                                                    )}
                                                </span>
                                                <span className="text-muted-foreground">
                                                    {formatDateTimeNl(
                                                        entry.performed_at,
                                                    )}
                                                    {entry.source
                                                        ? ` · ${entry.source}`
                                                        : ''}
                                                </span>
                                            </div>
                                        ),
                                    )}
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    Nog geen check-in/check-out historiek.
                                </p>
                            )}
                        </div>

                        <div className="space-y-3">
                            <p className="text-sm font-medium">Shiften</p>
                            {crewMember.assignments.map((assignment) => {
                                const isBusy =
                                    activeAssignmentId === assignment.id;

                                return (
                                    <div
                                        key={assignment.id}
                                        className="rounded-xl border border-border/70 bg-card p-3"
                                    >
                                        <div className="mb-3 flex flex-wrap items-start justify-between gap-2">
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
                                            {shiftStatusBadge(assignment)}
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
