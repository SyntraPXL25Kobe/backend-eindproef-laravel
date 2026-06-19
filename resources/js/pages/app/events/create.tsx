import { Head } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/CoordinatorEventController';
import CoordinatorEventForm from '@/components/coordinator-event-form';

type VisibilityOption = {
    value: string;
    label: string;
    description: string;
};

export default function CreateCoordinatorEvent({
    visibilityOptions,
}: {
    visibilityOptions: VisibilityOption[];
}) {
    return (
        <>
            <Head title="Nieuw evenement" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <CoordinatorEventForm
                    title="Nieuw evenement"
                    description="Sla eerst een concept op. Publiceren gebeurt daarna op de detailpagina."
                    formAction={store.form()}
                    values={{
                        title: '',
                        description: '',
                        location: '',
                        start_date: '',
                        end_date: '',
                        max_crew_members: '',
                        cover_image_url: '',
                        publication_visibility: 'public',
                    }}
                    visibilityOptions={visibilityOptions}
                    submitLabel="Opslaan als concept"
                />
            </div>
        </>
    );
}

CreateCoordinatorEvent.layout = {
    breadcrumbs: [
        {
            title: 'Mijn evenementen',
            href: '/app/events',
        },
        {
            title: 'Nieuw evenement',
            href: '/app/events/create',
        },
    ],
};
