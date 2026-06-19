import type { FormEvent } from 'react';
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

type EventFormErrors = Partial<Record<keyof CoordinatorEventFormData, string>>;

type Props = {
    title: string;
    description?: string;
    data: CoordinatorEventFormData;
    setData: (key: keyof CoordinatorEventFormData, value: string) => void;
    errors: EventFormErrors;
    processing: boolean;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    submitLabel: string;
    visibilityOptions: VisibilityOption[];
};

export default function CoordinatorEventForm({
    title,
    description,
    data,
    setData,
    errors,
    processing,
    onSubmit,
    submitLabel,
    visibilityOptions,
}: Props) {
    return (
        <form onSubmit={onSubmit} className="space-y-6">
            <Heading title={title} description={description} />

            <div className="grid gap-5 rounded-2xl border border-sidebar-border/70 bg-background/95 p-6 shadow-xs">
                <div className="grid gap-2">
                    <Label htmlFor="title">Titel</Label>
                    <Input
                        id="title"
                        value={data.title}
                        onChange={(event) =>
                            setData('title', event.currentTarget.value)
                        }
                        placeholder="Bijvoorbeeld: Stadsfestival 2026"
                    />
                    <InputError message={errors.title} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="description">Beschrijving</Label>
                    <textarea
                        id="description"
                        value={data.description}
                        onChange={(event) =>
                            setData('description', event.currentTarget.value)
                        }
                        placeholder="Beschrijf sfeer, taken en verwachtingen voor crew members."
                        className="min-h-32 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                    />
                    <InputError message={errors.description} />
                </div>

                <div className="grid gap-5 md:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="location">Locatie</Label>
                        <Input
                            id="location"
                            value={data.location}
                            onChange={(event) =>
                                setData('location', event.currentTarget.value)
                            }
                            placeholder="Antwerpen, Park Spoor Noord"
                        />
                        <InputError message={errors.location} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="max_crew_members">
                            Max. crew members
                        </Label>
                        <Input
                            id="max_crew_members"
                            type="number"
                            min="1"
                            value={data.max_crew_members}
                            onChange={(event) =>
                                setData(
                                    'max_crew_members',
                                    event.currentTarget.value,
                                )
                            }
                            placeholder="40"
                        />
                        <InputError message={errors.max_crew_members} />
                    </div>
                </div>

                <div className="grid gap-5 md:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="start_date">Startdatum</Label>
                        <Input
                            id="start_date"
                            type="date"
                            value={data.start_date}
                            onChange={(event) =>
                                setData('start_date', event.currentTarget.value)
                            }
                        />
                        <InputError message={errors.start_date} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="end_date">Einddatum</Label>
                        <Input
                            id="end_date"
                            type="date"
                            value={data.end_date}
                            onChange={(event) =>
                                setData('end_date', event.currentTarget.value)
                            }
                        />
                        <InputError message={errors.end_date} />
                    </div>
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="cover_image_url">Cover image URL</Label>
                    <Input
                        id="cover_image_url"
                        type="url"
                        value={data.cover_image_url}
                        onChange={(event) =>
                            setData(
                                'cover_image_url',
                                event.currentTarget.value,
                            )
                        }
                        placeholder="https://..."
                    />
                    <InputError message={errors.cover_image_url} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="publication_visibility">
                        Publicatievorm
                    </Label>
                    <select
                        id="publication_visibility"
                        value={data.publication_visibility}
                        onChange={(event) =>
                            setData(
                                'publication_visibility',
                                event.currentTarget.value,
                            )
                        }
                        className="h-10 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                    >
                        {visibilityOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <InputError message={errors.publication_visibility} />
                </div>

                <Alert>
                    <AlertTitle>Zichtbaarheid bij publicatie</AlertTitle>
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
        </form>
    );
}
