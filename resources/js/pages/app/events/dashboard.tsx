import { Head, router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { EventDashboardAttendanceList } from '@/components/event-dashboard/attendance-list';
import { EventDashboardCrewMemberDialog } from '@/components/event-dashboard/crew-member-dialog';
import { NoShowDialog } from '@/components/event-dashboard/no-show-dialog';
import { QrScannerDialog } from '@/components/event-dashboard/qr-scanner-dialog';
import { EventDashboardStatsCards } from '@/components/event-dashboard/stats-cards';
import type {
    EventDashboardAssignment,
    EventDashboardCrewMember,
    EventDashboardEvent,
    EventDashboardScanFeedback,
    EventDashboardStats,
} from '@/components/event-dashboard/types';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

function endpointFor(template: string, assignmentId: number): string {
    return template.replace('__ASSIGNMENT__', assignmentId.toString());
}

export default function CoordinatorEventDashboard({
    event,
    stats,
    assignments,
    last_updated_at,
    scan_endpoint,
    manual_check_in_endpoint,
    manual_check_out_endpoint,
    no_show_endpoint,
}: {
    event: EventDashboardEvent;
    stats: EventDashboardStats;
    assignments: EventDashboardAssignment[];
    last_updated_at: string;
    scan_endpoint: string;
    manual_check_in_endpoint: string;
    manual_check_out_endpoint: string;
    no_show_endpoint: string;
}) {
    const [scannerOpen, setScannerOpen] = useState(false);
    const [scannerPaused, setScannerPaused] = useState(false);
    const [scanFeedback, setScanFeedback] =
        useState<EventDashboardScanFeedback | null>(null);
    const [activeAssignmentId, setActiveAssignmentId] = useState<number | null>(
        null,
    );
    const [noShowAssignment, setNoShowAssignment] =
        useState<EventDashboardAssignment | null>(null);
    const [selectedCrewMemberId, setSelectedCrewMemberId] = useState<
        number | null
    >(null);

    const crewMembers = useMemo<EventDashboardCrewMember[]>(() => {
        const groupedByUser = new Map<number, EventDashboardCrewMember>();

        assignments.forEach((assignment) => {
            const existing = groupedByUser.get(assignment.user.id);

            if (existing) {
                existing.assignments.push(assignment);

                return;
            }

            groupedByUser.set(assignment.user.id, {
                id: assignment.user.id,
                name: assignment.user.name,
                email: assignment.user.email,
                phone: assignment.user.phone,
                assignments: [assignment],
                attendance_history: assignment.user.attendance_history,
            });
        });

        return Array.from(groupedByUser.values()).sort((a, b) =>
            a.name.localeCompare(b.name, 'nl-BE'),
        );
    }, [assignments]);

    const selectedCrewMember = useMemo(
        () =>
            selectedCrewMemberId === null
                ? null
                : (crewMembers.find(
                      (crewMember) => crewMember.id === selectedCrewMemberId,
                  ) ?? null),
        [crewMembers, selectedCrewMemberId],
    );

    useEffect(() => {
        const intervalId = window.setInterval(() => {
            if (!document.hidden && !scannerOpen) {
                router.reload({
                    only: ['stats', 'assignments', 'last_updated_at'],
                });
            }
        }, 15000);

        return () => window.clearInterval(intervalId);
    }, [scannerOpen]);

    useEffect(() => {
        return router.on('flash', (event) => {
            const flash = (event as CustomEvent).detail?.flash as
                | { scan_feedback?: EventDashboardScanFeedback }
                | undefined;

            if (flash?.scan_feedback) {
                setScanFeedback(flash.scan_feedback);
            }
        });
    }, []);

    return (
        <>
            <Head title={`Live dashboard · ${event.title}`} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="mx-auto w-full max-w-7xl space-y-6">
                    <div className="flex flex-col gap-4 rounded-3xl border border-sidebar-border/70 bg-linear-to-br from-background via-card to-muted/40 p-6 lg:flex-row lg:items-end lg:justify-between">
                        <div className="space-y-3">
                            <Heading
                                title={`${event.title} live dashboard`}
                                description={`Realtime check-ins, no-shows en crewstatus voor ${event.location}.`}
                            />
                            <p className="text-sm text-muted-foreground">
                                Laatst ververst:{' '}
                                {new Date(last_updated_at).toLocaleTimeString(
                                    'nl-BE',
                                )}
                            </p>
                        </div>
                        <Button
                            type="button"
                            onClick={() => {
                                setScanFeedback(null);
                                setScannerPaused(false);
                                setScannerOpen(true);
                            }}
                            disabled={!event.is_live_today}
                        >
                            Open scanner
                        </Button>
                    </div>

                    {!event.is_live_today && (
                        <Card className="border-dashed">
                            <CardContent className="pt-6 text-sm text-muted-foreground">
                                Check-in via QR is alleen actief op de dag van
                                het event.
                            </CardContent>
                        </Card>
                    )}

                    <EventDashboardStatsCards stats={stats} />

                    <EventDashboardAttendanceList
                        crewMembers={crewMembers}
                        onOpenDetails={(crewMember) =>
                            setSelectedCrewMemberId(crewMember.id)
                        }
                    />
                </div>
            </div>

            <QrScannerDialog
                open={scannerOpen}
                paused={scannerPaused}
                feedback={scanFeedback}
                onOpenChange={(open) => {
                    setScannerOpen(open);

                    if (!open) {
                        setScanFeedback(null);
                        setScannerPaused(false);
                    }
                }}
                onClearFeedback={() => {
                    setScanFeedback(null);
                    setScannerPaused(false);
                }}
                onScan={(rawValue) => {
                    setScannerPaused(true);
                    setScanFeedback(null);

                    router.post(
                        scan_endpoint,
                        { scan_result: rawValue },
                        {
                            preserveScroll: true,
                            preserveState: true,
                            onFinish: () => {
                                window.setTimeout(() => {
                                    setScannerPaused(false);
                                }, 1200);
                            },
                        },
                    );
                }}
            />

            <NoShowDialog
                assignment={noShowAssignment}
                open={noShowAssignment !== null}
                submitting={
                    noShowAssignment !== null &&
                    activeAssignmentId === noShowAssignment.id
                }
                onOpenChange={(open) => {
                    if (!open) {
                        setNoShowAssignment(null);
                    }
                }}
                onSubmit={(reason) => {
                    if (!noShowAssignment) {
                        return;
                    }

                    setActiveAssignmentId(noShowAssignment.id);
                    router.patch(
                        endpointFor(no_show_endpoint, noShowAssignment.id),
                        {
                            no_show: true,
                            reason: reason.trim() || null,
                        },
                        {
                            preserveScroll: true,
                            onFinish: () => {
                                setActiveAssignmentId(null);
                                setNoShowAssignment(null);
                            },
                        },
                    );
                }}
            />

            <EventDashboardCrewMemberDialog
                crewMember={selectedCrewMember}
                open={selectedCrewMember !== null}
                activeAssignmentId={activeAssignmentId}
                onOpenChange={(open) => {
                    if (!open) {
                        setSelectedCrewMemberId(null);
                    }
                }}
                onCheckIn={(assignmentId) => {
                    setActiveAssignmentId(assignmentId);
                    router.post(
                        endpointFor(manual_check_in_endpoint, assignmentId),
                        {},
                        {
                            preserveScroll: true,
                            onFinish: () => setActiveAssignmentId(null),
                        },
                    );
                }}
                onCheckOut={(assignmentId) => {
                    setActiveAssignmentId(assignmentId);
                    router.post(
                        endpointFor(manual_check_out_endpoint, assignmentId),
                        {},
                        {
                            preserveScroll: true,
                            onFinish: () => setActiveAssignmentId(null),
                        },
                    );
                }}
                onOpenNoShow={(assignment) => setNoShowAssignment(assignment)}
                onClearNoShow={(assignmentId) => {
                    setActiveAssignmentId(assignmentId);
                    router.patch(
                        endpointFor(no_show_endpoint, assignmentId),
                        { no_show: false },
                        {
                            preserveScroll: true,
                            onFinish: () => setActiveAssignmentId(null),
                        },
                    );
                }}
            />
        </>
    );
}

CoordinatorEventDashboard.layout = {
    breadcrumbs: [
        {
            title: 'Mijn events',
            href: '/app/events',
        },
        {
            title: 'Live dashboard',
            href: '/app/events',
        },
    ],
};
