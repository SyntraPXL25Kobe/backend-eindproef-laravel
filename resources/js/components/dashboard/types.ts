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
    show_url: string;
};
