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

type PendingApplication = {
    id: number;
    status: string;
    motivation: string | null;
    created_at: string | null;
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
    applications: PendingApplication[];
};

type ReviewFormData = {
    status: 'approved' | 'rejected';
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
    application: PendingApplication;
}) {
    const form = useForm<ReviewFormData>({
        status: 'approved',
    });

    const occupancyText = `${application.shift.approved_count}/${application.shift.capacity}`;

    return (
        <Card className="border-border/70 bg-card/95">
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
                    <Badge variant="outline">{application.zone.name}</Badge>
                </div>

                <div className="grid gap-1 text-sm text-muted-foreground">
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
                </div>
            </CardContent>
        </Card>
    );
}

export default function CoordinatorEventApplicationsManager({
    applications,
}: Props) {
    return (
        <section className="space-y-4">
            <Heading
                title="Aanvragen behandelen"
                description="Bekijk pending shift-aanvragen en keur ze goed of wijs ze af."
            />

            {applications.length === 0 ? (
                <Card className="border-dashed">
                    <CardContent className="py-8 text-sm text-muted-foreground">
                        Er zijn momenteel geen pending aanvragen voor dit event.
                    </CardContent>
                </Card>
            ) : (
                <div className="grid gap-4">
                    {applications.map((application) => (
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
