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
            <Head title="Profile settings" />

            <h1 className="sr-only">Profile settings</h1>

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
                                title="Profile"
                                description="Update your name and email address"
                            />

                            <div className="grid gap-2">
                                <Label htmlFor="name">Full Name</Label>

                                <Input
                                    id="name"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.name}
                                    name="name"
                                    required
                                    autoComplete="name"
                                    placeholder="Full name"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Email address</Label>

                                <Input
                                    id="email"
                                    type="email"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.email}
                                    name="email"
                                    required
                                    autoComplete="username"
                                    placeholder="Email address"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.email}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="phone">Phone</Label>

                                <Input
                                    id="phone"
                                    type="tel"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.phone}
                                    name="phone"
                                    required
                                    autoComplete="tel"
                                    placeholder="Phone number"
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
                                            Your email address is unverified.{' '}
                                            <Link
                                                href={send()}
                                                as="button"
                                                className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                            >
                                                Click here to re-send the
                                                verification email.
                                            </Link>
                                        </p>

                                        {status ===
                                            'verification-link-sent' && (
                                            <div className="mt-2 text-sm font-medium text-green-600">
                                                A new verification link has been
                                                sent to your email address.
                                            </div>
                                        )}
                                    </div>
                                )}

                            <Heading
                                variant="small"
                                title="Address"
                                description="Update your address information"
                            />

                            <div className="grid gap-2">
                                <Label htmlFor="address">Address</Label>

                                <Input
                                    id="address"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.address || ''}
                                    name="address"
                                    autoComplete="address"
                                    placeholder="Address"
                                />
                                <InputError
                                    className="mt-2"
                                    message={errors.address}
                                />
                            </div>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="city">City</Label>

                                    <Input
                                        id="city"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.city || ''}
                                        name="city"
                                        autoComplete="address-level2"
                                        placeholder="City"
                                    />
                                    <InputError
                                        className="mt-2"
                                        message={errors.city}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="postal_code">
                                        Postal Code
                                    </Label>

                                    <Input
                                        id="postal_code"
                                        className="mt-1 block w-full"
                                        defaultValue={
                                            auth.user.postal_code || ''
                                        }
                                        name="postal_code"
                                        autoComplete="postal-code"
                                        placeholder="Postal Code"
                                    />
                                    <InputError
                                        className="mt-2"
                                        message={errors.postal_code}
                                    />
                                </div>

                                {mustAddAddress && (
                                    <p className="text-sm text-red-600">
                                        Please add your address information.
                                    </p>
                                )}
                            </div>
                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    Save
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
            title: 'Profile settings',
            href: edit(),
        },
    ],
};
