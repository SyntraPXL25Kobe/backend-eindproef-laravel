import { Head, Link, useForm } from '@inertiajs/react';
import CoordinatorEventStructureManager from '@/components/coordinator-event-structure-manager';
import { useClipboard } from '@/hooks/use-clipboard';
import CoordinatorEventForm, {
    type CoordinatorEventFormData,
} from '@/components/coordinator-event-form';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type VisibilityOption = {
    value: string;
    label: string;
    description: string;
};

type EventDetail = {
    id: number;
    title: string;
    description: string | null;
    location: string;
    start_date: string | null;
    end_date: string | null;
    status: string;
    publication_visibility: string;
    max_crew_members: number | null;
    cover_image_url: string | null;
    public_url: string | null;
    invite_url: string | null;
    zones: Array<{
        id: number;
        name: string;
        description: string | null;
        shifts: Array<{
            id: number;
            title: string;
            description: string | null;
            starts_at: string | null;
            ends_at: string | null;
            capacity: number;
            status: string;
            required_skill_id: number | null;
            required_skill_name: string | null;
        }>;
    }>;
};

type SkillOption = {
    value: number;
    label: string;
};

const statusLabels: Record<string, string> = {
    draft: 'Concept',
    published: 'Gepubliceerd',
    archived: 'Gearchiveerd',
};

export default function EditCoordinatorEvent({
    event,
    visibilityOptions,
    skillOptions,
    shiftStatusOptions,
}: {
    event: EventDetail;
    visibilityOptions: VisibilityOption[];
    skillOptions: SkillOption[];
    shiftStatusOptions: VisibilityOption[];
}) {
    const [copiedText, copy] = useClipboard();
    const form = useForm<CoordinatorEventFormData>({
        title: event.title,
        description: event.description ?? '',
        location: event.location,
        start_date: event.start_date ?? '',
        end_date: event.end_date ?? '',
        max_crew_members: event.max_crew_members?.toString() ?? '',
        cover_image_url: event.cover_image_url ?? '',
        publication_visibility: event.publication_visibility,
    });

    const publishedLink =
        form.data.publication_visibility === 'invite_only'
            ? event.invite_url
            : event.public_url;

    return (
        <>
            <Head title={event.title} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="grid gap-6 xl:grid-cols-[minmax(0,2fr)_360px]">
                    <CoordinatorEventForm
                        title={event.title}
                        description="Werk je event uit en publiceer het zodra de crew-informatie klaarstaat."
                        data={form.data}
                        setData={form.setData}
                        errors={form.errors}
                        processing={form.processing}
                        visibilityOptions={visibilityOptions}
                        submitLabel="Wijzigingen opslaan"
                        onSubmit={(submitEvent) => {
                            submitEvent.preventDefault();
                            form.put(`/app/events/${event.id}`);
                        }}
                    />

                    <div className="space-y-4">
                        <Card>
                            <CardHeader>
                                <div className="flex flex-wrap gap-2">
                                    <Badge>
                                        {statusLabels[event.status] ??
                                            event.status}
                                    </Badge>
                                    <Badge variant="outline">
                                        {form.data.publication_visibility ===
                                        'invite_only'
                                            ? 'Invite-only'
                                            : 'Publiek'}
                                    </Badge>
                                </div>
                                <CardTitle>Publiceren</CardTitle>
                                <CardDescription>
                                    Een publicatie zet dit event live voor het
                                    gekozen kanaal.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm text-muted-foreground">
                                <p>
                                    Huidige keuze:{' '}
                                    {form.data.publication_visibility ===
                                    'invite_only'
                                        ? 'crew-uitnodigingslink'
                                        : 'publieke eventpagina'}
                                </p>
                                {publishedLink && (
                                    <p className="break-all">{publishedLink}</p>
                                )}
                            </CardContent>
                            <CardFooter className="flex flex-wrap gap-3">
                                <Button
                                    type="button"
                                    onClick={() =>
                                        form.post(
                                            `/app/events/${event.id}/publish`,
                                            {
                                                preserveScroll: true,
                                            },
                                        )
                                    }
                                    disabled={form.processing}
                                >
                                    {event.status === 'published'
                                        ? 'Opnieuw publiceren'
                                        : 'Nu publiceren'}
                                </Button>

                                {publishedLink && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => void copy(publishedLink)}
                                    >
                                        {copiedText === publishedLink
                                            ? 'Link gekopieerd'
                                            : 'Kopieer link'}
                                    </Button>
                                )}

                                {publishedLink && (
                                    <Button asChild variant="ghost">
                                        <Link href={publishedLink}>
                                            Open pagina
                                        </Link>
                                    </Button>
                                )}
                            </CardFooter>
                        </Card>

                        {event.cover_image_url && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Preview</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <img
                                        src={event.cover_image_url}
                                        alt={event.title}
                                        className="h-48 w-full rounded-xl object-cover"
                                    />
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>

                <CoordinatorEventStructureManager
                    eventId={event.id}
                    zones={event.zones}
                    skillOptions={skillOptions}
                    shiftStatusOptions={shiftStatusOptions}
                />
            </div>
        </>
    );
}

EditCoordinatorEvent.layout = {
    breadcrumbs: [
        {
            title: 'Mijn events',
            href: '/app/events',
        },
        {
            title: 'Event beheren',
            href: '/app/events',
        },
    ],
};
