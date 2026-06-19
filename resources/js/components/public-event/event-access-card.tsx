import { Link } from '@inertiajs/react';
import type { AuthUser, PublicEvent } from '@/components/public-event/types';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

export function EventAccessCard({
    user,
    event,
    isInvitation,
}: {
    user: AuthUser;
    event: PublicEvent;
    isInvitation: boolean;
}) {
    return (
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
                    Crew members kunnen zich hieronder voor een of meerdere
                    shifts inschrijven, ook als die shifts in verschillende
                    zones vallen.
                </p>

                {!user && (
                    <>
                        <Button asChild className="w-full">
                            <Link href="/register">Maak een account aan</Link>
                        </Button>
                        <Button asChild variant="outline" className="w-full">
                            <Link href="/login">Ik heb al een account</Link>
                        </Button>
                    </>
                )}

                {user && (
                    <div className="rounded-2xl border border-border/70 bg-muted/50 p-4 text-foreground">
                        Je bent ingelogd. Kies hieronder de shifts waarvoor je
                        wil applien.
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
