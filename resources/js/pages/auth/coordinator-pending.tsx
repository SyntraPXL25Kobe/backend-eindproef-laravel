import { Head, Link } from '@inertiajs/react';
import { login } from '@/routes';

export default function CoordinatorPending() {
    return (
        <>
            <Head title="Coordinator aanvraag in behandeling" />

            <div className="flex min-h-screen items-center justify-center bg-muted/30 px-6 py-12">
                <div className="w-full max-w-lg min-w-md bg-background p-8 text-center">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Aanvraag ingediend
                    </h1>

                    <p className="mt-3 text-sm text-muted-foreground">
                        Je coordinator-aanvraag staat op pending. Een admin
                        beoordeelt je aanvraag. Je ontvangt een bericht zodra de
                        aanvraag is goedgekeurd of afgewezen.
                    </p>

                    <p className="mt-6 text-xs text-muted-foreground">
                        Je kan later opnieuw aanmelden via{' '}
                        <Link
                            href={login()}
                            className="font-medium underline underline-offset-4"
                        >
                            login
                        </Link>
                        .
                    </p>
                </div>
            </div>
        </>
    );
}

CoordinatorPending.layout = null;
