import { useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type SkillOption = {
    value: number;
    label: string;
};

type ShiftStatusOption = {
    value: string;
    label: string;
};

type ShiftDetail = {
    id: number;
    title: string;
    description: string | null;
    starts_at: string | null;
    ends_at: string | null;
    capacity: number;
    status: string;
    required_skill_id: number | null;
    required_skill_name: string | null;
};

type ZoneDetail = {
    id: number;
    name: string;
    description: string | null;
    shifts: ShiftDetail[];
};

type Props = {
    eventId: number;
    zones: ZoneDetail[];
    skillOptions: SkillOption[];
    shiftStatusOptions: ShiftStatusOption[];
};

type ZoneFormData = {
    name: string;
    description: string;
};

type ShiftFormData = {
    title: string;
    description: string;
    starts_at: string;
    ends_at: string;
    capacity: string;
    required_skill_id: string;
    status: string;
};

const emptyShiftData = (status = 'open'): ShiftFormData => ({
    title: '',
    description: '',
    starts_at: '',
    ends_at: '',
    capacity: '1',
    required_skill_id: '',
    status,
});

function ZoneEditor({
    zone,
    skillOptions,
    shiftStatusOptions,
}: {
    zone: ZoneDetail;
    skillOptions: SkillOption[];
    shiftStatusOptions: ShiftStatusOption[];
}) {
    const zoneForm = useForm<ZoneFormData>({
        name: zone.name,
        description: zone.description ?? '',
    });
    const createShiftForm = useForm<ShiftFormData>(emptyShiftData());

    return (
        <Card className="border-border/70 bg-card/95">
            <CardHeader>
                <CardTitle>{zone.name}</CardTitle>
                <CardDescription>
                    Beheer de zonegegevens en alle shifts binnen deze zone.
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
                <form
                    className="grid gap-4 rounded-2xl border border-border/70 bg-muted/30 p-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        zoneForm.patch(`/app/zones/${zone.id}`, {
                            preserveScroll: true,
                        });
                    }}
                >
                    <div className="grid gap-2">
                        <Label htmlFor={`zone-name-${zone.id}`}>Zonenaam</Label>
                        <Input
                            id={`zone-name-${zone.id}`}
                            value={zoneForm.data.name}
                            onChange={(event) =>
                                zoneForm.setData(
                                    'name',
                                    event.currentTarget.value,
                                )
                            }
                        />
                        <InputError message={zoneForm.errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor={`zone-description-${zone.id}`}>
                            Beschrijving
                        </Label>
                        <textarea
                            id={`zone-description-${zone.id}`}
                            value={zoneForm.data.description}
                            onChange={(event) =>
                                zoneForm.setData(
                                    'description',
                                    event.currentTarget.value,
                                )
                            }
                            className="min-h-24 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                        />
                        <InputError message={zoneForm.errors.description} />
                    </div>

                    <div className="flex flex-wrap gap-3">
                        <Button disabled={zoneForm.processing}>
                            Zone opslaan
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={zoneForm.processing}
                            onClick={() =>
                                zoneForm.delete(`/app/zones/${zone.id}`, {
                                    preserveScroll: true,
                                })
                            }
                        >
                            Zone verwijderen
                        </Button>
                    </div>
                </form>

                <div className="space-y-4">
                    <Heading
                        variant="small"
                        title="Shifts"
                        description="Voeg shifts toe of werk bestaande shifts bij voor deze zone."
                    />

                    {zone.shifts.map((shift) => (
                        <ShiftEditor
                            key={shift.id}
                            shift={shift}
                            skillOptions={skillOptions}
                            shiftStatusOptions={shiftStatusOptions}
                        />
                    ))}

                    <form
                        className="grid gap-4 rounded-2xl border border-dashed border-border/70 bg-background/70 p-4"
                        onSubmit={(event) => {
                            event.preventDefault();
                            createShiftForm.post(
                                `/app/zones/${zone.id}/shifts`,
                                {
                                    preserveScroll: true,
                                    onSuccess: () =>
                                        createShiftForm.reset(
                                            ...(Object.keys(
                                                createShiftForm.data,
                                            ) as Array<keyof ShiftFormData>),
                                        ),
                                },
                            );
                        }}
                    >
                        <h4 className="text-sm font-medium">Nieuwe shift</h4>
                        <ShiftFields
                            form={createShiftForm}
                            skillOptions={skillOptions}
                            shiftStatusOptions={shiftStatusOptions}
                            idPrefix={`create-shift-${zone.id}`}
                        />
                        <div>
                            <Button disabled={createShiftForm.processing}>
                                Shift toevoegen
                            </Button>
                        </div>
                    </form>
                </div>
            </CardContent>
        </Card>
    );
}

