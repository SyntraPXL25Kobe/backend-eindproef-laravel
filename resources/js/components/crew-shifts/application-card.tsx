import { Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatDateTimeNl } from '@/lib/format-date-time';
import type { CrewApplication } from '@/components/crew-shifts/types';
import { statusLabel } from '@/components/crew-shifts/types';

export function CrewShiftApplicationCard({
    application,
    activeApplicationId,
    onCancel,
}: {
    application: CrewApplication;
    activeApplicationId: number | null;
    onCancel: (applicationId: number) => void;
}) {
    return (
        <Card className="h-full border-border/70 bg-card/95">
            <CardHeader>
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <CardTitle className="text-base">
                            {application.shift.title || 'Onbekende shift'}
                        </CardTitle>
                        <CardDescription>
                            {application.shift.event_title || 'Onbekend event'}
                            {application.shift.event_location
                                ? ` · ${application.shift.event_location}`
                                : ''}
                        </CardDescription>
                    </div>
                    <Badge>{statusLabel[application.status]}</Badge>
                </div>
            </CardHeader>
            <CardContent className="space-y-3 text-sm">
                <div className="space-y-1 text-muted-foreground">
                    <p>
                        <span className="font-medium text-foreground">
                            Zone:
                        </span>{' '}
                        {application.shift.zone_name || 'Onbekend'}
                    </p>
                    <p>
                        <span className="font-medium text-foreground">
                            Wanneer:
                        </span>{' '}
                        {formatDateTimeNl(
                            application.shift.starts_at,
                            'Nog niet ingepland',
                        )}{' '}
                        -{' '}
                        {formatDateTimeNl(
                            application.shift.ends_at,
                            'Nog niet ingepland',
                        )}
                    </p>
                    <p>
                        <span className="font-medium text-foreground">
                            Aangevraagd op:
                        </span>{' '}
                        {formatDateTimeNl(
                            application.created_at,
                            'Nog niet ingepland',
                        )}
                    </p>
                </div>

                <div className="rounded-lg border border-border/70 bg-muted/30 p-3 text-muted-foreground">
                    <p className="mb-1 font-medium text-foreground">
                        Motivatie
                    </p>
                    <p>
                        {application.motivation || 'Geen motivatie ingevuld.'}
                    </p>
                </div>

                <div className="flex flex-wrap gap-2 pt-1">
                    {application.shift.event_show_url && (
                        <Button asChild variant="outline">
                            <Link href={application.shift.event_show_url}>
                                Bekijk event
                            </Link>
                        </Button>
                    )}

                    {application.can_cancel && (
                        <Button
                            type="button"
                            disabled={activeApplicationId === application.id}
                            onClick={() => onCancel(application.id)}
                        >
                            {activeApplicationId === application.id
                                ? 'Verwerken...'
                                : 'Annuleer aanvraag'}
                        </Button>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
