export type AuthUser = { id: number } | null;

export type ShiftApplication = {
    id: number;
    status: string;
};

export type Shift = {
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

export type Zone = {
    id: number;
    name: string;
    description: string | null;
    shifts: Shift[];
};

export type PublicEvent = {
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