function ShiftEditor({
    shift,
    skillOptions,
    shiftStatusOptions,
}: {
    shift: ShiftDetail;
    skillOptions: SkillOption[];
    shiftStatusOptions: ShiftStatusOption[];
}) {
    const shiftForm = useForm<ShiftFormData>({
        title: shift.title,
        description: shift.description ?? '',
        starts_at: shift.starts_at ?? '',
        ends_at: shift.ends_at ?? '',
        capacity: shift.capacity.toString(),
        required_skill_id: shift.required_skill_id?.toString() ?? '',
        status: shift.status,
    });

    return (
        <form
            className="grid gap-4 rounded-2xl border border-border/70 bg-muted/30 p-4"
            onSubmit={(event) => {
                event.preventDefault();
                shiftForm.patch(`/app/shifts/${shift.id}`, {
                    preserveScroll: true,
                });
            }}
        >
            <div className="flex items-center justify-between gap-3">
                <h4 className="text-sm font-medium">{shift.title}</h4>
                <span className="text-xs text-muted-foreground">
                    {shift.required_skill_name
                        ? `Skill: ${shift.required_skill_name}`
                        : 'Geen skill vereist'}
                </span>
            </div>

            <ShiftFields
                form={shiftForm}
                skillOptions={skillOptions}
                shiftStatusOptions={shiftStatusOptions}
                idPrefix={`shift-${shift.id}`}
            />

            <div className="flex flex-wrap gap-3">
                <Button disabled={shiftForm.processing}>Shift opslaan</Button>
                <Button
                    type="button"
                    variant="outline"
                    disabled={shiftForm.processing}
                    onClick={() =>
                        shiftForm.delete(`/app/shifts/${shift.id}`, {
                            preserveScroll: true,
                        })
                    }
                >
                    Shift verwijderen
                </Button>
            </div>
        </form>
    );
}

