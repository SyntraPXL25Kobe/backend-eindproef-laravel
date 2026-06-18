import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type CrewShift = {
    application_id: number;
    shift_id: number;
    title: string;
    zone_name: string;
    starts_at: string | null;
    ends_at: string | null;
};

type CrewMember = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    approved_shifts_count: number;
    shifts: CrewShift[];
};

type Props = {
    crewMembers: CrewMember[];
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

export default function CoordinatorEventCrewOverview({ crewMembers }: Props) {
    return (
        <section className="space-y-4">
            <Heading
                title="Crew members & shifts"
                description="Overzicht van crewleden met hun goedgekeurde shifts voor dit event."
            />

            {crewMembers.length === 0 ? (
                <Card className="border-dashed">
                    <CardContent className="py-8 text-sm text-muted-foreground">
                        Er zijn nog geen goedgekeurde crewleden voor dit event.
                    </CardContent>
                </Card>
            ) : (
                <div className="grid gap-4 md:grid-cols-2">
                    {crewMembers.map((crewMember) => (
                        <Card
                            key={crewMember.id}
                            className="h-full border-border/70 bg-card/95"
                        >
                            <CardHeader>
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <CardTitle className="text-base">
                                            {crewMember.name}
                                        </CardTitle>
                                        <CardDescription>
                                            {crewMember.email}
                                            {crewMember.phone
                                                ? ` · ${crewMember.phone}`
                                                : ''}
                                        </CardDescription>
                                    </div>
                                    <Badge>
                                        {crewMember.approved_shifts_count}{' '}
                                        shift(s)
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-2">
                                    {crewMember.shifts.map((shift) => (
                                        <div
                                            key={shift.application_id}
                                            className="rounded-lg border border-border/70 bg-muted/30 p-3 text-sm"
                                        >
                                            <div className="flex flex-wrap items-start justify-between gap-2">
                                                <p className="font-medium text-foreground">
                                                    {shift.title}
                                                </p>
                                                <span className="text-xs text-muted-foreground">
                                                    {shift.zone_name}
                                                </span>
                                            </div>
                                            <p className="text-muted-foreground">
                                                {formatDateTime(
                                                    shift.starts_at,
                                                )}{' '}
                                                -{' '}
                                                {formatDateTime(shift.ends_at)}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            )}
        </section>
    );
}
