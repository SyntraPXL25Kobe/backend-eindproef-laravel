export type ApplicationStatus = 'pending' | 'approved' | 'rejected';

export type CrewApplication = {
    id: number;
    status: ApplicationStatus;
    motivation: string | null;
    created_at: string | null;
    reviewed_at: string | null;
    can_cancel: boolean;
    shift: {
        id: number | null;
        title: string | null;
        starts_at: string | null;
        ends_at: string | null;
        status: string | null;
        capacity: number | null;
        zone_name: string | null;
        event_title: string | null;
        event_location: string | null;
        event_show_url: string | null;
    };
};

export type ApplicationFilter = 'all' | ApplicationStatus;

export const statusLabel: Record<ApplicationStatus, string> = {
    pending: 'Pending',
    approved: 'Goedgekeurd',
    rejected: 'Afgewezen',
};
