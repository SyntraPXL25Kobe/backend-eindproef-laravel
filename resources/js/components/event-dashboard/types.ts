export type EventDashboardEvent = {
    id: number;
    title: string;
    location: string;
    start_date: string | null;
    end_date: string | null;
    is_live_today: boolean;
};

export type EventDashboardStats = {
    total_assigned: number;
    checked_in: number;
    pending: number;
    no_shows: number;
    check_in_rate: number;
};

export type EventDashboardAssignment = {
    id: number;
    application_id: number;
    confirmed_at: string | null;
    check_in_at: string | null;
    check_out_at: string | null;
    no_show: boolean;
    no_show_reason: string | null;
    can_check_in: boolean;
    can_mark_no_show: boolean;
    user: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
    };
    shift: {
        id: number;
        title: string;
        starts_at: string | null;
        ends_at: string | null;
        zone_name: string;
    };
};

export type EventDashboardCrewMember = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    assignments: EventDashboardAssignment[];
};

export type EventDashboardScanFeedback = {
    status: 'success' | 'error';
    message: string;
    assignment?: {
        id: number;
        check_in_at: string | null;
        check_out_at: string | null;
        user: {
            name: string;
            email: string;
            phone: string | null;
        };
        shift: {
            title: string;
            zone_name: string;
        };
    };
};
