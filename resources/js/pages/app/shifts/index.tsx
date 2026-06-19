import { Head, Link, router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
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
import { Input } from '@/components/ui/input';

type ApplicationStatus = 'pending' | 'approved' | 'rejected';

type CrewApplication = {
    id: number;
    status: ApplicationStatus;
    motivation: string | null;
    created_at: string | null;
    reviewed_at: string | null;
    can_cancel: boolean;
    shift: {
        id: number | null;
        title: string | null;
        starts_at: string | null;
        ends_at: string | null;
        status: string | null;
        capacity: number | null;
        zone_name: string | null;
        event_title: string | null;
        event_location: string | null;
        event_show_url: string | null;
    };
};

type PageProps = {
    applications: CrewApplication[];
};

const statusLabel: Record<ApplicationStatus, string> = {
    pending: 'Pending',
    approved: 'Goedgekeurd',
    rejected: 'Afgewezen',
};

function formatDateTime(value: string | null): string {
    if (!value) {
        return 'Nog niet ingepland';
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

export default function CrewShiftsIndex() {
    const { applications } = usePage<PageProps>().props;
    const [filter, setFilter] = useState<'all' | ApplicationStatus>('all');
    const [searchQuery, setSearchQuery] = useState('');
    const [activeApplicationId, setActiveApplicationId] = useState<
        number | null
    >(null);

    const filteredApplications = useMemo(() => {
        const normalizedQuery = searchQuery.trim().toLowerCase();

        return applications.filter((application) => {
            const matchesStatus =
                filter === 'all' || application.status === filter;

            if (!matchesStatus) {
                return false;
            }

            if (!normalizedQuery) {
                return true;
            }

            const searchable = [
                application.shift.title,
                application.shift.event_title,
                application.shift.zone_name,
                application.shift.event_location,
                application.motivation,
                statusLabel[application.status],
            ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            return searchable.includes(normalizedQuery);
        });
    }, [applications, filter, searchQuery]);

    const countByStatus = useMemo(
        () => ({
            pending: applications.filter((x) => x.status === 'pending').length,
            approved: applications.filter((x) => x.status === 'approved')
                .length,
            rejected: applications.filter((x) => x.status === 'rejected')
                .length,
        }),
        [applications],
    );

    return (
        <>
            <Head title="Mijn shifts" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="mx-auto w-full max-w-6xl space-y-5">
                    <Heading
                        title="Mijn shifts"
                        description="Overzicht van je aanvragen en shifts, chronologisch gesorteerd."
                    />

                    <Input
                        value={searchQuery}
                        onChange={(event) =>
                            setSearchQuery(event.currentTarget.value)
                        }
                        placeholder="Zoek op shift, event, zone, locatie, motivatie of status"
                        className="max-w-xl"
                    />

                    <div className="flex flex-wrap gap-2">
                        <Button
                            type="button"
                            variant={filter === 'all' ? 'default' : 'outline'}
                            onClick={() => setFilter('all')}
                        >
                            Alles ({applications.length})
                        </Button>
                        <Button
                            type="button"
                            variant={
                                filter === 'pending' ? 'default' : 'outline'
                            }
                            onClick={() => setFilter('pending')}
                        >
                            Pending ({countByStatus.pending})
                        </Button>
                        <Button
                            type="button"
                            variant={
                                filter === 'approved' ? 'default' : 'outline'
                            }
                            onClick={() => setFilter('approved')}
                        >
                            Goedgekeurd ({countByStatus.approved})
                        </Button>
                        <Button
                            type="button"
                            variant={
                                filter === 'rejected' ? 'default' : 'outline'
                            }
                            onClick={() => setFilter('rejected')}
                        >
                            Afgewezen ({countByStatus.rejected})
                        </Button>
                    </div>

                    {filteredApplications.length === 0 ? (
                        <Card className="border-dashed">
                            <CardHeader>
                                <CardTitle>Geen shifts gevonden</CardTitle>
                                <CardDescription>
                                    Pas je filter of zoekterm aan, of bekijk
                                    publieke events om te applyen.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Button asChild variant="outline">
                                    <Link href="/app">Naar dashboard</Link>
                                </Button>
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="grid gap-4 md:grid-cols-2">
                            {filteredApplications.map((application) => (
                                <Card
                                    key={application.id}
                                    className="h-full border-border/70 bg-card/95"
                                >
                                    <CardHeader>
                                        <div className="flex flex-wrap items-start justify-between gap-3">
                                            <div>
                                                <CardTitle className="text-base">
                                                    {application.shift.title ||
                                                        'Onbekende shift'}
                                                </CardTitle>
                                                <CardDescription>
                                                    {application.shift
                                                        .event_title ||
                                                        'Onbekend event'}
                                                    {application.shift
                                                        .event_location
                                                        ? ` · ${application.shift.event_location}`
                                                        : ''}
                                                </CardDescription>
                                            </div>
                                            <Badge>
                                                {
                                                    statusLabel[
                                                        application.status
                                                    ]
                                                }
                                            </Badge>
                                        </div>
                                    </CardHeader>
                                    <CardContent className="space-y-3 text-sm">
                                        <div className="space-y-1 text-muted-foreground">
                                            <p>
                                                <span className="font-medium text-foreground">
                                                    Zone:
                                                </span>{' '}
                                                {application.shift.zone_name ||
                                                    'Onbekend'}
                                            </p>
                                            <p>
                                                <span className="font-medium text-foreground">
                                                    Wanneer:
                                                </span>{' '}
                                                {formatDateTime(
                                                    application.shift.starts_at,
                                                )}{' '}
                                                -{' '}
                                                {formatDateTime(
                                                    application.shift.ends_at,
                                                )}
                                            </p>
                                            <p>
                                                <span className="font-medium text-foreground">
                                                    Aangevraagd op:
                                                </span>{' '}
                                                {formatDateTime(
                                                    application.created_at,
                                                )}
                                            </p>
                                        </div>

                                        <div className="rounded-lg border border-border/70 bg-muted/30 p-3 text-muted-foreground">
                                            <p className="mb-1 font-medium text-foreground">
                                                Motivatie
                                            </p>
                                            <p>
                                                {application.motivation ||
                                                    'Geen motivatie ingevuld.'}
                                            </p>
                                        </div>

                                        <div className="flex flex-wrap gap-2 pt-1">
                                            {application.shift
                                                .event_show_url && (
                                                <Button
                                                    asChild
                                                    variant="outline"
                                                >
                                                    <Link
                                                        href={
                                                            application.shift
                                                                .event_show_url
                                                        }
                                                    >
                                                        Bekijk event
                                                    </Link>
                                                </Button>
                                            )}

                                            {application.can_cancel && (
                                                <Button
                                                    type="button"
                                                    disabled={
                                                        activeApplicationId ===
                                                        application.id
                                                    }
                                                    onClick={() => {
                                                        setActiveApplicationId(
                                                            application.id,
                                                        );
                                                        router.delete(
                                                            `/app/applications/${application.id}`,
                                                            {
                                                                preserveScroll: true,
                                                                onFinish: () =>
                                                                    setActiveApplicationId(
                                                                        null,
                                                                    ),
                                                            },
                                                        );
                                                    }}
                                                >
                                                    {activeApplicationId ===
                                                    application.id
                                                        ? 'Verwerken...'
                                                        : 'Annuleer aanvraag'}
                                                </Button>
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

CrewShiftsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Mijn shifts',
            href: '/app/my-shifts',
        },
    ],
};
