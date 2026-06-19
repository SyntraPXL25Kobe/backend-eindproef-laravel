import type { EventDashboardStats } from '@/components/event-dashboard/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export function EventDashboardStatsCards({
    stats,
}: {
    stats: EventDashboardStats;
}) {
    return (
        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <Card>
                <CardHeader>
                    <CardTitle className="text-sm">Toegewezen crew</CardTitle>
                </CardHeader>
                <CardContent className="text-3xl font-semibold">
                    {stats.total_assigned}
                </CardContent>
            </Card>
            <Card>
                <CardHeader>
                    <CardTitle className="text-sm">Ingecheckt</CardTitle>
                </CardHeader>
                <CardContent className="text-3xl font-semibold text-emerald-600">
                    {stats.checked_in}
                </CardContent>
            </Card>
            <Card>
                <CardHeader>
                    <CardTitle className="text-sm">
                        Wachten op check-in
                    </CardTitle>
                </CardHeader>
                <CardContent className="text-3xl font-semibold text-amber-600">
                    {stats.pending}
                </CardContent>
            </Card>
            <Card>
                <CardHeader>
                    <CardTitle className="text-sm">No-shows</CardTitle>
                </CardHeader>
                <CardContent className="text-3xl font-semibold text-rose-600">
                    {stats.no_shows}
                    <p className="mt-2 text-sm font-normal text-muted-foreground">
                        Check-in rate: {stats.check_in_rate}%
                    </p>
                </CardContent>
            </Card>
        </div>
    );
}
