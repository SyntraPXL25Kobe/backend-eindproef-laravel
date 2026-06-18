import { useMemo, useState } from 'react';
import { useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type ApplicationStatus = 'pending' | 'approved' | 'rejected';

type EventApplication = {
    id: number;
    status: ApplicationStatus;
    motivation: string | null;
    created_at: string | null;
    reviewed_at: string | null;
    user: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
    };
    zone: {
        id: number;
        name: string;
    };
    shift: {
        id: number;
        title: string;
        starts_at: string | null;
        ends_at: string | null;
        capacity: number;
        approved_count: number;
    };
};

type Props = {
    applications: EventApplication[];
};

type ReviewFormData = {
    status: Exclude<ApplicationStatus, 'pending'>;
};

const STATUS_LABELS: Record<ApplicationStatus, string> = {
    pending: 'Pending',
    approved: 'Goedgekeurd',
    rejected: 'Afgewezen',
};

function formatDateTime(value: string | null): string {
    if (!value) {
        return 'Nog niet gepland';
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

function ApplicationReviewCard({
    application,
}: {
    application: EventApplication;
}) {
    const form = useForm<ReviewFormData>({
        status: 'approved',
    });

    const occupancyText = `${application.shift.approved_count}/${application.shift.capacity}`;

    return (
        <Card className="h-full border-border/70 bg-card/95">
            <CardHeader className="space-y-3">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <CardTitle className="text-base">
                            {application.user.name}
                        </CardTitle>
                        <CardDescription>
                            {application.user.email}
                            {application.user.phone
                                ? ` · ${application.user.phone}`
                                : ''}
                        </CardDescription>
                    </div>
                    <div className="flex items-center gap-2">
                        <Badge variant="outline">{application.zone.name}</Badge>
                        <Badge>{STATUS_LABELS[application.status]}</Badge>
                    </div>
                </div>

                <div className="grid gap-2 text-sm text-muted-foreground sm:grid-cols-2">
                    <p>
                        <span className="font-medium text-foreground">
                            Shift:
                        </span>{' '}
                        {application.shift.title}
                    </p>
                    <p>
                        <span className="font-medium text-foreground">
                            Wanneer:
                        </span>{' '}
                        {formatDateTime(application.shift.starts_at)} -{' '}
                        {formatDateTime(application.shift.ends_at)}
                    </p>
                    <p>
                        <span className="font-medium text-foreground">
                            Bezetting:
                        </span>{' '}
                        {occupancyText}
                    </p>
                    <p>
                        <span className="font-medium text-foreground">
                            Aangevraagd:
                        </span>{' '}
                        {formatDateTime(application.created_at)}
                    </p>
                    {application.reviewed_at && (
                        <p>
                            <span className="font-medium text-foreground">
                                Laatst behandeld:
                            </span>{' '}
                            {formatDateTime(application.reviewed_at)}
                        </p>
                    )}
                </div>
            </CardHeader>

            <CardContent className="space-y-4">
                <div className="rounded-lg border border-border/70 bg-muted/30 p-3 text-sm">
                    <p className="mb-1 font-medium text-foreground">
                        Motivatie
                    </p>
                    <p className="text-muted-foreground">
                        {application.motivation || 'Geen motivatie opgegeven.'}
                    </p>
                </div>

                <div className="flex flex-wrap gap-2">
                    {application.status !== 'approved' && (
                        <Button
                            type="button"
                            disabled={form.processing}
                            onClick={() => {
                                form.setData('status', 'approved');
                                form.patch(
                                    `/app/applications/${application.id}/review`,
                                    {
                                        preserveScroll: true,
                                    },
                                );
                            }}
                        >
                            Goedkeuren
                        </Button>
                    )}
                    {application.status !== 'rejected' && (
                        <Button
                            type="button"
                            variant="outline"
                            disabled={form.processing}
                            onClick={() => {
                                form.setData('status', 'rejected');
                                form.patch(
                                    `/app/applications/${application.id}/review`,
                                    {
                                        preserveScroll: true,
                                    },
                                );
                            }}
                        >
                            Afwijzen
                        </Button>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}

export default function CoordinatorEventApplicationsManager({
    applications,
}: Props) {
    const [statusFilter, setStatusFilter] = useState<'all' | ApplicationStatus>(
        'all',
    );

    const filteredApplications = useMemo(
        () =>
            statusFilter === 'all'
                ? applications
                : applications.filter(
                      (application) => application.status === statusFilter,
                  ),
        [applications, statusFilter],
    );

    const countByStatus = useMemo(
        () => ({
            pending: applications.filter(
                (application) => application.status === 'pending',
            ).length,
            approved: applications.filter(
                (application) => application.status === 'approved',
            ).length,
            rejected: applications.filter(
                (application) => application.status === 'rejected',
            ).length,
        }),
        [applications],
    );

    return (
        <section className="space-y-4">
            <Heading
                title="Aanvragen behandelen"
                description="Bekijk alle aanvragen en pas de status aan wanneer nodig."
            />

            <div className="flex flex-wrap gap-2">
                <Button
                    type="button"
                    variant={statusFilter === 'all' ? 'default' : 'outline'}
                    onClick={() => setStatusFilter('all')}
                >
                    Alles ({applications.length})
                </Button>
                <Button
                    type="button"
                    variant={statusFilter === 'pending' ? 'default' : 'outline'}
                    onClick={() => setStatusFilter('pending')}
                >
                    Pending ({countByStatus.pending})
                </Button>
                <Button
                    type="button"
                    variant={
                        statusFilter === 'approved' ? 'default' : 'outline'
                    }
                    onClick={() => setStatusFilter('approved')}
                >
                    Goedgekeurd ({countByStatus.approved})
                </Button>
                <Button
                    type="button"
                    variant={
                        statusFilter === 'rejected' ? 'default' : 'outline'
                    }
                    onClick={() => setStatusFilter('rejected')}
                >
                    Afgewezen ({countByStatus.rejected})
                </Button>
            </div>

            {filteredApplications.length === 0 ? (
                <Card className="border-dashed">
                    <CardContent className="py-8 text-sm text-muted-foreground">
                        Geen aanvragen gevonden voor deze filter.
                    </CardContent>
                </Card>
            ) : (
                <div className="grid gap-4 md:grid-cols-2">
                    {filteredApplications.map((application) => (
                        <ApplicationReviewCard
                            key={application.id}
                            application={application}
                        />
                    ))}
                </div>
            )}
        </section>
    );
}
