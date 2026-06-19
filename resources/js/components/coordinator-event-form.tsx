import { Form } from '@inertiajs/react';
import type { RouteFormDefinition } from '@/wayfinder';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type CoordinatorEventFormData = {
    title: string;
    description: string;
    location: string;
    start_date: string;
    end_date: string;
    max_crew_members: string;
    cover_image_url: string;
    publication_visibility: string;
};

type VisibilityOption = {
    value: string;
    label: string;
    description: string;
};

type Props = {
    title: string;
    description?: string;
    formAction: RouteFormDefinition<'get'> | RouteFormDefinition<'post'>;
    values: CoordinatorEventFormData;
    submitLabel: string;
    visibilityOptions: VisibilityOption[];
    onPublicationVisibilityChange?: (value: string) => void;
};

export default function CoordinatorEventForm({
    title,
    description,
    formAction,
    values,
    submitLabel,
    visibilityOptions,
    onPublicationVisibilityChange,
}: Props) {
    return (
        <Form
            action={formAction.action}
            method={formAction.method}
            options={{
                preserveScroll: true,
            }}
            className="space-y-6"
        >
            {({ errors, processing }) => (
                <>
                    <Heading title={title} description={description} />

                    <div className="grid gap-5 rounded-2xl border border-sidebar-border/70 bg-background/95 p-6 shadow-xs">
                        <div className="grid gap-2">
                            <Label htmlFor="title">Titel</Label>
                            <Input
                                id="title"
                                name="title"
                                defaultValue={values.title}
                                placeholder="Bijvoorbeeld: Stadsfestival 2026"
                            />
                            <InputError message={errors.title as string} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="description">Beschrijving</Label>
                            <textarea
                                id="description"
                                name="description"
                                defaultValue={values.description}
                                placeholder="Beschrijf sfeer, taken en verwachtingen voor de crew."
                                className="min-h-32 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                            />
                            <InputError
                                message={errors.description as string}
                            />
                        </div>

                        <div className="grid gap-5 md:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="location">Locatie</Label>
                                <Input
                                    id="location"
                                    name="location"
                                    defaultValue={values.location}
                                    placeholder="Antwerpen, Park Spoor Noord"
                                />
                                <InputError
                                    message={errors.location as string}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="max_crew_members">
                                    Max. crewleden
                                </Label>
                                <Input
                                    id="max_crew_members"
                                    name="max_crew_members"
                                    type="number"
                                    min="1"
                                    defaultValue={values.max_crew_members}
                                    placeholder="40"
                                />
                                <InputError
                                    message={errors.max_crew_members as string}
                                />
                            </div>
                        </div>

                        <div className="grid gap-5 md:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="start_date">Startdatum</Label>
                                <Input
                                    id="start_date"
                                    name="start_date"
                                    type="date"
                                    defaultValue={values.start_date}
                                />
                                <InputError
                                    message={errors.start_date as string}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="end_date">Einddatum</Label>
                                <Input
                                    id="end_date"
                                    name="end_date"
                                    type="date"
                                    defaultValue={values.end_date}
                                />
                                <InputError
                                    message={errors.end_date as string}
                                />
                            </div>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="cover_image_url">
                                Omslagafbeelding (URL)
                            </Label>
                            <Input
                                id="cover_image_url"
                                name="cover_image_url"
                                type="url"
                                defaultValue={values.cover_image_url}
                                placeholder="https://..."
                            />
                            <InputError
                                message={errors.cover_image_url as string}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="publication_visibility">
                                Publicatievorm
                            </Label>
                            <select
                                id="publication_visibility"
                                name="publication_visibility"
                                defaultValue={values.publication_visibility}
                                onChange={(event) =>
                                    onPublicationVisibilityChange?.(
                                        event.currentTarget.value,
                                    )
                                }
                                className="h-10 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                            >
                                {visibilityOptions.map((option) => (
                                    <option
                                        key={option.value}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                            <InputError
                                message={
                                    errors.publication_visibility as string
                                }
                            />
                        </div>

                        <Alert>
                            <AlertTitle>
                                Zichtbaarheid bij publicatie
                            </AlertTitle>
                            <AlertDescription>
                                {visibilityOptions.map((option) => (
                                    <p key={option.value}>
                                        <strong>{option.label}:</strong>{' '}
                                        {option.description}
                                    </p>
                                ))}
                            </AlertDescription>
                        </Alert>

                        <div className="flex items-center gap-3">
                            <Button disabled={processing}>{submitLabel}</Button>
                        </div>
                    </div>
                </>
            )}
        </Form>
    );
}
