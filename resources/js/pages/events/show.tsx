import { Head, Link, router, usePage } from '@inertiajs/react';
import { CalendarDays, MapPin, Users } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

type ShiftApplication = {
    id: number;
    status: string;
};

type Shift = {
    id: number;
    title: string;
    description: string | null;
    starts_at: string | null;
    ends_at: string | null;
    capacity: number;
    status: string;
    required_skill_name: string | null;
    application: ShiftApplication | null;
    can_apply: boolean;
    cannot_apply_reason:
        | 'shift_closed'
        | 'already_applied'
        | 'rejected'
        | 'overlap'
        | null;
    can_cancel: boolean;
};

type Zone = {
    id: number;
    name: string;
    description: string | null;
    shifts: Shift[];
};

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
    zones: Zone[];
};

type PageProps = {
    auth: {
        user: { id: number } | null;
        isCoordinator: boolean;
    };
};

export default function ShowPublicEvent({
    event,
    isInvitation,
}: {
    event: PublicEvent;
    isInvitation: boolean;
}) {
    const { auth } = usePage<PageProps>().props;
    const [activeShiftId, setActiveShiftId] = useState<number | null>(null);
    const [applyShift, setApplyShift] = useState<Shift | null>(null);
    const [motivation, setMotivation] = useState('');
    const [motivationError, setMotivationError] = useState<string | null>(null);

    const formatDateTime = (value: string | null) => {
        if (!value) {
            return 'Tijdstip volgt nog';
        }

        return new Intl.DateTimeFormat('nl-BE', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(new Date(value));
    };

    const openApplyDialog = (shift: Shift) => {
        setApplyShift(shift);
        setMotivation('');
        setMotivationError(null);
    };

    const closeApplyDialog = () => {
        setApplyShift(null);
        setMotivation('');
        setMotivationError(null);
    };

    const applyForShift = () => {
        if (!applyShift) {
            return;
        }

        setActiveShiftId(applyShift.id);
        setMotivationError(null);

        router.post(
            `/app/shifts/${applyShift.id}/applications`,
            {
                motivation: motivation.trim() || null,
            },
            {
                preserveScroll: true,
                onError: (errors) => {
                    setMotivationError(
                        typeof errors.motivation === 'string'
                            ? errors.motivation
                            : null,
                    );
                },
                onSuccess: () => closeApplyDialog(),
                onFinish: () => setActiveShiftId(null),
            },
        );
    };

    const cancelApplication = (applicationId: number, shiftId: number) => {
        setActiveShiftId(shiftId);
        router.delete(`/app/applications/${applicationId}`, {
            preserveScroll: true,
            onFinish: () => setActiveShiftId(null),
        });
    };

    return (
        <>
            <Head title={event.title} />

            <div className="min-h-screen bg-[linear-gradient(135deg,var(--color-background)_0%,var(--color-card)_55%,var(--color-muted)_100%)] px-4 py-10 text-foreground md:px-8">
                <div className="mx-auto flex max-w-6xl flex-col gap-6">
                    <div className="grid gap-6 lg:grid-cols-[1.4fr_0.8fr]">
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
                                    {event.coordinator_name || 'de coordinator'}
                                    .
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4 text-sm text-muted-foreground">
                                <p>
                                    Crew members kunnen zich hieronder voor een
                                    of meerdere shifts inschrijven, ook als die
                                    shifts in verschillende zones vallen.
                                </p>

                                {!auth.user && (
                                    <>
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
                                            <Link href="/login">
                                                Ik heb al een account
                                            </Link>
                                        </Button>
                                    </>
                                )}

                                {auth.user && (
                                    <div className="rounded-2xl border border-border/70 bg-muted/50 p-4 text-foreground">
                                        Je bent ingelogd. Kies hieronder de
                                        shifts waarvoor je wil applien.
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <section className="space-y-4">
                        <div className="space-y-2">
                            <h2 className="text-2xl font-semibold tracking-tight">
                                Beschikbare zones en shifts
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                Elke shift is apart aan te vragen. Een crew
                                member kan dus meerdere shifts kiezen, ook over
                                verschillende zones heen.
                            </p>
                        </div>

                        <div className="grid gap-4 xl:grid-cols-2">
                            {event.zones.map((zone) => (
                                <Card
                                    key={zone.id}
                                    className="border-border/70 bg-card/95"
                                >
                                    <CardHeader>
                                        <CardTitle>{zone.name}</CardTitle>
                                        <CardDescription>
                                            {zone.description ||
                                                'Geen extra zonebeschrijving beschikbaar.'}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        {zone.shifts.length === 0 ? (
                                            <div className="rounded-2xl border border-dashed border-border/70 bg-muted/40 p-4 text-sm text-muted-foreground">
                                                Voor deze zone zijn nog geen
                                                shifts gepubliceerd.
                                            </div>
                                        ) : (
                                            zone.shifts.map((shift) => (
                                                <div
                                                    key={shift.id}
                                                    className="rounded-2xl border border-border/70 bg-muted/30 p-4"
                                                >
                                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                                        <div className="space-y-2">
                                                            <div className="flex flex-wrap gap-2">
                                                                <Badge variant="outline">
                                                                    {
                                                                        shift.status
                                                                    }
                                                                </Badge>
                                                                {shift.application && (
                                                                    <Badge>
                                                                        {
                                                                            shift
                                                                                .application
                                                                                .status
                                                                        }
                                                                    </Badge>
                                                                )}
                                                                {shift.required_skill_name && (
                                                                    <Badge variant="secondary">
                                                                        Skill:{' '}
                                                                        {
                                                                            shift.required_skill_name
                                                                        }
                                                                    </Badge>
                                                                )}
                                                            </div>
                                                            <h3 className="text-lg font-medium text-foreground">
                                                                {shift.title}
                                                            </h3>
                                                            <p className="text-sm text-muted-foreground">
                                                                {shift.description ||
                                                                    'Geen extra shiftbeschrijving beschikbaar.'}
                                                            </p>
                                                        </div>
                                                        <div className="text-right text-sm text-muted-foreground">
                                                            <p>
                                                                {formatDateTime(
                                                                    shift.starts_at,
                                                                )}
                                                            </p>
                                                            <p>
                                                                tot{' '}
                                                                {formatDateTime(
                                                                    shift.ends_at,
                                                                )}
                                                            </p>
                                                            <p>
                                                                {shift.capacity}{' '}
                                                                plaatsen
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <CardFooter className="mt-4 flex items-center gap-3 px-0 pb-0">
                                                        {!auth.user && (
                                                            <Button
                                                                asChild
                                                                variant="outline"
                                                            >
                                                                <Link href="/login">
                                                                    Log in om te
                                                                    applien
                                                                </Link>
                                                            </Button>
                                                        )}

                                                        {auth.user &&
                                                            shift.can_apply && (
                                                                <Button
                                                                    type="button"
                                                                    disabled={
                                                                        activeShiftId ===
                                                                        shift.id
                                                                    }
                                                                    onClick={() =>
                                                                        openApplyDialog(
                                                                            shift,
                                                                        )
                                                                    }
                                                                >
                                                                    {activeShiftId ===
                                                                    shift.id
                                                                        ? 'Verwerken...'
                                                                        : 'Apply voor deze shift'}
                                                                </Button>
                                                            )}

                                                        {auth.user &&
                                                            shift.application &&
                                                            shift.can_cancel && (
                                                                <Button
                                                                    type="button"
                                                                    variant="outline"
                                                                    disabled={
                                                                        activeShiftId ===
                                                                        shift.id
                                                                    }
                                                                    onClick={() =>
                                                                        cancelApplication(
                                                                            shift
                                                                                .application!
                                                                                .id,
                                                                            shift.id,
                                                                        )
                                                                    }
                                                                >
                                                                    {activeShiftId ===
                                                                    shift.id
                                                                        ? 'Verwerken...'
                                                                        : 'Annuleer applicatie'}
                                                                </Button>
                                                            )}

                                                        {auth.user &&
                                                            !shift.can_apply &&
                                                            !shift.can_cancel &&
                                                            (() => {
                                                                const reason =
                                                                    shift.cannot_apply_reason;
                                                                const messages: Record<
                                                                    string,
                                                                    string
                                                                > = {
                                                                    overlap:
                                                                        'Je hebt al een aanvraag voor een shift met overlappende uren.',
                                                                    rejected:
                                                                        'Je aanvraag voor deze shift werd afgewezen. Opnieuw applyen is niet mogelijk.',
                                                                    already_applied: `Je status voor deze shift is ${shift.application?.status ?? 'bekend'}.`,
                                                                    shift_closed:
                                                                        'Deze shift staat momenteel niet open voor aanvragen.',
                                                                };
                                                                const text =
                                                                    reason
                                                                        ? messages[
                                                                              reason
                                                                          ]
                                                                        : null;

                                                                return text ? (
                                                                    <span className="text-sm text-muted-foreground">
                                                                        {text}
                                                                    </span>
                                                                ) : null;
                                                            })()}
                                                    </CardFooter>
                                                </div>
                                            ))
                                        )}
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </section>
                </div>
            </div>

            <Dialog
                open={Boolean(applyShift)}
                onOpenChange={(open) => {
                    if (!open) {
                        closeApplyDialog();
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Apply voor deze shift</DialogTitle>
                        <DialogDescription>
                            {applyShift
                                ? `Voeg optioneel een motivatie toe voor ${applyShift.title}.`
                                : 'Voeg optioneel een motivatie toe.'}
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-2">
                        <Label htmlFor="motivation">
                            Motivatie (optioneel)
                        </Label>
                        <textarea
                            id="motivation"
                            value={motivation}
                            onChange={(event) =>
                                setMotivation(event.currentTarget.value)
                            }
                            placeholder="Waarom pas jij goed bij deze shift?"
                            className="min-h-28 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                        />
                        <InputError message={motivationError ?? undefined} />
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={closeApplyDialog}
                            disabled={
                                !!applyShift && activeShiftId === applyShift.id
                            }
                        >
                            Annuleren
                        </Button>
                        <Button
                            type="button"
                            onClick={applyForShift}
                            disabled={
                                !applyShift || activeShiftId === applyShift.id
                            }
                        >
                            {applyShift && activeShiftId === applyShift.id
                                ? 'Verwerken...'
                                : 'Verstuur aanvraag'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

ShowPublicEvent.layout = null;
