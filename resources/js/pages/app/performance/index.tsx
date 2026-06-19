import { Head, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type PerformanceEvent = {
    id: number;
    title: string;
    location: string | null;
    start_date: string | null;
    end_date: string | null;
    shifts_total: number;
    check_ins: number;
    no_shows: number;
    last_check_in_at: string | null;
    last_check_out_at: string | null;
    shifts: {
        id: number;
        title: string | null;
        zone_name: string | null;
        starts_at: string | null;
        ends_at: string | null;
        check_in_at: string | null;
        check_out_at: string | null;
        no_show: boolean;
    }[];
};

type PageProps = {
    stats: {
        events_total: number;
        events_checked_in: number;
        total_no_shows: number;
        total_check_ins: number;
    };
    events: PerformanceEvent[];
};

function formatDate(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString('nl-BE');
}

function formatDateTime(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString('nl-BE');
}

export default function CrewPerformanceIndex() {
    const { stats, events } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Mijn prestaties" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="mx-auto w-full max-w-6xl space-y-6">
                    <Heading
                        title="Mijn prestaties"
                        description="Overzicht van je check-ins en no-shows per event."
                    />

                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm">
                                    Events
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-3xl font-semibold">
                                {stats.events_total}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm">
                                    Events ingecheckt
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-3xl font-semibold text-emerald-600">
                                {stats.events_checked_in}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm">
                                    Totaal check-ins
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-3xl font-semibold text-sky-600">
                                {stats.total_check_ins}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm">
                                    Totaal no-shows
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-3xl font-semibold text-rose-600">
                                {stats.total_no_shows}
                            </CardContent>
                        </Card>
                    </div>

                    {events.length === 0 ? (
                        <Card className="border-dashed">
                            <CardHeader>
                                <CardTitle>
                                    Nog geen prestatiegegevens
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                Je hebt nog geen toegewezen shifts met
                                check-in/no-show data.
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="space-y-3">
                            {events.map((event) => (
                                <Card key={event.id}>
                                    <CardHeader className="space-y-2">
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <CardTitle className="text-base">
                                                {event.title}
                                            </CardTitle>
                                            <Badge variant="outline">
                                                {event.shifts_total} shift
                                                {event.shifts_total === 1
                                                    ? ''
                                                    : 's'}
                                            </Badge>
                                        </div>
                                        <p className="text-sm text-muted-foreground">
                                            {event.location ||
                                                'Onbekende locatie'}{' '}
                                            · {formatDate(event.start_date)} -{' '}
                                            {formatDate(event.end_date)}
                                        </p>
                                    </CardHeader>
                                    <CardContent className="grid gap-3 text-sm md:grid-cols-2 xl:grid-cols-4">
                                        <div>
                                            <p className="text-muted-foreground">
                                                Check-ins
                                            </p>
                                            <p className="font-medium">
                                                {event.check_ins}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-muted-foreground">
                                                No-shows
                                            </p>
                                            <p className="font-medium">
                                                {event.no_shows}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-muted-foreground">
                                                Laatste check-in
                                            </p>
                                            <p className="font-medium">
                                                {formatDateTime(
                                                    event.last_check_in_at,
                                                )}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-muted-foreground">
                                                Laatste check-out
                                            </p>
                                            <p className="font-medium">
                                                {formatDateTime(
                                                    event.last_check_out_at,
                                                )}
                                            </p>
                                        </div>
                                    </CardContent>

                                    <CardContent className="pt-0">
                                        <p className="mb-2 text-sm font-medium">
                                            Gedraaide shiften
                                        </p>

                                        <div className="space-y-2">
                                            {event.shifts.map((shift) => (
                                                <div
                                                    key={shift.id}
                                                    className="rounded-lg border border-border/70 bg-muted/20 p-3 text-sm"
                                                >
                                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                                        <p className="font-medium">
                                                            {shift.title ||
                                                                'Onbekende shift'}
                                                        </p>
                                                        <Badge
                                                            variant={
                                                                shift.no_show
                                                                    ? 'destructive'
                                                                    : 'outline'
                                                            }
                                                        >
                                                            {shift.no_show
                                                                ? 'No-show'
                                                                : shift.check_out_at
                                                                  ? 'Afgerond'
                                                                  : shift.check_in_at
                                                                    ? 'Ingecheckt'
                                                                    : 'Gepland'}
                                                        </Badge>
                                                    </div>

                                                    <p className="text-muted-foreground">
                                                        {shift.zone_name ||
                                                            'Onbekende zone'}{' '}
                                                        ·{' '}
                                                        {formatDateTime(
                                                            shift.starts_at,
                                                        )}{' '}
                                                        -{' '}
                                                        {formatDateTime(
                                                            shift.ends_at,
                                                        )}
                                                    </p>
                                                </div>
                                            ))}
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

CrewPerformanceIndex.layout = {
    breadcrumbs: [
        {
            title: 'Mijn prestaties',
            href: '/app/my-performance',
        },
    ],
};
