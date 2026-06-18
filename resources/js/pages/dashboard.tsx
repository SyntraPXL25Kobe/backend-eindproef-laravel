import { Head, Link, usePage } from '@inertiajs/react';
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
import { dashboard } from '@/routes';
import type { Auth } from '@/types';

type PublicEvent = {
    id: number;
    title: string;
    description: string | null;
    location: string;
    start_date: string | null;
    end_date: string | null;
    max_crew_members: number | null;
    cover_image_url: string | null;
    coordinator_name: string | null;
    show_url: string;
};

type PageProps = {
    auth: Auth;
    publicEvents: PublicEvent[];
};

export default function Dashboard() {
    const { auth, publicEvents } = usePage<PageProps>().props;
    const isCoordinator = auth.isCoordinator;

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
                <div className="rounded-3xl border border-sidebar-border/70 bg-linear-to-br from-background via-card to-muted/40 p-6">
                    <Heading
                        title={
                            isCoordinator
                                ? 'Coordinator dashboard'
                                : 'Publieke events'
                        }
                        description={
                            isCoordinator
                                ? 'Beheer je eigen events en publicaties vanuit het events-overzicht.'
                                : 'Hier zie je alle events die publiek gepubliceerd zijn en openstaan om te verkennen.'
                        }
                    />

                    {isCoordinator && (
                        <div className="mt-4">
                            <Button asChild>
                                <Link href="/app/events">
                                    Ga naar mijn events
                                </Link>
                            </Button>
                        </div>
                    )}
                </div>

                {!isCoordinator && (
                    <div className="grid gap-4 xl:grid-cols-2">
                        {publicEvents.length === 0 ? (
                            <Card className="border-dashed">
                                <CardHeader>
                                    <CardTitle>
                                        Nog geen publieke events
                                    </CardTitle>
                                    <CardDescription>
                                        Zodra coordinators events publiek
                                        publiceren, verschijnen ze hier voor
                                        crew members.
                                    </CardDescription>
                                </CardHeader>
                            </Card>
                        ) : (
                            publicEvents.map((event) => (
                                <Card
                                    key={event.id}
                                    className="overflow-hidden"
                                >
                                    {event.cover_image_url && (
                                        <div className="h-44 overflow-hidden border-b border-border/60">
                                            <img
                                                src={event.cover_image_url}
                                                alt={event.title}
                                                className="size-full object-cover"
                                            />
                                        </div>
                                    )}
                                    <CardHeader className="gap-3">
                                        <div className="flex flex-wrap gap-2">
                                            <Badge>Publiek</Badge>
                                            {event.coordinator_name && (
                                                <Badge variant="outline">
                                                    {event.coordinator_name}
                                                </Badge>
                                            )}
                                        </div>
                                        <CardTitle>{event.title}</CardTitle>
                                        <CardDescription>
                                            {event.location} ·{' '}
                                            {event.start_date} tot{' '}
                                            {event.end_date}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-3 text-sm text-muted-foreground">
                                        <p>
                                            {event.description ||
                                                'Dit event is publiek zichtbaar voor crew members.'}
                                        </p>
                                        <p>
                                            {event.max_crew_members
                                                ? `${event.max_crew_members} crewplaatsen voorzien`
                                                : 'Flexibele crewcapaciteit'}
                                        </p>
                                    </CardContent>
                                    <CardFooter>
                                        <Button asChild variant="outline">
                                            <Link href={event.show_url}>
                                                Bekijk event
                                            </Link>
                                        </Button>
                                    </CardFooter>
                                </Card>
                            ))
                        )}
                    </div>
                )}
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
