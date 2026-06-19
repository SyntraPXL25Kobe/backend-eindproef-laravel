import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import {
    publish,
    update,
} from '@/actions/App/Http/Controllers/CoordinatorEventController';
import CoordinatorEventApplicationsManager from '@/components/coordinator-event-applications-manager';
import CoordinatorEventCrewOverview from '@/components/coordinator-event-crew-overview';
import CoordinatorEventForm from '@/components/coordinator-event-form';
import CoordinatorEventStructureManager from '@/components/coordinator-event-structure-manager';
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
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useClipboard } from '@/hooks/use-clipboard';

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
    dashboard_url: string;
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

type ShiftStatusOption = {
    value: string;
    label: string;
};

type EventApplication = {
    id: number;
    status: 'pending' | 'approved' | 'rejected';
    motivation: string | null;
    created_at: string | null;
    reviewed_at: string | null;
    user: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
    };
    zone: {
        id: number;
        name: string;
    };
    shift: {
        id: number;
        title: string;
        starts_at: string | null;
        ends_at: string | null;
        capacity: number;
        approved_count: number;
    };
};

type CrewMember = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    approved_shifts_count: number;
    shifts: Array<{
        application_id: number;
        shift_id: number;
        title: string;
        zone_name: string;
        starts_at: string | null;
        ends_at: string | null;
    }>;
};

const statusLabels: Record<string, string> = {
    draft: 'Concept',
    published: 'Gepubliceerd',
    archived: 'Gearchiveerd',
};

export default function EditCoordinatorEvent({
    event,
    applications,
    crewMembers,
    visibilityOptions,
    skillOptions,
    shiftStatusOptions,
}: {
    event: EventDetail;
    applications: EventApplication[];
    crewMembers: CrewMember[];
    visibilityOptions: VisibilityOption[];
    skillOptions: SkillOption[];
    shiftStatusOptions: ShiftStatusOption[];
}) {
    const [copiedText, copy] = useClipboard();
    const [publicationVisibility, setPublicationVisibility] = useState(
        event.publication_visibility,
    );

    const publishedLink =
        publicationVisibility === 'invite_only'
            ? event.invite_url
            : event.public_url;

    return (
        <>
            <Head title={event.title} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Tabs defaultValue="crew" className="space-y-5">
                    <TabsList variant="line" className="w-full justify-start">
                        <TabsTrigger value="crew">Crew en shiften</TabsTrigger>
                        <TabsTrigger value="details">
                            Evenementdetails
                        </TabsTrigger>
                        <TabsTrigger value="publish">Publicatie</TabsTrigger>
                        <TabsTrigger value="structure">
                            Zones en shiften
                        </TabsTrigger>
                        <TabsTrigger value="applications">
                            Aanvragen
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent
                        value="crew"
                        className="mx-auto w-full max-w-6xl space-y-4"
                    >
                        <CoordinatorEventCrewOverview
                            crewMembers={crewMembers}
                        />
                    </TabsContent>

                    <TabsContent
                        value="details"
                        className="mx-auto w-full max-w-5xl space-y-4"
                    >
                        <CoordinatorEventForm
                            title={event.title}
                            description="Werk je evenement uit en sla wijzigingen op voordat je publiceert."
                            formAction={update.form({ event: event.id })}
                            values={{
                                title: event.title,
                                description: event.description ?? '',
                                location: event.location,
                                start_date: event.start_date ?? '',
                                end_date: event.end_date ?? '',
                                max_crew_members:
                                    event.max_crew_members?.toString() ?? '',
                                cover_image_url: event.cover_image_url ?? '',
                                publication_visibility:
                                    event.publication_visibility,
                            }}
                            visibilityOptions={visibilityOptions}
                            submitLabel="Wijzigingen opslaan"
                            onPublicationVisibilityChange={
                                setPublicationVisibility
                            }
                        />
                    </TabsContent>

                    <TabsContent
                        value="publish"
                        className="mx-auto w-full max-w-5xl space-y-4"
                    >
                        <Card>
                            <CardHeader>
                                <div className="flex flex-wrap gap-2">
                                    <Badge>
                                        {statusLabels[event.status] ??
                                            event.status}
                                    </Badge>
                                    <Badge variant="outline">
                                        {publicationVisibility === 'invite_only'
                                            ? 'Alleen op uitnodiging'
                                            : 'Publiek'}
                                    </Badge>
                                </div>
                                <CardTitle>Publicatie</CardTitle>
                                <CardDescription>
                                    Zet dit evenement live als publieke pagina
                                    of als unieke uitnodigingslink voor de crew.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm text-muted-foreground">
                                <p>
                                    Huidige keuze:{' '}
                                    {publicationVisibility === 'invite_only'
                                        ? 'uitnodigingslink voor de crew'
                                        : 'publieke evenementpagina'}
                                </p>
                                {publishedLink && (
                                    <p className="break-all">{publishedLink}</p>
                                )}
                            </CardContent>
                            <CardFooter className="flex flex-wrap gap-3">
                                <Form
                                    {...publish.form({ event: event.id })}
                                    options={{ preserveScroll: true }}
                                >
                                    {({ processing }) => (
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {event.status === 'published'
                                                ? 'Opnieuw publiceren'
                                                : 'Publiceren'}
                                        </Button>
                                    )}
                                </Form>

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
                                            Pagina openen
                                        </Link>
                                    </Button>
                                )}

                                <Button asChild variant="secondary">
                                    <Link href={event.dashboard_url}>
                                        Dashboard openen
                                    </Link>
                                </Button>
                            </CardFooter>
                        </Card>

                        {event.cover_image_url && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Voorbeeld</CardTitle>
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
                    </TabsContent>

                    <TabsContent
                        value="structure"
                        className="mx-auto w-full max-w-6xl space-y-4"
                    >
                        <CoordinatorEventStructureManager
                            eventId={event.id}
                            zones={event.zones}
                            skillOptions={skillOptions}
                            shiftStatusOptions={shiftStatusOptions}
                        />
                    </TabsContent>

                    <TabsContent
                        value="applications"
                        className="mx-auto w-full max-w-6xl space-y-4"
                    >
                        <CoordinatorEventApplicationsManager
                            applications={applications}
                        />
                    </TabsContent>
                </Tabs>
            </div>
        </>
    );
}

EditCoordinatorEvent.layout = {
    breadcrumbs: [
        {
            title: 'Mijn evenementen',
            href: '/app/events',
        },
        {
            title: 'Evenement beheren',
            href: '/app/events',
        },
    ],
};
