import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import DashboardController from '@/actions/App/Http/Controllers/DashboardController';
import { DashboardHero } from '@/components/dashboard/dashboard-hero';
import { PublicEventCard } from '@/components/dashboard/public-event-card';
import { PublicEventsSearch } from '@/components/dashboard/public-events-search';
import type { PublicEvent } from '@/components/dashboard/types';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
    publicEvents: PublicEvent[];
    filters: {
        search: string;
    };
};

export default function Dashboard() {
    const { auth, publicEvents, filters } = usePage<PageProps>().props;
    const isCoordinator = auth.isCoordinator;
    const [search, setSearch] = useState(filters.search ?? '');

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
                <DashboardHero isCoordinator={isCoordinator} />

                {!isCoordinator && (
                    <div className="space-y-4">
                        <PublicEventsSearch
                            search={search}
                            hasActiveSearch={Boolean(filters.search)}
                            onSearchChange={setSearch}
                            onReset={() => {
                                setSearch('');
                                router.get(
                                    DashboardController.index(),
                                    {},
                                    {
                                        preserveState: true,
                                        preserveScroll: true,
                                        replace: true,
                                    },
                                );
                            }}
                        />

                        <div className="grid gap-4 xl:grid-cols-2">
                            {publicEvents.length === 0 ? (
                                <Card className="border-dashed">
                                    <CardHeader>
                                        <CardTitle>
                                            Geen evenementen gevonden
                                        </CardTitle>
                                        <CardDescription>
                                            {filters.search
                                                ? 'Geen resultaten voor je zoekopdracht.'
                                                : 'Zodra coordinators evenementen publiek publiceren, verschijnen ze hier voor crewleden.'}
                                        </CardDescription>
                                    </CardHeader>
                                </Card>
                            ) : (
                                publicEvents.map((event) => (
                                    <PublicEventCard
                                        key={event.id}
                                        event={event}
                                    />
                                ))
                            )}
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
