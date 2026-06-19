import { type ComponentType, useEffect, useState } from 'react';
import type {
    IDetectedBarcode,
    IScannerError,
    IScannerProps,
} from '@yudiel/react-qr-scanner';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { EventDashboardScanFeedback } from '@/components/event-dashboard/types';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { formatDateTimeNl } from '@/lib/format-date-time';

const qrFormats: NonNullable<IScannerProps['formats']> = ['qr_code'];

export function QrScannerDialog({
    open,
    paused,
    feedback,
    onOpenChange,
    onClearFeedback,
    onScan,
}: {
    open: boolean;
    paused: boolean;
    feedback: EventDashboardScanFeedback | null;
    onOpenChange: (open: boolean) => void;
    onClearFeedback: () => void;
    onScan: (rawValue: string) => void;
}) {
    const [ScannerComponent, setScannerComponent] =
        useState<ComponentType<IScannerProps> | null>(null);
    const [scannerError, setScannerError] = useState<string | null>(null);

    useEffect(() => {
        if (!open) {
            setScannerError(null);
            return;
        }

        let cancelled = false;

        import('@yudiel/react-qr-scanner')
            .then((module) => {
                if (!cancelled) {
                    setScannerComponent(() => module.Scanner);
                }
            })
            .catch(() => {
                if (!cancelled) {
                    setScannerError('De QR-scanner kon niet geladen worden.');
                }
            });

        return () => {
            cancelled = true;
        };
    }, [open]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="inset-0 h-dvh max-h-none w-dvw max-w-none translate-x-0 translate-y-0 gap-0 rounded-none border-0 p-0 shadow-none sm:max-w-none [&>button.absolute]:hidden">
                <DialogHeader>
                    <DialogTitle className="sr-only">
                        Scan check-in QR
                    </DialogTitle>
                    <DialogDescription className="sr-only">
                        Full screen scanner voor event check-in.
                    </DialogDescription>
                </DialogHeader>

                {feedback?.status === 'success' && feedback.assignment ? (
                    <div className="flex h-full flex-col bg-emerald-600 text-white">
                        <div className="flex items-center justify-between p-4">
                            <Badge className="bg-white/15 text-white">
                                Geldige scan
                            </Badge>
                            <DialogClose asChild>
                                <Button variant="secondary" size="sm">
                                    Sluiten
                                </Button>
                            </DialogClose>
                        </div>

                        <div className="flex flex-1 items-center justify-center p-6">
                            <div className="w-full max-w-xl space-y-6 rounded-3xl bg-white/12 p-6">
                                <div>
                                    <p className="text-sm text-white/80">
                                        Crew member
                                    </p>
                                    <h2 className="text-3xl font-semibold tracking-tight">
                                        {feedback.assignment.user.name}
                                    </h2>
                                    <p className="text-sm text-white/80">
                                        {feedback.assignment.user.email}
                                        {feedback.assignment.user.phone
                                            ? ` · ${feedback.assignment.user.phone}`
                                            : ''}
                                    </p>
                                </div>

                                <div className="grid gap-3 text-sm sm:grid-cols-2">
                                    <div className="rounded-2xl bg-white/12 p-4">
                                        <p className="text-white/75">Shift</p>
                                        <p className="text-lg font-medium">
                                            {feedback.assignment.shift.title}
                                        </p>
                                    </div>
                                    <div className="rounded-2xl bg-white/12 p-4">
                                        <p className="text-white/75">Zone</p>
                                        <p className="text-lg font-medium">
                                            {
                                                feedback.assignment.shift
                                                    .zone_name
                                            }
                                        </p>
                                    </div>
                                    <div className="rounded-2xl bg-white/12 p-4 sm:col-span-2">
                                        <p className="text-white/75">
                                            Ingecheckt om
                                        </p>
                                        <p className="text-lg font-medium">
                                            {feedback.assignment.check_in_at
                                                ? formatDateTimeNl(
                                                      feedback.assignment
                                                          .check_in_at,
                                                  )
                                                : '-'}
                                        </p>
                                    </div>
                                </div>

                                <div className="flex flex-wrap gap-2">
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={onClearFeedback}
                                    >
                                        Scan volgende
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                ) : (
                    <div className="flex h-full flex-col bg-black text-white">
                        <div className="flex items-center justify-between gap-3 p-4">
                            <div>
                                <h2 className="text-lg font-semibold">
                                    Scan check-in QR
                                </h2>
                                <p className="text-xs text-white/70 sm:text-sm">
                                    Gebruik bij voorkeur de achtercamera. Camera
                                    werkt op HTTPS of localhost.
                                </p>
                            </div>
                            <DialogClose asChild>
                                <Button variant="secondary" size="sm">
                                    Sluiten
                                </Button>
                            </DialogClose>
                        </div>

                        <div className="mx-auto flex w-full max-w-5xl flex-1 items-center justify-center p-4 pb-6 sm:p-6">
                            <div className="w-full overflow-hidden rounded-3xl border border-white/20 bg-black/70">
                                {ScannerComponent ? (
                                    <ScannerComponent
                                        onScan={(codes: IDetectedBarcode[]) => {
                                            const value =
                                                codes[0]?.rawValue?.trim();

                                            if (value) {
                                                onScan(value);
                                            }
                                        }}
                                        onError={(error: IScannerError) =>
                                            setScannerError(error.message)
                                        }
                                        paused={paused}
                                        formats={qrFormats}
                                        constraints={{
                                            facingMode: 'environment',
                                        }}
                                        components={{
                                            finder: true,
                                            onOff: true,
                                            torch: true,
                                        }}
                                        sound={true}
                                    />
                                ) : (
                                    <div className="flex min-h-[50vh] items-center justify-center p-6 text-sm text-white/80">
                                        Scanner laden...
                                    </div>
                                )}
                            </div>
                        </div>

                        {feedback?.status === 'error' && (
                            <div className="mx-auto w-full max-w-5xl px-6 pb-6">
                                <p className="rounded-2xl border border-red-400/40 bg-red-600/20 px-4 py-3 text-sm text-red-100">
                                    {feedback.message}
                                </p>
                            </div>
                        )}
                    </div>
                )}

                {scannerError && (
                    <p className="absolute right-4 bottom-4 rounded-xl bg-red-600/90 px-3 py-2 text-sm text-white">
                        {scannerError}
                    </p>
                )}
            </DialogContent>
        </Dialog>
    );
}
