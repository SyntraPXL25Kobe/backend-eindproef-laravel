import { useMemo, useState } from 'react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { formatDateTimeNl } from '@/lib/format-date-time';

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

export default function CoordinatorEventCrewOverview({ crewMembers }: Props) {
    const [search, setSearch] = useState('');

    const filtered = useMemo(() => {
        const q = search.trim().toLowerCase();

        if (!q) {
            return crewMembers;
        }

        return crewMembers.filter(
            (crewMember) =>
                crewMember.name.toLowerCase().includes(q) ||
                crewMember.email.toLowerCase().includes(q) ||
                crewMember.shifts.some(
                    (shift) =>
                        shift.title.toLowerCase().includes(q) ||
                        shift.zone_name.toLowerCase().includes(q),
                ),
        );
    }, [crewMembers, search]);

    return (
        <section className="space-y-4">
            <Heading
                title="Crew en shiften"
                description="Overzicht van crewleden met hun goedgekeurde shiften voor dit evenement."
            />

            <Input
                placeholder="Zoek op naam, e-mail, shift of zone…"
                value={search}
                onChange={(e) => setSearch(e.currentTarget.value)}
                className="max-w-sm"
            />

            {crewMembers.length === 0 ? (
                <Card className="border-dashed">
                    <CardContent className="py-8 text-sm text-muted-foreground">
                        Er zijn nog geen goedgekeurde crewleden voor dit
                        evenement.
                    </CardContent>
                </Card>
            ) : filtered.length === 0 ? (
                <Card className="border-dashed">
                    <CardContent className="py-8 text-sm text-muted-foreground">
                        Geen resultaten voor &ldquo;{search}&rdquo;.
                    </CardContent>
                </Card>
            ) : (
                <div className="grid gap-4 md:grid-cols-2">
                    {filtered.map((crewMember) => (
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
                                        shift(en)
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
                                                {formatDateTimeNl(
                                                    shift.starts_at,
                                                )}{' '}
                                                -{' '}
                                                {formatDateTimeNl(
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
        </section>
    );
}
