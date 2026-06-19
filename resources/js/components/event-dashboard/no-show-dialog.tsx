import { useEffect, useState } from 'react';
import type { EventDashboardAssignment } from '@/components/event-dashboard/types';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

export function NoShowDialog({
    assignment,
    open,
    submitting,
    onOpenChange,
    onSubmit,
}: {
    assignment: EventDashboardAssignment | null;
    open: boolean;
    submitting: boolean;
    onOpenChange: (open: boolean) => void;
    onSubmit: (reason: string) => void;
}) {
    const [reason, setReason] = useState('');

    useEffect(() => {
        if (open) {
            setReason('');
        }
    }, [open]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Markeer als no-show</DialogTitle>
                    <DialogDescription>
                        {assignment
                            ? `Registreer waarom ${assignment.user.name} niet aanwezig was.`
                            : 'Voeg optioneel een reden toe.'}
                    </DialogDescription>
                </DialogHeader>

                <textarea
                    value={reason}
                    onChange={(event) => setReason(event.currentTarget.value)}
                    placeholder="Optionele reden voor no-show"
                    className="min-h-28 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                />

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                        disabled={submitting}
                    >
                        Annuleren
                    </Button>
                    <Button
                        type="button"
                        onClick={() => onSubmit(reason)}
                        disabled={submitting}
                    >
                        {submitting ? 'Verwerken...' : 'Bevestig no-show'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
