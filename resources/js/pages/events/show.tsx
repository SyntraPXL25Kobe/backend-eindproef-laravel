import { Head, Link } from '@inertiajs/react';
import { CalendarDays, MapPin, Users } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

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
};

export default function ShowPublicEvent({
    event,
    isInvitation,
}: {
    event: PublicEvent;
    isInvitation: boolean;
}) {
    return (
        <>
            <Head title={event.title} />

            <div className="min-h-screen bg-[linear-gradient(135deg,var(--color-background)_0%,var(--color-card)_55%,var(--color-muted)_100%)] px-4 py-10 text-foreground md:px-8">
                <div className="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[1.4fr_0.8fr]">
                    <section className="overflow-hidden rounded-[2rem] border border-border/70 bg-card/90 shadow-2xl backdrop-blur-sm">
                        {event.cover_image_url && (
                            <div className="h-72 overflow-hidden">
                                <img
                                    src={event.cover_image_url}
                                    alt={event.title}
                                    className="size-full object-cover"
                                />
                            </div>
                        )}
                        <div className="space-y-6 p-8">
                            <div className="space-y-3">
                                <p className="text-sm tracking-[0.35em] text-primary/80 uppercase">
                                    {isInvitation
                                        ? 'Crew-uitnodiging'
                                        : 'Publiek event'}
                                </p>
                                <h1 className="max-w-3xl text-4xl font-semibold tracking-tight md:text-5xl">
                                    {event.title}
                                </h1>
                                <p className="max-w-2xl text-base text-muted-foreground md:text-lg">
                                    {event.description ||
                                        'Dit event is gepubliceerd en klaar om met crew members gedeeld te worden.'}
                                </p>
                            </div>

                            <div className="grid gap-3 text-sm text-foreground/90 md:grid-cols-3">
                                <div className="rounded-2xl border border-border/70 bg-muted/60 p-4">
                                    <CalendarDays className="mb-3 size-4 text-primary" />
                                    <p>{event.start_date}</p>
                                    <p className="text-muted-foreground">
                                        tot {event.end_date}
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-muted/60 p-4">
                                    <MapPin className="mb-3 size-4 text-primary" />
                                    <p>{event.location}</p>
                                    <p className="text-muted-foreground">
                                        Locatie van het event
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-muted/60 p-4">
                                    <Users className="mb-3 size-4 text-primary" />
                                    <p>
                                        {event.max_crew_members
                                            ? `${event.max_crew_members} plaatsen`
                                            : 'Flexibele bezetting'}
                                    </p>
                                    <p className="text-muted-foreground">
                                        Crew-capaciteit
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <Card className="border-border/70 bg-card/95 text-card-foreground shadow-2xl backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle>
                                {isInvitation
                                    ? 'Je bent uitgenodigd voor dit event'
                                    : 'Dit event is publiek zichtbaar'}
                            </CardTitle>
                            <CardDescription className="text-muted-foreground">
                                Georganiseerd door{' '}
                                {event.coordinator_name || 'de coordinator'}.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 text-sm text-muted-foreground">
                            <p>
                                Gebruik deze pagina om crew members context te
                                geven voordat shifts en inschrijvingen gedeeld
                                worden.
                            </p>
                            <Button asChild className="w-full">
                                <Link href="/register">
                                    Maak een account aan
                                </Link>
                            </Button>
                            <Button
                                asChild
                                variant="outline"
                                className="w-full"
                            >
                                <Link href="/login">Ik heb al een account</Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

ShowPublicEvent.layout = null;
