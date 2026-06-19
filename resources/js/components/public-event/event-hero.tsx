import { CalendarDays, MapPin, Users } from 'lucide-react';
import type { PublicEvent } from '@/components/public-event/types';

export function EventHero({
    event,
    isInvitation,
}: {
    event: PublicEvent;
    isInvitation: boolean;
}) {
    return (
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
                            : 'Publiek evenement'}
                    </p>
                    <h1 className="max-w-3xl text-4xl font-semibold tracking-tight md:text-5xl">
                        {event.title}
                    </h1>
                    <p className="max-w-2xl text-base text-muted-foreground md:text-lg">
                        {event.description ||
                            'Dit evenement is gepubliceerd en klaar om met de crew gedeeld te worden.'}
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
                            Locatie van het evenement
                        </p>
                    </div>
                    <div className="rounded-2xl border border-border/70 bg-muted/60 p-4">
                        <Users className="mb-3 size-4 text-primary" />
                        <p>
                            {event.max_crew_members
                                ? `${event.max_crew_members} plaatsen`
                                : 'Flexibele bezetting'}
                        </p>
                        <p className="text-muted-foreground">Crew-capaciteit</p>
                    </div>
                </div>
            </div>
        </section>
    );
}
