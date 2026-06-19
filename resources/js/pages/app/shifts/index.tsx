import { Head, Link, router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { CrewShiftApplicationCard } from '@/components/crew-shifts/application-card';
import { ApplicationStatusFilters } from '@/components/crew-shifts/application-status-filters';
import { CheckInQrDialog } from '@/components/crew-shifts/check-in-qr-dialog';
import { statusLabel } from '@/components/crew-shifts/types';
import type {
    ApplicationFilter,
    CrewApplication,
} from '@/components/crew-shifts/types';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';

type PageProps = {
    applications: CrewApplication[];
};

export default function CrewShiftsIndex() {
    const { applications } = usePage<PageProps>().props;
    const [filter, setFilter] = useState<ApplicationFilter>('all');
    const [searchQuery, setSearchQuery] = useState('');
    const [activeApplicationId, setActiveApplicationId] = useState<
        number | null
    >(null);
    const [qrApplication, setQrApplication] = useState<CrewApplication | null>(
        null,
    );

    const filteredApplications = useMemo(() => {
        const normalizedQuery = searchQuery.trim().toLowerCase();

        return applications.filter((application) => {
            const matchesStatus =
                filter === 'all' || application.status === filter;

            if (!matchesStatus) {
                return false;
            }

            if (!normalizedQuery) {
                return true;
            }

            const searchable = [
                application.shift.title,
                application.shift.event_title,
                application.shift.zone_name,
                application.shift.event_location,
                application.motivation,
                statusLabel[application.status],
            ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            return searchable.includes(normalizedQuery);
        });
    }, [applications, filter, searchQuery]);

    const countByStatus = useMemo(
        () => ({
            pending: applications.filter((x) => x.status === 'pending').length,
            approved: applications.filter((x) => x.status === 'approved')
                .length,
            rejected: applications.filter((x) => x.status === 'rejected')
                .length,
        }),
        [applications],
    );

    return (
        <>
            <Head title="Mijn shifts" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="mx-auto w-full max-w-6xl space-y-5">
                    <Heading
                        title="Mijn shifts"
                        description="Overzicht van je aanvragen en shifts, chronologisch gesorteerd."
                    />

                    <Input
                        value={searchQuery}
                        onChange={(event) =>
                            setSearchQuery(event.currentTarget.value)
                        }
                        placeholder="Zoek op shift, event, zone, locatie, motivatie of status"
                        className="max-w-xl"
                    />

                    <ApplicationStatusFilters
                        filter={filter}
                        total={applications.length}
                        countByStatus={countByStatus}
                        onFilterChange={setFilter}
                    />

                    {filteredApplications.length === 0 ? (
                        <Card className="border-dashed">
                            <CardHeader>
                                <CardTitle>Geen shifts gevonden</CardTitle>
                                <CardDescription>
                                    Pas je filter of zoekterm aan, of bekijk
                                    publieke events om te applyen.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Button asChild variant="outline">
                                    <Link href="/app">Naar dashboard</Link>
                                </Button>
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="grid gap-4 md:grid-cols-2">
                            {filteredApplications.map((application) => (
                                <CrewShiftApplicationCard
                                    key={application.id}
                                    application={application}
                                    activeApplicationId={activeApplicationId}
                                    onShowQr={setQrApplication}
                                    onCancel={(applicationId) => {
                                        setActiveApplicationId(applicationId);
                                        router.delete(
                                            `/app/applications/${applicationId}`,
                                            {
                                                preserveScroll: true,
                                                onFinish: () =>
                                                    setActiveApplicationId(
                                                        null,
                                                    ),
                                            },
                                        );
                                    }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>

            <CheckInQrDialog
                application={qrApplication}
                open={qrApplication !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setQrApplication(null);
                    }
                }}
            />
        </>
    );
}

CrewShiftsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Mijn shifts',
            href: '/app/my-shifts',
        },
    ],
};
