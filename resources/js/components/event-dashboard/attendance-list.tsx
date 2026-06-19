import type { ColumnDef } from '@tanstack/react-table';
import type { EventDashboardCrewMember } from '@/components/event-dashboard/types';
import { Badge } from '@/components/ui/badge';
import { DataTable } from '@/components/ui/data-table';

export function EventDashboardAttendanceList({
    crewMembers,
    onOpenDetails,
}: {
    crewMembers: EventDashboardCrewMember[];
    onOpenDetails: (crewMember: EventDashboardCrewMember) => void;
}) {
    const columns: ColumnDef<EventDashboardCrewMember>[] = [
        {
            accessorKey: 'name',
            header: 'Naam',
            cell: ({ row }) => (
                <div className="font-medium">{row.original.name}</div>
            ),
        },
        {
            accessorKey: 'email',
            header: 'E-mail',
            cell: ({ row }) => (
                <div className="text-sm text-muted-foreground">
                    {row.original.email}
                </div>
            ),
        },
        {
            accessorKey: 'phone',
            header: 'Telefoon',
            cell: ({ row }) =>
                row.original.phone ? (
                    <div className="text-sm">{row.original.phone}</div>
                ) : (
                    <span className="text-xs text-muted-foreground">—</span>
                ),
        },
        {
            id: 'status',
            header: 'Status',
            cell: ({ row }) =>
                (() => {
                    const assignments = row.original.assignments;
                    const hasOpenCheckIn = assignments.some(
                        (assignment) =>
                            assignment.check_in_at !== null &&
                            assignment.check_out_at === null,
                    );
                    const hasCheckedOut = assignments.some(
                        (assignment) => assignment.check_out_at !== null,
                    );
                    const hasNoShow = assignments.some(
                        (assignment) => assignment.no_show,
                    );

                    if (hasOpenCheckIn) {
                        return (
                            <Badge className="bg-emerald-600 text-white hover:bg-emerald-600">
                                Ingecheckt
                            </Badge>
                        );
                    }

                    if (hasCheckedOut) {
                        return (
                            <Badge className="bg-sky-600 text-white hover:bg-sky-600">
                                Uitgecheckt
                            </Badge>
                        );
                    }

                    if (hasNoShow) {
                        return <Badge variant="destructive">No-show</Badge>;
                    }

                    return <Badge variant="outline">Wacht op check-in</Badge>;
                })(),
        },
        {
            id: 'shifts_count',
            header: 'Aantal shifts',
            cell: ({ row }) => row.original.assignments.length,
        },
    ];

    return (
        <div className="w-full">
            <DataTable
                columns={columns}
                data={crewMembers}
                searchPlaceholder="Zoeken op naam..."
                searchColumn="name"
                onRowClick={onOpenDetails}
                rowClassName="hover:bg-muted/40"
            />
        </div>
    );
}
