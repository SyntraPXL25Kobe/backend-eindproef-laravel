import type { IDetectedBarcode, IScannerError } from '@yudiel/react-qr-scanner';
import { Scanner as QrScanner } from '@yudiel/react-qr-scanner';
import { useState } from 'react';
import type { EventDashboardScanFeedback } from '@/components/event-dashboard/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { formatDateTimeNl } from '@/lib/format-date-time';

type QrScannerDialogProps = {
    open: boolean;
    paused: boolean;
    feedback: EventDashboardScanFeedback | null;
    onOpenChange: (open: boolean) => void;
    onClearFeedback: () => void;
    onScan: (rawValue: string) => void;
};

export function QrScannerDialog({
    open,
    paused,
    feedback,
    onOpenChange,
    onClearFeedback,
    onScan,
}: QrScannerDialogProps) {
    const [scannerError, setScannerError] = useState<string | null>(null);
    const scannedAssignment =
        feedback?.status === 'success' ? feedback.assignment : null;
    const scanErrorMessage =
        feedback?.status === 'error' ? feedback.message : null;

    const handleDetectedCodes = (codes: IDetectedBarcode[]) => {
        const value = codes[0]?.rawValue?.trim();

        if (value) {
            onScan(value);
        }
    };

    const handleScannerError = (error: IScannerError) => {
        setScannerError(error.message);
    };

    const handleOpenChange = (nextOpen: boolean) => {
        if (!nextOpen) {
            setScannerError(null);
        }

        onOpenChange(nextOpen);
    };

    const handleClearFeedback = () => {
        setScannerError(null);
        onClearFeedback();
    };

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent className="inset-0 flex h-dvh max-h-none min-h-dvh w-dvw max-w-none translate-x-0 translate-y-0 flex-col gap-0 rounded-none border-0 p-0 shadow-none sm:max-w-none [&>button.absolute]:hidden">
                <DialogHeader>
                    <DialogTitle className="sr-only">
                        Scan check-in QR
                    </DialogTitle>
                    <DialogDescription className="sr-only">
                        Full screen scanner voor event check-in.
                    </DialogDescription>
                </DialogHeader>

                {scannedAssignment ? (
                    <div className="flex h-full min-h-0 flex-1 flex-col bg-emerald-600 text-white">
                        <div className="flex items-center justify-between px-4 pt-4 pb-3">
                            <Badge className="bg-white/15 text-white">
                                Geldige scan
                            </Badge>
                            <DialogClose asChild>
                                <Button variant="secondary" size="sm">
                                    Sluiten
                                </Button>
                            </DialogClose>
                        </div>

                        <div className="flex flex-1 flex-col justify-between gap-4 p-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
                            <div className="space-y-4 rounded-3xl bg-white/12 p-4 sm:p-6">
                                <div className="space-y-1">
                                    <p className="text-sm text-white/80">
                                        Crew member
                                    </p>
                                    <h2 className="text-2xl font-semibold tracking-tight sm:text-3xl">
                                        {scannedAssignment.user.name}
                                    </h2>
                                    <p className="text-sm text-white/80">
                                        {scannedAssignment.user.email}
                                        {scannedAssignment.user.phone
                                            ? ` · ${scannedAssignment.user.phone}`
                                            : ''}
                                    </p>
                                </div>

                                <div className="grid gap-3 text-sm sm:grid-cols-2">
                                    <div className="rounded-2xl bg-white/12 p-4">
                                        <p className="text-white/75">Shift</p>
                                        <p className="text-lg font-medium">
                                            {scannedAssignment.shift.title}
                                        </p>
                                    </div>
                                    <div className="rounded-2xl bg-white/12 p-4">
                                        <p className="text-white/75">Zone</p>
                                        <p className="text-lg font-medium">
                                            {scannedAssignment.shift.zone_name}
                                        </p>
                                    </div>
                                    <div className="rounded-2xl bg-white/12 p-4 sm:col-span-2">
                                        <p className="text-white/75">
                                            Ingecheckt om
                                        </p>
                                        <p className="text-lg font-medium">
                                            {scannedAssignment.check_in_at
                                                ? formatDateTimeNl(
                                                      scannedAssignment.check_in_at,
                                                  )
                                                : '-'}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                onClick={handleClearFeedback}
                                className="w-full rounded-2xl border border-white/40 bg-white/18 px-4 py-5 text-center text-base font-semibold text-white active:scale-[0.99]"
                            >
                                Tik hier voor volgende scan
                            </button>
                        </div>
                    </div>
                ) : scanErrorMessage ? (
                    <div className="flex h-full min-h-0 flex-1 flex-col bg-red-600 text-white">
                        <div className="flex items-center justify-between px-4 pt-4 pb-3">
                            <Badge className="bg-white/15 text-white">
                                Ongeldige scan
                            </Badge>
                            <DialogClose asChild>
                                <Button variant="secondary" size="sm">
                                    Sluiten
                                </Button>
                            </DialogClose>
                        </div>

                        <div className="flex flex-1 flex-col justify-between gap-4 p-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
                            <div className="space-y-4 rounded-3xl bg-white/12 p-4 text-center sm:p-6">
                                <h2 className="text-2xl font-semibold tracking-tight sm:text-3xl">
                                    Scan niet gelukt
                                </h2>
                                <p className="text-base text-white/90">
                                    {scanErrorMessage}
                                </p>

                                {feedback?.assignment && (
                                    <div className="grid gap-3 text-left text-sm sm:grid-cols-2">
                                        <div className="rounded-2xl bg-white/12 p-4 sm:col-span-2">
                                            <p className="text-white/75">
                                                Crew member
                                            </p>
                                            <p className="text-lg font-medium">
                                                {feedback.assignment.user.name}
                                            </p>
                                            <p className="text-xs text-white/80">
                                                {feedback.assignment.user.email}
                                            </p>
                                        </div>
                                        <div className="rounded-2xl bg-white/12 p-4">
                                            <p className="text-white/75">
                                                Shift
                                            </p>
                                            <p className="text-base font-medium">
                                                {
                                                    feedback.assignment.shift
                                                        .title
                                                }
                                            </p>
                                        </div>
                                        <div className="rounded-2xl bg-white/12 p-4">
                                            <p className="text-white/75">
                                                Zone
                                            </p>
                                            <p className="text-base font-medium">
                                                {
                                                    feedback.assignment.shift
                                                        .zone_name
                                                }
                                            </p>
                                        </div>
                                    </div>
                                )}
                            </div>

                            <button
                                type="button"
                                onClick={handleClearFeedback}
                                className="w-full rounded-2xl border border-white/40 bg-white/18 px-4 py-5 text-center text-base font-semibold text-white active:scale-[0.99]"
                            >
                                Tik hier om opnieuw te scannen
                            </button>
                        </div>
                    </div>
                ) : (
                    <div className="flex h-full flex-col bg-black text-white">
                        <div className="flex items-center justify-between gap-3 p-4">
                            <div>
                                <h2 className="text-lg font-semibold">
                                    Scan check-in QR
                                </h2>
                            </div>
                            <DialogClose asChild>
                                <Button variant="secondary" size="sm">
                                    Sluiten
                                </Button>
                            </DialogClose>
                        </div>

                        <div className="mx-auto flex w-full max-w-5xl flex-1 items-center justify-center p-4 pb-6 sm:p-6">
                            <div className="w-full overflow-hidden rounded-3xl border border-white/20 bg-black/70">
                                <QrScanner
                                    onScan={handleDetectedCodes}
                                    onError={handleScannerError}
                                    paused={paused}
                                    allowMultiple={true}
                                    scanDelay={600}
                                    constraints={{
                                        facingMode: {
                                            ideal: 'environment',
                                        },
                                    }}
                                    components={{
                                        finder: true,
                                        torch: true,
                                    }}
                                    retryDelay={300}
                                    styles={{
                                        container: {
                                            width: '100%',
                                            minHeight: '55vh',
                                        },
                                        video: {
                                            objectFit: 'cover',
                                        },
                                    }}
                                    sound={true}
                                    startTimeoutMs={5000}
                                />
                            </div>
                        </div>
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
