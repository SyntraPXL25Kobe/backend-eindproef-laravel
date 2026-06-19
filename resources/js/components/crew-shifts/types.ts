export type ApplicationStatus = 'pending' | 'approved' | 'rejected';

export type CrewApplication = {
    assignment_id: number | null;
    id: number;
    status: ApplicationStatus;
    motivation: string | null;
    created_at: string | null;
    reviewed_at: string | null;
    can_cancel: boolean;
    check_in: {
        is_available_today: boolean;
        checked_in_at: string | null;
        no_show: boolean;
        no_show_reason: string | null;
        qr_svg_src: string | null;
    } | null;
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
    pending: 'In behandeling',
    approved: 'Goedgekeurd',
    rejected: 'Afgewezen',
};
