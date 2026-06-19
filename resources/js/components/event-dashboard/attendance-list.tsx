import type { ColumnDef } from '@tanstack/react-table';
import type { EventDashboardAssignment } from '@/components/event-dashboard/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { formatDateTimeNl } from '@/lib/format-date-time';

export function EventDashboardAttendanceList({
    assignments,
    activeAssignmentId,
    onCheckIn,
    onOpenNoShow,
    onClearNoShow,
}: {
    assignments: EventDashboardAssignment[];
    activeAssignmentId: number | null;
    onCheckIn: (assignmentId: number) => void;
    onOpenNoShow: (assignment: EventDashboardAssignment) => void;
    onClearNoShow: (assignmentId: number) => void;
}) {
    const columns: ColumnDef<EventDashboardAssignment>[] = [
        {
            accessorKey: 'user.name',
            header: 'Naam',
            cell: ({ row }) => (
                <div className="font-medium">{row.original.user.name}</div>
            ),
        },
        {
            accessorKey: 'user.email',
            header: 'E-mail',
            cell: ({ row }) => (
                <div className="text-sm text-muted-foreground">
                    {row.original.user.email}
                </div>
            ),
        },
        {
            accessorKey: 'user.phone',
            header: 'Telefoon',
            cell: ({ row }) =>
                row.original.user.phone ? (
                    <div className="text-sm">{row.original.user.phone}</div>
                ) : (
                    <span className="text-xs text-muted-foreground">—</span>
                ),
        },
        {
            accessorKey: 'shift.title',
            header: 'Shift',
            cell: ({ row }) => (
                <div className="text-sm">{row.original.shift.title}</div>
            ),
        },
        {
            accessorKey: 'shift.zone_name',
            header: 'Zone',
            cell: ({ row }) => (
                <div className="text-sm text-muted-foreground">
                    {row.original.shift.zone_name}
                </div>
            ),
        },
        {
            accessorKey: 'check_in_at',
            header: 'Status',
            cell: ({ row }) => {
                if (row.original.check_in_at) {
                    return (
                        <Badge className="bg-emerald-600 text-white hover:bg-emerald-600">
                            Ingecheckt
                        </Badge>
                    );
                }

                if (row.original.no_show) {
                    return <Badge variant="destructive">No-show</Badge>;
                }

                return <Badge variant="outline">Wacht op check-in</Badge>;
            },
        },
        {
            accessorKey: 'check_in_at',
            header: 'Check-in tijd',
            cell: ({ row }) =>
                row.original.check_in_at ? (
                    <div className="text-sm">
                        {formatDateTimeNl(row.original.check_in_at)}
                    </div>
                ) : (
                    <span className="text-xs text-muted-foreground">—</span>
                ),
        },
        {
            id: 'actions',
            header: 'Acties',
            cell: ({ row }) => {
                const assignment = row.original;
                const isBusy = activeAssignmentId === assignment.id;

                return (
                    <div className="flex flex-wrap gap-1">
                        {!assignment.check_in_at && assignment.can_check_in && (
                            <Button
                                size="sm"
                                variant="default"
                                disabled={isBusy}
                                onClick={() => onCheckIn(assignment.id)}
                                className="text-xs"
                            >
                                {isBusy ? 'Verwerken...' : 'Inchecken'}
                            </Button>
                        )}

                        {!assignment.check_in_at &&
                            !assignment.no_show &&
                            assignment.can_mark_no_show && (
                                <Button
                                    size="sm"
                                    variant="outline"
                                    disabled={isBusy}
                                    onClick={() => onOpenNoShow(assignment)}
                                    className="text-xs"
                                >
                                    No-show
                                </Button>
                            )}

                        {assignment.no_show && assignment.can_mark_no_show && (
                            <Button
                                size="sm"
                                variant="secondary"
                                disabled={isBusy}
                                onClick={() => onClearNoShow(assignment.id)}
                                className="text-xs"
                            >
                                Wis no-show
                            </Button>
                        )}
                    </div>
                );
            },
        },
    ];

    return (
        <div className="w-full">
            <DataTable
                columns={columns}
                data={assignments}
                searchPlaceholder="Zoeken op naam..."
                searchColumn="user.name"
            />
        </div>
    );
}
