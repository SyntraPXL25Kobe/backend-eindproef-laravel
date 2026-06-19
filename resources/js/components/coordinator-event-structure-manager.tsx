import { Form, router } from '@inertiajs/react';
import {
    destroy as destroyShift,
    store as storeShift,
    update as updateShift,
} from '@/actions/App/Http/Controllers/CoordinatorShiftController';
import {
    destroy as destroyZone,
    store as storeZone,
    update as updateZone,
} from '@/actions/App/Http/Controllers/CoordinatorZoneController';
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
    return (
        <Card className="border-border/70 bg-card/95">
            <CardHeader>
                <CardTitle>{zone.name}</CardTitle>
                <CardDescription>
                    Beheer de zonegegevens en alle shiften binnen deze zone.
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
                <Form
                    {...updateZone.form({ zone: zone.id })}
                    options={{ preserveScroll: true }}
                    className="grid gap-4 rounded-2xl border border-border/70 bg-muted/30 p-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor={`zone-name-${zone.id}`}>
                                    Zonenaam
                                </Label>
                                <Input
                                    id={`zone-name-${zone.id}`}
                                    name="name"
                                    defaultValue={zone.name}
                                />
                                <InputError message={errors.name as string} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor={`zone-description-${zone.id}`}>
                                    Beschrijving
                                </Label>
                                <textarea
                                    id={`zone-description-${zone.id}`}
                                    name="description"
                                    defaultValue={zone.description ?? ''}
                                    className="min-h-24 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                                />
                                <InputError
                                    message={errors.description as string}
                                />
                            </div>

                            <div className="flex flex-wrap gap-3">
                                <Button disabled={processing}>
                                    Zone opslaan
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    disabled={processing}
                                    onClick={() =>
                                        router.delete(
                                            destroyZone({ zone: zone.id }),
                                            {
                                                preserveScroll: true,
                                            },
                                        )
                                    }
                                >
                                    Zone verwijderen
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

                <div className="space-y-4">
                    <Heading
                        variant="small"
                        title="Shiften"
                        description="Voeg shiften toe of werk bestaande shiften bij voor deze zone."
                    />

                    {zone.shifts.map((shift) => (
                        <ShiftEditor
                            key={shift.id}
                            shift={shift}
                            skillOptions={skillOptions}
                            shiftStatusOptions={shiftStatusOptions}
                        />
                    ))}

                    <Form
                        {...storeShift.form({ zone: zone.id })}
                        options={{ preserveScroll: true }}
                        resetOnSuccess={[
                            'title',
                            'description',
                            'starts_at',
                            'ends_at',
                            'capacity',
                            'required_skill_id',
                            'status',
                        ]}
                        className="grid gap-4 rounded-2xl border border-dashed border-border/70 bg-background/70 p-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <h4 className="text-sm font-medium">
                                    Nieuwe shift
                                </h4>
                                <ShiftFields
                                    values={emptyShiftData()}
                                    errors={errors as EventFormErrors}
                                    skillOptions={skillOptions}
                                    shiftStatusOptions={shiftStatusOptions}
                                    idPrefix={`create-shift-${zone.id}`}
                                />
                                <div>
                                    <Button disabled={processing}>
                                        Shift toevoegen
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </div>
            </CardContent>
        </Card>
    );
}

type EventFormErrors = Partial<Record<keyof ShiftFormData, string>>;

