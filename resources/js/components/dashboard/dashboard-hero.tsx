import { Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';

export function DashboardHero({ isCoordinator }: { isCoordinator: boolean }) {
    return (
        <div className="rounded-3xl border border-sidebar-border/70 bg-linear-to-br from-background via-card to-muted/40 p-6">
            <Heading
                title={
                    isCoordinator ? 'Coordinator dashboard' : 'Publieke events'
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
                        <Link href="/app/events">Ga naar mijn events</Link>
                    </Button>
                </div>
            )}
        </div>
    );
}
