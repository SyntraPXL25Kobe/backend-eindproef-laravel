import { Link } from '@inertiajs/react';
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
import type { PublicEvent } from '@/components/dashboard/types';

export function PublicEventCard({ event }: { event: PublicEvent }) {
    return (
        <Card className="overflow-hidden">
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
                    {event.location} · {event.start_date} tot {event.end_date}
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
                    <Link href={event.show_url}>Bekijk event</Link>
                </Button>
            </CardFooter>
        </Card>
    );
}
