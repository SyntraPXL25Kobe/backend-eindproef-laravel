import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
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

type EventListItem = {
    id: number;
    title: string;
    location: string;
    start_date: string | null;
    end_date: string | null;
    status: string;
    publication_visibility: string;
    published_at: string | null;
    edit_url: string;
    dashboard_url: string;
    public_url: string | null;
    invite_url: string | null;
};

const statusLabels: Record<string, string> = {
    draft: 'Concept',
    published: 'Gepubliceerd',
    archived: 'Gearchiveerd',
};

const visibilityLabels: Record<string, string> = {
    public: 'Publiek',
    invite_only: 'Invite-only',
};

export default function CoordinatorEventsIndex({
    events,
}: {
    events: EventListItem[];
}) {
    return (
        <>
            <Head title="Mijn events" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 rounded-3xl border border-sidebar-border/70 bg-linear-to-br from-background via-background to-muted/40 p-6">
                    <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <Heading
                            title="Mijn events"
                            description="Maak events aan als concept en publiceer ze publiek of via een unieke crew-link."
                        />
                        <Button asChild>
                            <Link href="/app/events/create">Nieuw event</Link>
                        </Button>
                    </div>
                </div>

                <div className="grid gap-4 xl:grid-cols-2">
                    {events.length === 0 ? (
                        <Card className="border-dashed">
                            <CardHeader>
                                <CardTitle>Nog geen events</CardTitle>
                                <CardDescription>
                                    Start met een concept en beslis later of je
                                    publiek wil publiceren of alleen met crew
                                    members wil delen.
                                </CardDescription>
                            </CardHeader>
                            <CardFooter>
                                <Button asChild>
                                    <Link href="/app/events/create">
                                        Eerste event aanmaken
                                    </Link>
                                </Button>
                            </CardFooter>
                        </Card>
                    ) : (
                        events.map((event) => (
                            <Card key={event.id}>
                                <CardHeader className="gap-3">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <Badge>
                                            {statusLabels[event.status] ??
                                                event.status}
                                        </Badge>
                                        <Badge variant="outline">
                                            {visibilityLabels[
                                                event.publication_visibility
                                            ] ?? event.publication_visibility}
                                        </Badge>
                                    </div>
                                    <CardTitle>{event.title}</CardTitle>
                                    <CardDescription>
                                        {event.location} · {event.start_date}{' '}
                                        tot {event.end_date}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-3 text-sm text-muted-foreground">
                                    {event.public_url && (
                                        <p>
                                            Publieke pagina: {event.public_url}
                                        </p>
                                    )}
                                    {event.invite_url && (
                                        <p>
                                            Crew-uitnodiging: {event.invite_url}
                                        </p>
                                    )}
                                    {!event.public_url && !event.invite_url && (
                                        <p>
                                            Dit event staat nog als concept en
                                            is nergens zichtbaar.
                                        </p>
                                    )}
                                </CardContent>
                                <CardFooter className="flex flex-wrap gap-2">
                                    <Button asChild variant="outline">
                                        <Link href={event.edit_url}>
                                            Beheren
                                        </Link>
                                    </Button>
                                    <Button asChild variant="secondary">
                                        <Link href={event.dashboard_url}>
                                            Live dashboard
                                        </Link>
                                    </Button>
                                </CardFooter>
                            </Card>
                        ))
                    )}
                </div>
            </div>
        </>
    );
}

CoordinatorEventsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Mijn events',
            href: '/app/events',
        },
    ],
};
