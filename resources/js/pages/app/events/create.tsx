import { Head, useForm } from '@inertiajs/react';
import CoordinatorEventForm from '@/components/coordinator-event-form';
import type {CoordinatorEventFormData} from '@/components/coordinator-event-form';

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
    const form = useForm<CoordinatorEventFormData>({
        title: '',
        description: '',
        location: '',
        start_date: '',
        end_date: '',
        max_crew_members: '',
        cover_image_url: '',
        publication_visibility: 'public',
    });

    return (
        <>
            <Head title="Nieuw event" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <CoordinatorEventForm
                    title="Nieuw event"
                    description="Sla eerst een concept op. Publiceren gebeurt daarna op de detailpagina."
                    data={form.data}
                    setData={form.setData}
                    errors={form.errors}
                    processing={form.processing}
                    visibilityOptions={visibilityOptions}
                    submitLabel="Opslaan als concept"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.post('/app/events');
                    }}
                />
            </div>
        </>
    );
}

CreateCoordinatorEvent.layout = {
    breadcrumbs: [
        {
            title: 'Mijn events',
            href: '/app/events',
        },
        {
            title: 'Nieuw event',
            href: '/app/events/create',
        },
    ],
};