function ShiftFields({
    form,
    skillOptions,
    shiftStatusOptions,
    idPrefix,
}: {
    form: ReturnType<typeof useForm<ShiftFormData>>;
    skillOptions: SkillOption[];
    shiftStatusOptions: ShiftStatusOption[];
    idPrefix: string;
}) {
    return (
        <>
            <div className="grid gap-4 md:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor={`${idPrefix}-title`}>Titel</Label>
                    <Input
                        id={`${idPrefix}-title`}
                        value={form.data.title}
                        onChange={(event) =>
                            form.setData('title', event.currentTarget.value)
                        }
                    />
                    <InputError message={form.errors.title} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor={`${idPrefix}-capacity`}>Capaciteit</Label>
                    <Input
                        id={`${idPrefix}-capacity`}
                        type="number"
                        min="1"
                        value={form.data.capacity}
                        onChange={(event) =>
                            form.setData('capacity', event.currentTarget.value)
                        }
                    />
                    <InputError message={form.errors.capacity} />
                </div>
            </div>

            <div className="grid gap-2">
                <Label htmlFor={`${idPrefix}-description`}>Beschrijving</Label>
                <textarea
                    id={`${idPrefix}-description`}
                    value={form.data.description}
                    onChange={(event) =>
                        form.setData('description', event.currentTarget.value)
                    }
                    className="min-h-24 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                />
                <InputError message={form.errors.description} />
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor={`${idPrefix}-starts-at`}>Start</Label>
                    <Input
                        id={`${idPrefix}-starts-at`}
                        type="datetime-local"
                        value={form.data.starts_at}
                        onChange={(event) =>
                            form.setData('starts_at', event.currentTarget.value)
                        }
                    />
                    <InputError message={form.errors.starts_at} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor={`${idPrefix}-ends-at`}>Einde</Label>
                    <Input
                        id={`${idPrefix}-ends-at`}
                        type="datetime-local"
                        value={form.data.ends_at}
                        onChange={(event) =>
                            form.setData('ends_at', event.currentTarget.value)
                        }
                    />
                    <InputError message={form.errors.ends_at} />
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor={`${idPrefix}-status`}>Status</Label>
                    <select
                        id={`${idPrefix}-status`}
                        value={form.data.status}
                        onChange={(event) =>
                            form.setData('status', event.currentTarget.value)
                        }
                        className="h-10 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                    >
                        {shiftStatusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <InputError message={form.errors.status} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor={`${idPrefix}-skill`}>Vereiste skill</Label>
                    <select
                        id={`${idPrefix}-skill`}
                        value={form.data.required_skill_id}
                        onChange={(event) =>
                            form.setData(
                                'required_skill_id',
                                event.currentTarget.value,
                            )
                        }
                        className="h-10 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                    >
                        <option value="">Geen skill vereist</option>
                        {skillOptions.map((option) => (
                            <option
                                key={option.value}
                                value={option.value.toString()}
                            >
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <InputError message={form.errors.required_skill_id} />
                </div>
            </div>
        </>
    );
}

export default function CoordinatorEventStructureManager({
    eventId,
    zones,
    skillOptions,
    shiftStatusOptions,
}: Props) {
    const createZoneForm = useForm<ZoneFormData>({
        name: '',
        description: '',
    });

    return (
        <section className="space-y-6">
            <Heading
                title="Zones en shifts"
                description="Beheer per event de zones en de shifts waarvoor crew members zich kunnen inschrijven."
            />

            <Card className="border-dashed border-border/70 bg-card/95">
                <CardHeader>
                    <CardTitle>Nieuwe zone</CardTitle>
                    <CardDescription>
                        Voeg eerst een zone toe. Daarna kan je binnen die zone
                        afzonderlijke shifts aanmaken.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form
                        className="grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end"
                        onSubmit={(event) => {
                            event.preventDefault();
                            createZoneForm.post(
                                `/app/events/${eventId}/zones`,
                                {
                                    preserveScroll: true,
                                    onSuccess: () => createZoneForm.reset(),
                                },
                            );
                        }}
                    >
                        <div className="grid gap-2">
                            <Label htmlFor="create-zone-name">Zonenaam</Label>
                            <Input
                                id="create-zone-name"
                                value={createZoneForm.data.name}
                                onChange={(event) =>
                                    createZoneForm.setData(
                                        'name',
                                        event.currentTarget.value,
                                    )
                                }
                            />
                            <InputError message={createZoneForm.errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="create-zone-description">
                                Beschrijving
                            </Label>
                            <Input
                                id="create-zone-description"
                                value={createZoneForm.data.description}
                                onChange={(event) =>
                                    createZoneForm.setData(
                                        'description',
                                        event.currentTarget.value,
                                    )
                                }
                            />
                            <InputError
                                message={createZoneForm.errors.description}
                            />
                        </div>

                        <div>
                            <Button disabled={createZoneForm.processing}>
                                Zone toevoegen
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <div className="grid gap-6">
                {zones.length === 0 ? (
                    <Card className="border-dashed border-border/70 bg-card/95">
                        <CardContent className="pt-6 text-sm text-muted-foreground">
                            Nog geen zones toegevoegd voor dit event.
                        </CardContent>
                    </Card>
                ) : (
                    zones.map((zone) => (
                        <ZoneEditor
                            key={zone.id}
                            eventId={eventId}
                            zone={zone}
                            skillOptions={skillOptions}
                            shiftStatusOptions={shiftStatusOptions}
                        />
                    ))
                )}
            </div>
        </section>
    );
}