function ShiftEditor({
    shift,
    skillOptions,
    shiftStatusOptions,
}: {
    shift: ShiftDetail;
    skillOptions: SkillOption[];
    shiftStatusOptions: ShiftStatusOption[];
}) {
    return (
        <Form
            {...updateShift.form({ shift: shift.id })}
            options={{ preserveScroll: true }}
            className="grid gap-4 rounded-2xl border border-border/70 bg-muted/30 p-4"
        >
            {({ processing, errors }) => (
                <>
                    <div className="flex items-center justify-between gap-3">
                        <h4 className="text-sm font-medium">{shift.title}</h4>
                        <span className="text-xs text-muted-foreground">
                            {shift.required_skill_name
                                ? `Vaardigheid: ${shift.required_skill_name}`
                                : 'Geen vaardigheid vereist'}
                        </span>
                    </div>

                    <ShiftFields
                        values={{
                            title: shift.title,
                            description: shift.description ?? '',
                            starts_at: shift.starts_at ?? '',
                            ends_at: shift.ends_at ?? '',
                            capacity: shift.capacity.toString(),
                            required_skill_id:
                                shift.required_skill_id?.toString() ?? '',
                            status: shift.status,
                        }}
                        errors={errors as EventFormErrors}
                        skillOptions={skillOptions}
                        shiftStatusOptions={shiftStatusOptions}
                        idPrefix={`shift-${shift.id}`}
                    />

                    <div className="flex flex-wrap gap-3">
                        <Button disabled={processing}>Shift opslaan</Button>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={processing}
                            onClick={() =>
                                router.delete(
                                    destroyShift({ shift: shift.id }),
                                    {
                                        preserveScroll: true,
                                    },
                                )
                            }
                        >
                            Shift verwijderen
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}

function ShiftFields({
    values,
    errors,
    skillOptions,
    shiftStatusOptions,
    idPrefix,
}: {
    values: ShiftFormData;
    errors: EventFormErrors;
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
                        name="title"
                        defaultValue={values.title}
                    />
                    <InputError message={errors.title} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor={`${idPrefix}-capacity`}>Capaciteit</Label>
                    <Input
                        id={`${idPrefix}-capacity`}
                        name="capacity"
                        type="number"
                        min="1"
                        defaultValue={values.capacity}
                    />
                    <InputError message={errors.capacity} />
                </div>
            </div>

            <div className="grid gap-2">
                <Label htmlFor={`${idPrefix}-description`}>Beschrijving</Label>
                <textarea
                    id={`${idPrefix}-description`}
                    name="description"
                    defaultValue={values.description}
                    className="min-h-24 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                />
                <InputError message={errors.description} />
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor={`${idPrefix}-starts-at`}>Start</Label>
                    <Input
                        id={`${idPrefix}-starts-at`}
                        name="starts_at"
                        type="datetime-local"
                        defaultValue={values.starts_at}
                    />
                    <InputError message={errors.starts_at} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor={`${idPrefix}-ends-at`}>Einde</Label>
                    <Input
                        id={`${idPrefix}-ends-at`}
                        name="ends_at"
                        type="datetime-local"
                        defaultValue={values.ends_at}
                    />
                    <InputError message={errors.ends_at} />
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor={`${idPrefix}-status`}>Status</Label>
                    <select
                        id={`${idPrefix}-status`}
                        name="status"
                        defaultValue={values.status}
                        className="h-10 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                    >
                        {shiftStatusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <InputError message={errors.status} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor={`${idPrefix}-skill`}>
                        Vereiste vaardigheid
                    </Label>
                    <select
                        id={`${idPrefix}-skill`}
                        name="required_skill_id"
                        defaultValue={values.required_skill_id}
                        className="h-10 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                    >
                        <option value="">Geen vaardigheid vereist</option>
                        {skillOptions.map((option) => (
                            <option
                                key={option.value}
                                value={option.value.toString()}
                            >
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <InputError message={errors.required_skill_id} />
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
    return (
        <section className="space-y-6">
            <Heading
                title="Zones en shiften"
                description="Beheer per evenement de zones en de shiften waarvoor de crew zich kan inschrijven."
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
                    <Form
                        {...storeZone.form({ event: eventId })}
                        options={{ preserveScroll: true }}
                        resetOnSuccess={['name', 'description']}
                        className="grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="create-zone-name">
                                        Zonenaam
                                    </Label>
                                    <Input id="create-zone-name" name="name" />
                                    <InputError
                                        message={errors.name as string}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="create-zone-description">
                                        Beschrijving
                                    </Label>
                                    <Input
                                        id="create-zone-description"
                                        name="description"
                                    />
                                    <InputError
                                        message={errors.description as string}
                                    />
                                </div>

                                <div>
                                    <Button disabled={processing}>
                                        Zone toevoegen
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </CardContent>
            </Card>

            <div className="grid gap-6">
                {zones.length === 0 ? (
                    <Card className="border-dashed border-border/70 bg-card/95">
                        <CardContent className="pt-6 text-sm text-muted-foreground">
                            Nog geen zones toegevoegd voor dit evenement.
                        </CardContent>
                    </Card>
                ) : (
                    zones.map((zone) => (
                        <ZoneEditor
                            key={zone.id}
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
