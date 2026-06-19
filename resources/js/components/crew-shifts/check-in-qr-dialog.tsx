import type { CrewApplication } from '@/components/crew-shifts/types';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

export function CheckInQrDialog({
    application,
    open,
    onOpenChange,
}: {
    application: CrewApplication | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const checkIn = application?.check_in;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Check-in QR</DialogTitle>
                    <DialogDescription>
                        Toon deze QR-code aan de coordinator op de dag van het
                        event om ingecheckt te worden.
                    </DialogDescription>
                </DialogHeader>

                {application && checkIn?.qr_svg_src ? (
                    <div className="space-y-4">
                        <div className="rounded-2xl border border-border/70 bg-white p-4">
                            <img
                                src={checkIn.qr_svg_src}
                                alt={`Check-in QR voor ${application.shift.title ?? 'deze shift'}`}
                                className="mx-auto block w-full max-w-xs"
                            />
                        </div>
                        <div className="rounded-xl border border-border/70 bg-muted/30 p-3 text-sm text-muted-foreground">
                            {application.shift.event_title || 'Onbekend event'}
                            {' · '}
                            {application.shift.title || 'Onbekende shift'}
                        </div>
                    </div>
                ) : (
                    <p className="text-sm text-muted-foreground">
                        Deze QR-code is momenteel niet beschikbaar.
                    </p>
                )}
            </DialogContent>
        </Dialog>
    );
}
