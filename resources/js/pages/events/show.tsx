import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { ApplyShiftDialog } from '@/components/public-event/apply-shift-dialog';
import { EventAccessCard } from '@/components/public-event/event-access-card';
import { EventHero } from '@/components/public-event/event-hero';
import type { PublicEvent, Shift } from '@/components/public-event/types';
import { ZoneCard } from '@/components/public-event/zone-card';

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
                        <EventHero event={event} isInvitation={isInvitation} />

                        <EventAccessCard
                            user={auth.user}
                            event={event}
                            isInvitation={isInvitation}
                        />
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
                                <ZoneCard
                                    key={zone.id}
                                    zone={zone}
                                    user={auth.user}
                                    activeShiftId={activeShiftId}
                                    onOpenApply={openApplyDialog}
                                    onCancelApplication={cancelApplication}
                                />
                            ))}
                        </div>
                    </section>
                </div>
            </div>

            <ApplyShiftDialog
                applyShift={applyShift}
                motivation={motivation}
                motivationError={motivationError}
                activeShiftId={activeShiftId}
                onOpenChange={(open) => {
                    if (!open) {
                        closeApplyDialog();
                    }
                }}
                onMotivationChange={setMotivation}
                onSubmit={applyForShift}
                onCancel={closeApplyDialog}
            />
        </>
    );
}

ShowPublicEvent.layout = null;
