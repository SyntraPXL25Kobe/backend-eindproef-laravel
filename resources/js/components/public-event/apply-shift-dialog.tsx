import InputError from '@/components/input-error';
import type { Shift } from '@/components/public-event/types';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

export function ApplyShiftDialog({
    applyShift,
    motivation,
    motivationError,
    activeShiftId,
    onOpenChange,
    onMotivationChange,
    onSubmit,
    onCancel,
}: {
    applyShift: Shift | null;
    motivation: string;
    motivationError: string | null;
    activeShiftId: number | null;
    onOpenChange: (open: boolean) => void;
    onMotivationChange: (value: string) => void;
    onSubmit: () => void;
    onCancel: () => void;
}) {
    return (
        <Dialog open={Boolean(applyShift)} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Apply voor deze shift</DialogTitle>
                    <DialogDescription>
                        {applyShift
                            ? `Voeg optioneel een motivatie toe voor ${applyShift.title}.`
                            : 'Voeg optioneel een motivatie toe.'}
                    </DialogDescription>
                </DialogHeader>

                <div className="grid gap-2">
                    <Label htmlFor="motivation">Motivatie (optioneel)</Label>
                    <textarea
                        id="motivation"
                        value={motivation}
                        onChange={(event) =>
                            onMotivationChange(event.currentTarget.value)
                        }
                        placeholder="Waarom pas jij goed bij deze shift?"
                        className="min-h-28 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                    />
                    <InputError message={motivationError ?? undefined} />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={onCancel}
                        disabled={
                            !!applyShift && activeShiftId === applyShift.id
                        }
                    >
                        Annuleren
                    </Button>
                    <Button
                        type="button"
                        onClick={onSubmit}
                        disabled={
                            !applyShift || activeShiftId === applyShift.id
                        }
                    >
                        {applyShift && activeShiftId === applyShift.id
                            ? 'Verwerken...'
                            : 'Verstuur aanvraag'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
