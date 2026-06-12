import { useMemo, useState } from 'react';
import { Form } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { update as updateSkills } from '@/routes/skills';

type SkillOption = {
    id: number;
    name: string;
    description: string | null;
};

type ProfileSkillsDialogProps = {
    availableSkills: SkillOption[];
    userSkills: number[];
};

export default function ProfileSkillsDialog({
    availableSkills,
    userSkills,
}: ProfileSkillsDialogProps) {
    const [open, setOpen] = useState(false);
    const [draftSkills, setDraftSkills] = useState<number[]>(userSkills);

    const selectedSkills = useMemo(
        () => availableSkills.filter((skill) => userSkills.includes(skill.id)),
        [availableSkills, userSkills],
    );

    const toggleSkill = (skillId: number, isChecked: boolean) => {
        setDraftSkills(
            isChecked
                ? [...draftSkills, skillId]
                : draftSkills.filter((id) => id !== skillId),
        );
    };

    return (
        <div className="space-y-6">
            <div className="flex items-start justify-between gap-4">
                <Heading
                    variant="small"
                    title="Skills"
                    description="Show off your skills and let others know what you're good at!"
                />

                <Dialog
                    open={open}
                    onOpenChange={(nextOpen) => {
                        setOpen(nextOpen);

                        if (nextOpen) {
                            setDraftSkills(userSkills);
                        }
                    }}
                >
                    <DialogTrigger asChild>
                        <Button variant="outline" type="button">
                            Edit Skills
                        </Button>
                    </DialogTrigger>

                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Edit Skills</DialogTitle>
                            <DialogDescription>
                                Select the skills that should appear on your
                                profile.
                            </DialogDescription>
                        </DialogHeader>

                        <Form
                            {...updateSkills.form()}
                            options={{
                                preserveScroll: true,
                            }}
                            onSuccess={() => setOpen(false)}
                            className="space-y-4"
                        >
                            {({ processing, errors, resetAndClearErrors }) => (
                                <>
                                    {draftSkills.map((skillId) => (
                                        <input
                                            key={skillId}
                                            type="hidden"
                                            name="skills[]"
                                            value={skillId}
                                        />
                                    ))}

                                    <div className="max-h-72 space-y-3 overflow-y-auto pr-1">
                                        {availableSkills.map((skill) => {
                                            const isChecked =
                                                draftSkills.includes(skill.id);

                                            return (
                                                <label
                                                    key={skill.id}
                                                    className="flex cursor-pointer items-start gap-3 rounded-md border p-3"
                                                >
                                                    <Checkbox
                                                        checked={isChecked}
                                                        onCheckedChange={(
                                                            checked,
                                                        ) =>
                                                            toggleSkill(
                                                                skill.id,
                                                                checked ===
                                                                    true,
                                                            )
                                                        }
                                                        className="mt-0.5"
                                                    />
                                                    <span className="space-y-1">
                                                        <span className="block text-sm font-medium">
                                                            {skill.name}
                                                        </span>
                                                        {skill.description && (
                                                            <span className="block text-xs text-muted-foreground">
                                                                {
                                                                    skill.description
                                                                }
                                                            </span>
                                                        )}
                                                    </span>
                                                </label>
                                            );
                                        })}
                                    </div>

                                    <InputError message={errors.skills} />

                                    <DialogFooter>
                                        <DialogClose asChild>
                                            <Button
                                                type="button"
                                                variant="secondary"
                                                onClick={() => {
                                                    setDraftSkills(userSkills);
                                                    resetAndClearErrors();
                                                }}
                                            >
                                                Annuleer
                                            </Button>
                                        </DialogClose>

                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            Save
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>

            {selectedSkills.length > 0 ? (
                <div className="flex flex-wrap gap-2">
                    {selectedSkills.map((skill) => (
                        <Badge key={skill.id} variant="secondary">
                            {skill.name}
                        </Badge>
                    ))}
                </div>
            ) : (
                <p className="text-sm text-muted-foreground">
                    You haven't added any skills yet. Click the "Edit Skills"
                </p>
            )}
        </div>
    );
}
