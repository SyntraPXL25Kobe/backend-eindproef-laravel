import { Form, Head, usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
    mustAddAddress?: boolean;
};

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth, mustAddAddress } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Profielinstellingen" />

            <h1 className="sr-only">Profielinstellingen</h1>

            <div className="space-y-6">
                <Form
                    {...ProfileController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <Heading
                                variant="small"
                                title="Profiel"
                                description="Werk je naam en e-mailadres bij"
                            />

                            <div className="grid gap-2">
                                <Label htmlFor="name">Volledige naam</Label>

                                <Input
                                    id="name"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.name}
                                    name="name"
                                    required
                                    autoComplete="name"
                                    placeholder="Volledige naam"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">E-mailadres</Label>

                                <Input
                                    id="email"
                                    type="email"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.email}
                                    name="email"
                                    required
                                    autoComplete="username"
                                    placeholder="E-mailadres"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.email}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="phone">Telefoonnummer</Label>

                                <Input
                                    id="phone"
                                    type="tel"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.phone}
                                    name="phone"
                                    required
                                    autoComplete="tel"
                                    placeholder="Telefoonnummer"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.phone}
                                />
                            </div>

                            {mustVerifyEmail &&
                                auth.user.email_verified_at === null && (
                                    <div>
                                        <p className="-mt-4 text-sm text-muted-foreground">
                                            Je e-mailadres is nog niet
                                            geverifieerd.{' '}
                                            <Link
                                                href={send()}
                                                as="button"
                                                className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                            >
                                                Verstuur de verificatiemail
                                                opnieuw.
                                            </Link>
                                        </p>

                                        {status ===
                                            'verification-link-sent' && (
                                            <div className="mt-2 text-sm font-medium text-green-600">
                                                Er is een nieuwe verificatielink
                                                verstuurd naar je e-mailadres.
                                            </div>
                                        )}
                                    </div>
                                )}

                            <Heading
                                variant="small"
                                title="Adres"
                                description="Werk je adresgegevens bij"
                            />

                            <div className="grid gap-2">
                                <Label htmlFor="address">Adres</Label>

                                <Input
                                    id="address"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.address || ''}
                                    name="address"
                                    autoComplete="address"
                                    placeholder="Adres"
                                />
                                <InputError
                                    className="mt-2"
                                    message={errors.address}
                                />
                            </div>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="city">Plaats</Label>

                                    <Input
                                        id="city"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.city || ''}
                                        name="city"
                                        autoComplete="address-level2"
                                        placeholder="Plaats"
                                    />
                                    <InputError
                                        className="mt-2"
                                        message={errors.city}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="postal_code">
                                        Postcode
                                    </Label>

                                    <Input
                                        id="postal_code"
                                        className="mt-1 block w-full"
                                        defaultValue={
                                            auth.user.postal_code || ''
                                        }
                                        name="postal_code"
                                        autoComplete="postal-code"
                                        placeholder="Postcode"
                                    />
                                    <InputError
                                        className="mt-2"
                                        message={errors.postal_code}
                                    />
                                </div>

                                {mustAddAddress && (
                                    <p className="text-sm text-red-600">
                                        Vul je adresgegevens aan.
                                    </p>
                                )}
                            </div>
                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    Opslaan
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>

            <DeleteUser />
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Profielinstellingen',
            href: edit(),
        },
    ],
};
